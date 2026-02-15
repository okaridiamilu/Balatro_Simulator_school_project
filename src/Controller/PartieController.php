<?php

namespace App\Controller;

use App\Entity\Partie;
use App\Entity\User;
use App\Entity\JokerInstance;
use App\Entity\JokerTemplate;
use App\Entity\HandLevel;
use App\Enum\EtatJoker;
use App\Form\JokerInstanceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/partie')]
class PartieController extends AbstractController
{
    /**
     * Liste toutes les parties
     */
    #[Route('/', name: 'partie_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $parties = $em->getRepository(Partie::class)->findAll();
        
        return $this->render('partie/index.html.twig', [
            'parties' => $parties,
        ]);
    }

    /**
     * Créer une nouvelle partie
     */
    #[Route('/new', name: 'partie_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Pour l'instant, on utilise le premier user en base
        // Plus tard, on utilisera le système d'authentification
        $user = $em->getRepository(User::class)->findOneBy([]);
        
        if (!$user) {
            $this->addFlash('error', 'Aucun utilisateur trouvé. Créez un utilisateur d\'abord.');
            return $this->redirectToRoute('partie_index');
        }

        // Créer un HandLevel par défaut avec tous les niveaux à 0
        $handLevel = new HandLevel();
        $handLevel->setPair(0);
        $handLevel->setTwoPair(0);
        $handLevel->setThreeOfAKind(0);
        $handLevel->setStraight(0);
        $handLevel->setFlush(0);
        $handLevel->setFullHouse(0);
        $handLevel->setFourOfAKind(0);
        $handLevel->setStraightFlush(0);
        $handLevel->setRoyalFlush(0);
        $handLevel->setHighCard(0);
        
        $em->persist($handLevel);

        $partie = new Partie();
        $partie->setUser($user);
        $partie->setIdentifiant('Partie ' . date('Y-m-d H:i:s'));
        $partie->setMoney(50); // Argent de départ
        $partie->setHand(8);   // 8 cartes en main
        $partie->setDiscard(3); // 3 défausses
        $partie->setHandLevel($handLevel); // Associer le HandLevel

        $em->persist($partie);
        $em->flush();

        $this->addFlash('success', 'Nouvelle partie créée avec succès !');
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    /**
     * Afficher le détail d'une partie
     */
    #[Route('/{id}', name: 'partie_show', requirements: ['id' => '\d+'])]
    public function show(Partie $partie, Request $request, EntityManagerInterface $em): Response
    {
        // Traiter l'ajout manuel d'un joker
        if ($request->isMethod('POST') && $request->request->has('joker_instance')) {
            $data = $request->request->all('joker_instance');
            
            // Vérifier le token CSRF
            if (!$this->isCsrfTokenValid('joker_instance', $data['_token'])) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
            }
            
            // Récupérer le template de joker
            $jokerTemplate = $em->getRepository(JokerTemplate::class)->find($data['jokerTemplate']);
            
            if (!$jokerTemplate) {
                $this->addFlash('error', 'Joker template non trouvé.');
                return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
            }
            
            // Déterminer si le joker sera négatif
            $isNegatif = !empty($data['etat']) && $data['etat'] === 'negatif';
            
            // Vérifier si on a assez de slots disponibles (sauf pour les jokers négatifs qui s'auto-ajoutent)
            if (!$isNegatif && $partie->getAvailableJokerSlots() <= 0) {
                $this->addFlash('error', 'Plus de slots disponibles ! (' . $partie->getUsedJokerSlots() . '/' . $partie->getTotalJokerSlots() . ') - Ajoutez un joker négatif pour augmenter la limite.');
                return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
            }
            
            // Créer l'instance
            $jokerInstance = new JokerInstance();
            $jokerInstance->setJokerTemplate($jokerTemplate);
            $jokerInstance->setPartie($partie);
            $jokerInstance->setOrdre((int)$data['ordre']);
            $jokerInstance->setCompteurStack((int)($data['compteurStack'] ?? 0));
            
            // État optionnel
            if (!empty($data['etat'])) {
                $jokerInstance->setEtat(EtatJoker::from($data['etat']));
            }
            
            $em->persist($jokerInstance);
            $em->flush();
            
            $this->addFlash('success', 'Joker "' . $jokerTemplate->getNom() . '" ajouté à la partie !');
            
            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }
        
        return $this->render('partie/show.html.twig', [
            'partie' => $partie,
        ]);
    }

    /**
     * Supprimer un joker d'une partie
     */
    #[Route('/{partieId}/joker/{jokerId}/delete', name: 'partie_joker_delete')]
    public function deleteJoker(
        int $partieId,
        int $jokerId,
        EntityManagerInterface $em
    ): Response {
        $jokerInstance = $em->getRepository(JokerInstance::class)->find($jokerId);
        
        if (!$jokerInstance || $jokerInstance->getPartie()->getId() !== $partieId) {
            $this->addFlash('error', 'Joker non trouvé.');
            return $this->redirectToRoute('partie_show', ['id' => $partieId]);
        }
        
        $em->remove($jokerInstance);
        $em->flush();
        
        $this->addFlash('success', 'Joker supprimé de la partie.');
        
        return $this->redirectToRoute('partie_show', ['id' => $partieId]);
    }

    /**
     * Supprimer une partie
     */
    #[Route('/{id}/delete', name: 'partie_delete', methods: ['POST'])]
    public function delete(Partie $partie, EntityManagerInterface $em): Response
    {
        $em->remove($partie);
        $em->flush();
        
        $this->addFlash('success', 'Partie supprimée avec succès.');
        
        return $this->redirectToRoute('partie_index');
    }

    /**
     * Modifier l'argent d'une partie
     */
    #[Route('/{id}/money/{amount}', name: 'partie_money_update')]
    public function updateMoney(Partie $partie, int $amount, EntityManagerInterface $em): Response
    {
        $partie->setMoney($partie->getMoney() + $amount);
        $em->flush();
        
        $message = $amount > 0 ? "+$amount $" : "$amount $";
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    /**
     * Modifier le nombre de cartes en main
     */
    #[Route('/{id}/hand/{amount}', name: 'partie_hand_update')]
    public function updateHand(Partie $partie, int $amount, EntityManagerInterface $em): Response
    {
        $newValue = $partie->getHand() + $amount;
        if ($newValue < 1) {
            $newValue = 1; // Minimum 1 carte en main
        }
        $partie->setHand($newValue);
        $em->flush();
        
        $message = $amount > 0 ? "+$amount main" : "$amount main";
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    /**
     * Modifier le nombre de défausses
     */
    #[Route('/{id}/discard/{amount}', name: 'partie_discard_update')]
    public function updateDiscard(Partie $partie, int $amount, EntityManagerInterface $em): Response
    {
        $newValue = $partie->getDiscard() + $amount;
        if ($newValue < 0) {
            $newValue = 0; // Minimum 0 défausses
        }
        $partie->setDiscard($newValue);
        $em->flush();
        
        $message = $amount > 0 ? "+$amount défausse" : "$amount défausse";
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    /**
     * Modifier le nombre de slots de jokers
     */
    #[Route('/{id}/joker-slots/{amount}', name: 'partie_joker_slots_update')]
    public function updateJokerSlots(Partie $partie, int $amount, EntityManagerInterface $em): Response
    {
        $newValue = $partie->getJokerSlots() + $amount;
        if ($newValue < 1) {
            $newValue = 1; // Minimum 1 slot de joker
        }
        if ($newValue > 10) {
            $newValue = 10; // Maximum 10 slots (limite raisonnable)
        }
        $partie->setJokerSlots($newValue);
        $em->flush();
        
        $message = $amount > 0 ? "+$amount slot joker" : "$amount slot joker";
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    /**
     * API pour rechercher des jokers (AJAX)
     */
    #[Route('/api/jokers/search', name: 'api_jokers_search')]
    public function searchJokers(Request $request, EntityManagerInterface $em): Response
    {
        $query = $request->query->get('q', '');
        
        $qb = $em->getRepository(JokerTemplate::class)->createQueryBuilder('j');
        
        if ($query) {
            $qb->where('j.nom LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }
        
        $jokers = $qb->orderBy('j.rarete', 'DESC')
                     ->addOrderBy('j.nom', 'ASC')
                     ->setMaxResults(10)
                     ->getQuery()
                     ->getResult();
        
        $data = [];
        foreach ($jokers as $joker) {
            $data[] = [
                'id' => $joker->getId(),
                'nom' => $joker->getNom(),
                'rarete' => $joker->getRarete()->value,
                'description' => $joker->getDescription(),
                'typeStack' => $joker->getTypeStack()->value,
                'stackParUnite' => $joker->getStackParUnite(),
            ];
        }
        
        return $this->json($data);
    }
}
