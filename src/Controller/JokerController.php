<?php

namespace App\Controller;

use App\Entity\Joker;
use App\Form\JokerType;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/joker')]
class JokerController extends AbstractController
{
    // Créer un nouveau joker
    #[Route('/new', name: 'joker_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LoggerInterface $logger): Response
    {
        $joker = new Joker();
        $form = $this->createForm(JokerType::class, $joker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Log des informations du joker créé
            $logger->info('Joker créé: ' . $joker->getNom() . ' - ' . $joker->getEtat() . ' - ' . $joker->getRarete());
            
            // Pour l'instant, on affiche juste un message de succès
            // Plus tard, tu sauvegarderas en base de données
            $this->addFlash('success', 'Joker créé avec succès !');

            return $this->redirectToRoute('about');
        }

        return $this->render('joker/new.html.twig', [
            'joker' => $joker,
            'jokerForm' => $form,
        ]);
    }

    // Afficher un joker spécifique
    #[Route('/{id}', name: 'joker_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        // Pour l'instant, on crée un joker fictif
        $joker = new Joker();
        $joker->setNom('Joker Exemple')
              ->setEtat('normale')
              ->setRarete('commun')
              ->setDescription('Un joker de test')
              ->setEffet('Effet de test');

        return $this->render('joker/show.html.twig', [
            'joker' => $joker,
        ]);
    }

    // Modifier un joker
    #[Route('/{id}/edit', name: 'joker_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        // Pour l'instant, on crée un joker fictif
        $joker = new Joker();
        $joker->setNom('Joker à modifier')
              ->setEtat('normale')
              ->setRarete('commun')
              ->setDescription('Description à modifier')
              ->setEffet('Effet à modifier');

        $form = $this->createForm(JokerType::class, $joker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Joker modifié avec succès !');

            return $this->redirectToRoute('about');
        }

        return $this->render('joker/edit.html.twig', [
            'joker' => $joker,
            'jokerForm' => $form,
        ]);
    }

    // Supprimer un joker
    #[Route('/{id}/delete', name: 'joker_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        // Vérification du token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $this->addFlash('success', 'Joker supprimé avec succès !');
        }

        return $this->redirectToRoute('about');
    }
}
