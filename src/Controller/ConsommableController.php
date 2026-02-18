<?php

namespace App\Controller;

use App\Entity\Consommable;
use App\Entity\Partie;
use App\Enum\ConsommableCategory;
use App\Enum\ConsommableStatus;
use App\Enum\ConsommableType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/partie/{partieId}/consommables')]
class ConsommableController extends AbstractController
{
    /**
     * Afficher les consommables d'une partie
     */
    #[Route('/', name: 'partie_consommables_index')]
    public function index(int $partieId, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            $this->addFlash('error', 'Partie non trouvée.');
            return $this->redirectToRoute('partie_index');
        }
        
        return $this->render('consommable/index.html.twig', [
            'partie' => $partie,
        ]);
    }

    /**
     * Ajouter un consommable
     */
    #[Route('/add', name: 'partie_consommables_add', methods: ['POST'])]
    public function add(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            $this->addFlash('error', 'Partie non trouvée.');
            return $this->redirectToRoute('partie_index');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('consommable_add', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_consommables_index', ['partieId' => $partieId]);
        }

        $type = ConsommableType::from($request->request->get('type'));
        $category = ConsommableCategory::from($request->request->get('category'));
        
        // Générer automatiquement le nom et la description basés sur le type
        $namesAndDescriptions = $this->getConsommableData();
        $typeValue = $type->value;
        
        $name = $namesAndDescriptions[$typeValue]['name'] ?? ucfirst(str_replace('_', ' ', $typeValue));
        $description = $namesAndDescriptions[$typeValue]['description'] ?? 'Consommable';

        // Créer le consommable
        $consommable = new Consommable();
        $consommable->setName($name);
        $consommable->setDescription($description);
        $consommable->setCategory($category);
        $consommable->setType($type);
        $consommable->setStatus(ConsommableStatus::from($request->request->get('status', 'base')));

        $em->persist($consommable);
        $partie->addConsommable($consommable);
        $em->flush();

        $this->addFlash('success', 'Consommable ajouté !');

        return $this->redirectToRoute('partie_consommables_index', ['partieId' => $partieId]);
    }

    /**
     * Retirer un consommable
     */
    #[Route('/{consommableId}/remove', name: 'partie_consommables_remove', methods: ['POST'])]
    public function remove(int $partieId, int $consommableId, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        $consommable = $em->getRepository(Consommable::class)->find($consommableId);
        
        if (!$partie || !$consommable) {
            $this->addFlash('error', 'Partie ou consommable non trouvé.');
            return $this->redirectToRoute('partie_index');
        }

        $partie->removeConsommable($consommable);
        $em->flush();

        $this->addFlash('success', 'Consommable retiré.');

        return $this->redirectToRoute('partie_consommables_index', ['partieId' => $partieId]);
    }

    /**
     * Vider tous les consommables
     */
    #[Route('/clear', name: 'partie_consommables_clear', methods: ['POST'])]
    public function clear(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            $this->addFlash('error', 'Partie non trouvée.');
            return $this->redirectToRoute('partie_index');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('clear_consommables', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_consommables_index', ['partieId' => $partieId]);
        }

        $consommables = $partie->getConsommables();
        foreach ($consommables as $consommable) {
            $partie->removeConsommable($consommable);
        }

        $em->flush();

        $this->addFlash('success', 'Tous les consommables ont été retirés !');

        return $this->redirectToRoute('partie_consommables_index', ['partieId' => $partieId]);
    }

    /**
     * Actions groupées sur plusieurs consommables
     */
    #[Route('/bulk-action', name: 'partie_consommables_bulk_action', methods: ['POST'])]
    public function bulkAction(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            $this->addFlash('error', 'Partie non trouvée.');
            return $this->redirectToRoute('partie_index');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('bulk_action_consommables', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_consommables_index', ['partieId' => $partieId]);
        }

        $consommableIds = $request->request->all('consommable_ids');
        $action = $request->request->get('action');

        if (empty($consommableIds)) {
            $this->addFlash('error', 'Aucun consommable sélectionné.');
            return $this->redirectToRoute('partie_consommables_index', ['partieId' => $partieId]);
        }

        $consommables = $em->getRepository(Consommable::class)->findBy(['id' => $consommableIds]);
        $count = count($consommables);

        switch ($action) {
            case 'delete':
                foreach ($consommables as $consommable) {
                    $partie->removeConsommable($consommable);
                }
                $this->addFlash('success', "$count consommable(s) supprimé(s).");
                break;

            case 'change_status':
                $status = ConsommableStatus::from($request->request->get('bulk_status'));
                foreach ($consommables as $consommable) {
                    $consommable->setStatus($status);
                }
                $this->addFlash('success', "$count consommable(s) - statut modifié.");
                break;

            case 'reset':
                foreach ($consommables as $consommable) {
                    $consommable->setStatus(ConsommableStatus::BASE);
                }
                $this->addFlash('success', "$count consommable(s) réinitialisé(s) à l'état de base.");
                break;

            case 'duplicate':
                $duplicateCount = (int) $request->request->get('duplicate_count', 1);
                if ($duplicateCount < 1 || $duplicateCount > 10) {
                    $this->addFlash('error', 'Le nombre de duplications doit être entre 1 et 10.');
                    return $this->redirectToRoute('partie_consommables_index', ['partieId' => $partieId]);
                }
                
                $totalCreated = 0;
                foreach ($consommables as $consommable) {
                    for ($i = 0; $i < $duplicateCount; $i++) {
                        $duplicate = new Consommable();
                        $duplicate->setName($consommable->getName());
                        $duplicate->setDescription($consommable->getDescription());
                        $duplicate->setCategory($consommable->getCategory());
                        $duplicate->setType($consommable->getType());
                        $duplicate->setStatus($consommable->getStatus());
                        
                        $em->persist($duplicate);
                        $partie->addConsommable($duplicate);
                        $totalCreated++;
                    }
                }
                $this->addFlash('success', "$totalCreated consommable(s) créé(s) par duplication (×$duplicateCount chaque).");
                break;

            default:
                $this->addFlash('error', 'Action non reconnue.');
                return $this->redirectToRoute('partie_consommables_index', ['partieId' => $partieId]);
        }

        $em->flush();

        return $this->redirectToRoute('partie_consommables_index', ['partieId' => $partieId]);
    }

    private function getConsommableData(): array
    {
        return [
            // TAROTS
            'the_fool' => ['name' => 'The Fool', 'description' => 'Crée la dernière carte Tarot ou Planète utilisée'],
            'the_magician' => ['name' => 'The Magician', 'description' => 'Améliore 2 cartes sélectionnées en Lucky Card'],
            'the_high_priestess' => ['name' => 'The High Priestess', 'description' => 'Crée jusqu\'à 2 cartes Planet'],
            'the_empress' => ['name' => 'The Empress', 'description' => 'Améliore 2 cartes sélectionnées en Mult Card'],
            'the_emperor' => ['name' => 'The Emperor', 'description' => 'Crée jusqu\'à 2 cartes Tarot'],
            'the_hierophant' => ['name' => 'The Hierophant', 'description' => 'Améliore 2 cartes sélectionnées en Bonus Card'],
            'the_lovers' => ['name' => 'The Lovers', 'description' => 'Améliore 1 carte sélectionnée en Wild Card'],
            'the_chariot' => ['name' => 'The Chariot', 'description' => 'Améliore 1 carte sélectionnée en Steel Card'],
            'justice' => ['name' => 'Justice', 'description' => 'Améliore 1 carte sélectionnée en Glass Card'],
            'the_hermit' => ['name' => 'The Hermit', 'description' => 'Double l\'argent (max $20)'],
            'wheel_of_fortune' => ['name' => 'Wheel of Fortune', 'description' => '1 chance sur 4 d\'ajouter un jeton Foil, Holo, ou Polychrome'],
            'strength' => ['name' => 'Strength', 'description' => 'Augmente le rang de jusqu\'à 2 cartes sélectionnées de 1'],
            'the_hanged_man' => ['name' => 'The Hanged Man', 'description' => 'Détruit jusqu\'à 2 cartes sélectionnées'],
            'death' => ['name' => 'Death', 'description' => 'Convertit 2 cartes sélectionnées en nouvelles cartes aléatoires'],
            'temperance' => ['name' => 'Temperance', 'description' => 'Donne la valeur totale de vente de tous les Jokers actuels'],
            'the_devil' => ['name' => 'The Devil', 'description' => 'Améliore 1 carte sélectionnée en Stone Card'],
            'the_tower' => ['name' => 'The Tower', 'description' => 'Améliore 1 carte sélectionnée en Gold Card'],
            'the_star' => ['name' => 'The Star', 'description' => 'Convertit jusqu\'à 3 cartes sélectionnées en Diamonds'],
            'the_moon' => ['name' => 'The Moon', 'description' => 'Convertit jusqu\'à 3 cartes sélectionnées en Clubs'],
            'the_sun' => ['name' => 'The Sun', 'description' => 'Convertit jusqu\'à 3 cartes sélectionnées en Hearts'],
            'judgement' => ['name' => 'Judgement', 'description' => 'Crée une carte Joker aléatoire'],
            'the_world' => ['name' => 'The World', 'description' => 'Convertit jusqu\'à 3 cartes sélectionnées en Spades'],
            
            // PLANETS
            'mercury' => ['name' => 'Mercury', 'description' => 'Améliore Pair'],
            'venus' => ['name' => 'Venus', 'description' => 'Améliore Brelan'],
            'earth' => ['name' => 'Earth', 'description' => 'Améliore Full House'],
            'mars' => ['name' => 'Mars', 'description' => 'Améliore Quinte'],
            'jupiter' => ['name' => 'Jupiter', 'description' => 'Améliore Flush'],
            'saturn' => ['name' => 'Saturn', 'description' => 'Améliore Straight Flush'],
            'uranus' => ['name' => 'Uranus', 'description' => 'Améliore Two Pair'],
            'neptune' => ['name' => 'Neptune', 'description' => 'Améliore Four of a Kind'],
            'pluto' => ['name' => 'Pluto', 'description' => 'Améliore High Card'],
            'planet_x' => ['name' => 'Planet X', 'description' => 'Améliore Five of a Kind'],
            'ceres' => ['name' => 'Ceres', 'description' => 'Améliore Flush House'],
            'eris' => ['name' => 'Eris', 'description' => 'Améliore Flush Five'],
            
            // SPECTRALS
            'familiar' => ['name' => 'Familiar', 'description' => 'Détruit 1 carte aléatoire, ajoute 3 cartes Enhanced aléatoires'],
            'grim' => ['name' => 'Grim', 'description' => 'Détruit 1 carte aléatoire, ajoute 2 cartes Enhanced aléatoires'],
            'incantation' => ['name' => 'Incantation', 'description' => 'Détruit 1 carte aléatoire, ajoute 4 cartes Enhanced aléatoires'],
            'talisman' => ['name' => 'Talisman', 'description' => 'Ajoute un Gold Seal à 1 carte sélectionnée'],
            'aura' => ['name' => 'Aura', 'description' => 'Ajoute Foil, Holo, ou Polychrome à 1 carte sélectionnée'],
            'wraith' => ['name' => 'Wraith', 'description' => 'Crée un Joker Rare aléatoire, met l\'argent à $0'],
            'sigil' => ['name' => 'Sigil', 'description' => 'Convertit toutes les cartes dans la main en une seule couleur aléatoire'],
            'ouija' => ['name' => 'Ouija', 'description' => 'Convertit toutes les cartes dans la main en un seul rang aléatoire, -1 main size'],
            'ectoplasm' => ['name' => 'Ectoplasm', 'description' => 'Ajoute Negative à un Joker aléatoire, -1 hand per round'],
            'immolate' => ['name' => 'Immolate', 'description' => 'Détruit 5 cartes aléatoires, gagne $20'],
            'ankh' => ['name' => 'Ankh', 'description' => 'Crée une copie d\'un Joker aléatoire, détruit tous les autres Jokers'],
            'deja_vu' => ['name' => 'Deja Vu', 'description' => 'Ajoute un Red Seal à 1 carte sélectionnée'],
            'hex' => ['name' => 'Hex', 'description' => 'Ajoute Polychrome à un Joker aléatoire, détruit tous les autres Jokers'],
            'trance' => ['name' => 'Trance', 'description' => 'Ajoute un Blue Seal à 1 carte sélectionnée'],
            'medium' => ['name' => 'Medium', 'description' => 'Ajoute un Purple Seal à 1 carte sélectionnée'],
            'cryptid' => ['name' => 'Cryptid', 'description' => 'Crée 2 copies d\'une carte sélectionnée'],
            'the_soul' => ['name' => 'The Soul', 'description' => 'Crée un Joker Legendary'],
            'black_hole' => ['name' => 'Black Hole', 'description' => 'Améliore toutes les mains de poker de 1 niveau'],
        ];
    }
}
