<?php

namespace App\Controller;

use App\Entity\Carte;
use App\Entity\Partie;
use App\Enum\CarteColor;
use App\Enum\CarteNumber;
use App\Enum\CarteStatus;
use App\Enum\CarteStatusMatter;
use App\Enum\CarteStatusSeal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/partie/{partieId}/cartes')]
class CarteController extends AbstractController
{
    //Afficher les cartes d'une partie d'un joueur
    #[Route('/', name: 'partie_cartes_index')]
    public function index(int $partieId, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            $this->addFlash('error', 'Partie non trouvée.');
            return $this->redirectToRoute('partie_index');
        }
        
        return $this->render('carte/deck.html.twig', [
            'partie' => $partie,
        ]);
    }

    //Fonction pour ajouter une carte au deck de la partie
    #[Route('/add', name: 'partie_cartes_add', methods: ['POST'])]
    public function add(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            $this->addFlash('error', 'Partie non trouvée.');
            return $this->redirectToRoute('partie_index');
        }

        // Vérifier le token CSRF (cross-site request forgery) (en gros c'est pour éviter que des attaques externes puissent faire des requêtes à notre place) (je le dis ici je ne le répèterais pas à chaque fois mais c'est important de le faire pour toutes les actions qui modifient des données)
        if (!$this->isCsrfTokenValid('carte_add', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
        }

        // Créer la carte (on récupère les datas du formulaire d'ajout de carte)
        $carte = new Carte();
        $carte->setNumber(CarteNumber::from($request->request->get('number')));
        $carte->setColor(CarteColor::from($request->request->get('color')));
        $carte->setStatus(CarteStatus::from($request->request->get('status', 'base')));
        $carte->setSeal(CarteStatusSeal::from($request->request->get('seal', 'base')));
        $carte->setMatter(CarteStatusMatter::from($request->request->get('matter', 'base')));

        $em->persist($carte);
        $partie->addCarte($carte);
        $em->flush();

        $this->addFlash('success', 'Carte ajoutée au deck !');

        return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
    }

    //Retirer une carte du deck
    #[Route('/{carteId}/remove', name: 'partie_cartes_remove', methods: ['POST'])]
    public function remove(int $partieId, int $carteId, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        $carte = $em->getRepository(Carte::class)->find($carteId);
        
        if (!$partie || !$carte) {
            $this->addFlash('error', 'Partie ou carte non trouvée.');
            return $this->redirectToRoute('partie_index');
        }

        $partie->removeCarte($carte);
        $em->flush();

        $this->addFlash('success', 'Carte retirée du deck.');

        return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
    }

    /**
     * Créer un deck standard de 52 cartes
     */
    #[Route('/create-standard-deck', name: 'partie_cartes_create_standard_deck', methods: ['POST'])]
    public function createStandardDeck(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            $this->addFlash('error', 'Partie non trouvée.');
            return $this->redirectToRoute('partie_index');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('create_deck', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
        }

        // Créer 52 cartes (13 valeurs × 4 couleurs)
        $numbers = CarteNumber::cases();
        $colors = CarteColor::cases();
        $count = 0;
        //Une belle petite boucle imbriquée pour créer toutes les cartes du deck standard
        foreach ($numbers as $number) {
            foreach ($colors as $color) {
                $carte = new Carte();
                $carte->setNumber($number);
                $carte->setColor($color);
                $carte->setStatus(CarteStatus::BASE);
                $carte->setSeal(CarteStatusSeal::BASE);
                $carte->setMatter(CarteStatusMatter::BASE);
                
                $em->persist($carte);
                $partie->addCarte($carte);
                $count++;
            }
        }

        $em->flush();

        $this->addFlash('success', "Deck standard de $count cartes créé !");

        return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
    }

    /**
     * Supprimer toutes les cartes du deck
     */
    #[Route('/clear', name: 'partie_cartes_clear', methods: ['POST'])]
    public function clear(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            $this->addFlash('error', 'Partie non trouvée.');
            return $this->redirectToRoute('partie_index');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('clear_deck', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
        }

        $cartes = $partie->getCartes();
        foreach ($cartes as $carte) {
            $partie->removeCarte($carte);
        }

        $em->flush();

        $this->addFlash('success', 'Deck vidé !');

        return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
    }

    //Fonction pour modifier plusieurs cartes en même temps (quand on sélectionne plusieurs cartes dans l'interface)
    #[Route('/bulk-action', name: 'partie_cartes_bulk_action', methods: ['POST'])]
    public function bulkAction(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            $this->addFlash('error', 'Partie non trouvée.');
            return $this->redirectToRoute('partie_index');
        }

        if (!$this->isCsrfTokenValid('bulk_action', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
        }

        // Récupérer les IDs des cartes sélectionnées et l'action à effectuer
        $carteIds = $request->request->all('carte_ids');
        $action = $request->request->get('action');

        if (empty($carteIds)) {
            $this->addFlash('error', 'Aucune carte sélectionnée.');
            return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
        }

        $cartes = $em->getRepository(Carte::class)->findBy(['id' => $carteIds]);
        $count = count($cartes);

        // Selon l'action demandée, on fait l'opération sur toutes les cartes sélectionnées
        switch ($action) {
            case 'delete':
                foreach ($cartes as $carte) {
                    $partie->removeCarte($carte);
                }
                $this->addFlash('success', "$count carte(s) supprimée(s).");
                break;

            case 'change_status':
                $status = CarteStatus::from($request->request->get('bulk_status'));
                foreach ($cartes as $carte) {
                    $carte->setStatus($status);
                }
                $this->addFlash('success', "$count carte(s) - édition modifiée.");
                break;

            case 'change_seal':
                $seal = CarteStatusSeal::from($request->request->get('bulk_seal'));
                foreach ($cartes as $carte) {
                    $carte->setSeal($seal);
                }
                $this->addFlash('success', "$count carte(s) - sceau modifié.");
                break;

            case 'change_matter':
                $matter = CarteStatusMatter::from($request->request->get('bulk_matter'));
                foreach ($cartes as $carte) {
                    $carte->setMatter($matter);
                }
                $this->addFlash('success', "$count carte(s) - amélioration modifiée.");
                break;

            case 'reset_to_base':
                // Remettre toutes les cartes à leur état initial (sans édition, sceau, ni amélioration)
                foreach ($cartes as $carte) {
                    $carte->setStatus(CarteStatus::BASE);
                    $carte->setSeal(CarteStatusSeal::BASE);
                    $carte->setMatter(CarteStatusMatter::BASE);
                }
                $this->addFlash('success', "$count carte(s) réinitialisée(s) (base).");
                break;

            case 'duplicate':
                $duplicateCount = (int) $request->request->get('duplicate_count', 1);
                if ($duplicateCount < 1 || $duplicateCount > 10) {
                    $this->addFlash('error', 'Le nombre de duplications doit être entre 1 et 10.');
                    return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
                }
                
                // Pour chaque carte sélectionnée, on en crée X copies identiques
                $totalCreated = 0;
                foreach ($cartes as $carte) {
                    for ($i = 0; $i < $duplicateCount; $i++) {
                        $duplicate = new Carte();
                        $duplicate->setNumber($carte->getNumber());
                        $duplicate->setColor($carte->getColor());
                        $duplicate->setStatus($carte->getStatus());
                        $duplicate->setSeal($carte->getSeal());
                        $duplicate->setMatter($carte->getMatter());
                        
                        $em->persist($duplicate);
                        $partie->addCarte($duplicate);
                        $totalCreated++;
                    }
                }
                $this->addFlash('success', "$totalCreated carte(s) créée(s) par duplication (×$duplicateCount chaque).");
                break;

            default:
                $this->addFlash('error', 'Action non reconnue.');
                return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
        }

        $em->flush();

        return $this->redirectToRoute('partie_cartes_index', ['partieId' => $partieId]);
    }
}
