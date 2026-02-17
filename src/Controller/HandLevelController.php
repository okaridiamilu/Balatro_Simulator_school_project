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
    /**
     * Configuration de base des mains (chips, mult) et leurs augmentations par niveau
     */
    private function getHandsConfig(): array
    {
        return [
            'highCard' => [
                'name' => 'High Card',
                'icon' => '🃏',
                'baseChips' => 5,
                'baseMult' => 1,
                'chipsPerLevel' => 10,
                'multPerLevel' => 1,
            ],
            'pair' => [
                'name' => 'Pair',
                'icon' => '👫',
                'baseChips' => 10,
                'baseMult' => 2,
                'chipsPerLevel' => 15,
                'multPerLevel' => 1,
            ],
            'twoPair' => [
                'name' => 'Two Pair',
                'icon' => '👥',
                'baseChips' => 20,
                'baseMult' => 2,
                'chipsPerLevel' => 20,
                'multPerLevel' => 1,
            ],
            'threeOfAKind' => [
                'name' => 'Three of a Kind',
                'icon' => '🎯',
                'baseChips' => 30,
                'baseMult' => 3,
                'chipsPerLevel' => 20,
                'multPerLevel' => 2,
            ],
            'straight' => [
                'name' => 'Straight',
                'icon' => '📏',
                'baseChips' => 30,
                'baseMult' => 4,
                'chipsPerLevel' => 30,
                'multPerLevel' => 3,
            ],
            'flush' => [
                'name' => 'Flush',
                'icon' => '🌊',
                'baseChips' => 35,
                'baseMult' => 4,
                'chipsPerLevel' => 15,
                'multPerLevel' => 2,
            ],
            'fullHouse' => [
                'name' => 'Full House',
                'icon' => '🏠',
                'baseChips' => 40,
                'baseMult' => 4,
                'chipsPerLevel' => 25,
                'multPerLevel' => 2,
            ],
            'fourOfAKind' => [
                'name' => 'Four of a Kind',
                'icon' => '💎',
                'baseChips' => 60,
                'baseMult' => 7,
                'chipsPerLevel' => 30,
                'multPerLevel' => 3,
            ],
            'straightFlush' => [
                'name' => 'Straight Flush',
                'icon' => '🔥',
                'baseChips' => 100,
                'baseMult' => 8,
                'chipsPerLevel' => 40,
                'multPerLevel' => 4,
            ],
            'royalFlush' => [
                'name' => 'Royal Flush',
                'icon' => '👑',
                'baseChips' => 100,
                'baseMult' => 8,
                'chipsPerLevel' => 40,
                'multPerLevel' => 4,
            ],
        ];
    }

    /**
     * Calculer les chips et mult pour un niveau donné d'une main
     */
    private function calculateHandStats(string $handKey, int $level): array
    {
        $config = $this->getHandsConfig()[$handKey];
        
        return [
            'chips' => $config['baseChips'] + ($level * $config['chipsPerLevel']),
            'mult' => $config['baseMult'] + ($level * $config['multPerLevel']),
        ];
    }

    /**
     * Afficher les niveaux des mains
     */
    #[Route('', name: 'hand_levels_index')]
    public function index(int $partieId, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            throw $this->createNotFoundException('Partie non trouvée');
        }

        $handLevel = $partie->getHandLevel();
        $handsConfig = $this->getHandsConfig();
        
        // Préparer les données pour chaque main
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

        return $this->render('hand_level/index.html.twig', [
            'partie' => $partie,
            'handsData' => $handsData,
        ]);
    }

    /**
     * Mettre à jour le niveau d'une main
     */
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

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('hand_level_update', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
        }

        $handLevel = $partie->getHandLevel();
        
        // Vérifier que le type de main est valide
        $validHandTypes = array_keys($this->getHandsConfig());
        if (!in_array($handType, $validHandTypes)) {
            $this->addFlash('error', 'Type de main invalide.');
            return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
        }

        // Mettre à jour le niveau
        $getter = 'get' . ucfirst($handType);
        $setter = 'set' . ucfirst($handType);
        
        $currentLevel = $handLevel->$getter();
        $newLevel = max(0, $currentLevel + $amount); // Minimum 0
        
        $handLevel->$setter($newLevel);
        $em->flush();

        $handName = $this->getHandsConfig()[$handType]['name'];
        $this->addFlash('success', "Niveau de {$handName} mis à jour : {$newLevel}");

        return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
    }

    /**
     * Réinitialiser tous les niveaux à 0
     */
    #[Route('/reset', name: 'hand_levels_reset', methods: ['POST'])]
    public function reset(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            throw $this->createNotFoundException('Partie non trouvée');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('hand_levels_reset', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
        }

        $handLevel = $partie->getHandLevel();
        
        // Réinitialiser tous les niveaux à 0
        foreach (array_keys($this->getHandsConfig()) as $handType) {
            $setter = 'set' . ucfirst($handType);
            $handLevel->$setter(0);
        }
        
        $em->flush();

        $this->addFlash('success', 'Tous les niveaux ont été réinitialisés à 0.');

        return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
    }

    /**
     * Améliorer toutes les mains de 1 niveau (effet Black Hole)
     */
    #[Route('/upgrade-all', name: 'hand_levels_upgrade_all', methods: ['POST'])]
    public function upgradeAll(int $partieId, Request $request, EntityManagerInterface $em): Response
    {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            throw $this->createNotFoundException('Partie non trouvée');
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('hand_levels_upgrade_all', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
        }

        $handLevel = $partie->getHandLevel();
        
        // Augmenter tous les niveaux de 1
        foreach (array_keys($this->getHandsConfig()) as $handType) {
            $getter = 'get' . ucfirst($handType);
            $setter = 'set' . ucfirst($handType);
            $handLevel->$setter($handLevel->$getter() + 1);
        }
        
        $em->flush();

        $this->addFlash('success', '🕳️ Toutes les mains ont été améliorées de 1 niveau (Black Hole) !');

        return $this->redirectToRoute('hand_levels_index', ['partieId' => $partieId]);
    }
}
