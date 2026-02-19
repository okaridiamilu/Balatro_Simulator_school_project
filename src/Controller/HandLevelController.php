<?php

namespace App\Controller;

use App\Entity\Partie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/partie/{partieId}/hand-levels')]
class HandLevelController extends AbstractController
{
    // Configuration de toutes les mains (high card, pair, brelan, etc.) avec leurs chips/mult de base et combien ils augmentent par niveau
    private function getHandsConfig(): array
    {
        return [
            'highCard' => [
                'name' => 'High Card',
                'icon' => '[HC]',
                'baseChips' => 5,
                'baseMult' => 1,
                'chipsPerLevel' => 10,
                'multPerLevel' => 1,
            ],
            'pair' => [
                'name' => 'Pair',
                'icon' => '[PR]',
                'baseChips' => 10,
                'baseMult' => 2,
                'chipsPerLevel' => 15,
                'multPerLevel' => 1,
            ],
            'twoPair' => [
                'name' => 'Two Pair',
                'icon' => '[2P]',
                'baseChips' => 20,
                'baseMult' => 2,
                'chipsPerLevel' => 20,
                'multPerLevel' => 1,
            ],
            'threeOfAKind' => [
                'name' => 'Three of a Kind',
                'icon' => '[3K]',
                'baseChips' => 30,
                'baseMult' => 3,
                'chipsPerLevel' => 20,
                'multPerLevel' => 2,
            ],
            'straight' => [
                'name' => 'Straight',
                'icon' => '[ST]',
                'baseChips' => 30,
                'baseMult' => 4,
                'chipsPerLevel' => 30,
                'multPerLevel' => 3,
            ],
            'flush' => [
                'name' => 'Flush',
                'icon' => '[FL]',
                'baseChips' => 35,
                'baseMult' => 4,
                'chipsPerLevel' => 15,
                'multPerLevel' => 2,
            ],
            'fullHouse' => [
                'name' => 'Full House',
                'icon' => '[FH]',
                'baseChips' => 40,
                'baseMult' => 4,
                'chipsPerLevel' => 25,
                'multPerLevel' => 2,
            ],
            'fourOfAKind' => [
                'name' => 'Four of a Kind',
                'icon' => '[4K]',
                'baseChips' => 60,
                'baseMult' => 7,
                'chipsPerLevel' => 30,
                'multPerLevel' => 3,
            ],
            'straightFlush' => [
                'name' => 'Straight Flush',
                'icon' => '[SF]',
                'baseChips' => 100,
                'baseMult' => 8,
                'chipsPerLevel' => 40,
                'multPerLevel' => 4,
            ],
            'royalFlush' => [
                'name' => 'Royal Flush',
                'icon' => '[RF]',
                'baseChips' => 100,
                'baseMult' => 8,
                'chipsPerLevel' => 40,
                'multPerLevel' => 4,
            ],
        ];
    }

    // Calculer les chips et mult d'une main selon son niveau actuel (baseChips + niveau * chipsPerLevel)
    private function calculateHandStats(string $handKey, int $level): array
    {
        $config = $this->getHandsConfig()[$handKey];
        
        return [
            'chips' => $config['baseChips'] + ($level * $config['chipsPerLevel']),
            'mult' => $config['baseMult'] + ($level * $config['multPerLevel']),
        ];
    }

    // Afficher la page avec tous les niveaux de mains de la partie
    #[Route('', name: 'hand_levels_index')]
    public function index(int $partieId, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            throw $this->createNotFoundException('Partie non trouvée');
        }

        $handLevel = $partie->getHandLevel();
        $handsConfig = $this->getHandsConfig();
        
        // Préparer les données pour chaque main (avec son niveau actuel, ses chips et mult)
        $handsData = [];
        foreach ($handsConfig as $key => $config) {
            $getter = 'get' . ucfirst($key);
            $level = $handLevel->$getter();
            $stats = $this->calculateHandStats($key, $level);
            
            $handsData[$key] = [
                'config' => $config,
                'level' => $level,
                'chips' => $stats['chips'],
                'mult' => $stats['mult'],
            ];
        }

        return $this->render('hand_level/levels.html.twig', [
            'partie' => $partie,
            'handsData' => $handsData,
        ]);
    }

    // Augmenter ou diminuer le niveau d'une main spécifique (+1 ou -1 selon le bouton cliqué)
    #[Route('/{handType}/update/{amount}', name: 'hand_level_update', methods: ['POST'])]
    public function updateLevel(
        int $partieId,
        string $handType,
        int $amount,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            throw $this->createNotFoundException('Partie non trouvée');
        }

        // Vérifier le token CSRF (sécurité contre les attaques)
        if (!$this->isCsrfTokenValid('hand_level_update', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
        }

        $handLevel = $partie->getHandLevel();
        
        // Vérifier que le type de main demandé existe bien (highCard, pair, twoPair, etc.)
        $validHandTypes = array_keys($this->getHandsConfig());
        if (!in_array($handType, $validHandTypes)) {
            $this->addFlash('error', 'Type de main invalide.');
            return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
        }

        // Mettre à jour le niveau (avec un minimum de 0, on ne peut pas avoir un niveau négatif !)
        $getter = 'get' . ucfirst($handType);
        $setter = 'set' . ucfirst($handType);
        
        $currentLevel = $handLevel->$getter();
        $newLevel = max(0, $currentLevel + $amount); // Ne descend jamais en dessous de 0
        
        $handLevel->$setter($newLevel);
        $em->flush();

        $handName = $this->getHandsConfig()[$handType]['name'];
        $this->addFlash('success', "Niveau de {$handName} mis à jour : {$newLevel}");

        return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
    }

    // Remettre toutes les mains au niveau 0 (quand on veut recommencer à zéro)
    #[Route('/reset', name: 'hand_levels_reset', methods: ['POST'])]
    public function reset(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            throw $this->createNotFoundException('Partie non trouvée');
        }

        // Vérifier le token CSRF (sécurité contre les attaques)
        if (!$this->isCsrfTokenValid('hand_levels_reset', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
        }

        $handLevel = $partie->getHandLevel();
        
        // Passer toutes les mains à 0 (boucler sur toutes les mains et set(0))
        foreach (array_keys($this->getHandsConfig()) as $handType) {
            $setter = 'set' . ucfirst($handType);
            $handLevel->$setter(0);
        }
        
        $em->flush();

        $this->addFlash('success', 'Tous les niveaux ont été réinitialisés à 0.');

        return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
    }

    // Améliorer toutes les mains de +1 niveau d'un coup (comme l'effet du consommable Black Hole)
    #[Route('/upgrade-all', name: 'hand_levels_upgrade_all', methods: ['POST'])]
    public function upgradeAll(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            throw $this->createNotFoundException('Partie non trouvée');
        }

        // Vérifier le token CSRF (sécurité contre les attaques)
        if (!$this->isCsrfTokenValid('hand_levels_upgrade_all', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
        }

        $handLevel = $partie->getHandLevel();
        
        // Ajouter 1 niveau à chaque main (l'effet Black Hole du jeu original)
        foreach (array_keys($this->getHandsConfig()) as $handType) {
            $getter = 'get' . ucfirst($handType);
            $setter = 'set' . ucfirst($handType);
            $handLevel->$setter($handLevel->$getter() + 1);
        }
        
        $em->flush();

        $this->addFlash('success', 'Toutes les mains ont été améliorées de 1 niveau (Black Hole) !');

        return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
    }
}
