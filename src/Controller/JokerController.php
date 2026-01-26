<?php

namespace App\Controller;

use App\Entity\Joker;
use App\Form\JokerType;
use Doctrine\ORM\EntityManagerInterface;
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
    public function new(Request $request, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        $joker = new Joker();
        $form = $this->createForm(JokerType::class, $joker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Log des informations du joker créé
            $logger->info('Joker créé: ' . $joker->getNom() . ' - ' . $joker->getEtat()->value . ' - ' . $joker->getRarete()->value);
            
            // Sauvegarder en base de données
            $em->persist($joker);  // Prépare l'objet pour la sauvegarde
            $em->flush();          // Exécute la requête INSERT en base
            
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
    public function edit(Request $request, EntityManagerInterface $em, int $id): Response
    {
        // Récupérer le joker depuis la BDD
        $joker = $em->getRepository(Joker::class)->find($id);
        
        // Si le joker n'existe pas, erreur 404
        if (!$joker) {
            throw $this->createNotFoundException('Le joker avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $form = $this->createForm(JokerType::class, $joker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Pas besoin de persist() car l'objet existe déjà en BDD
            $em->flush();  // Met à jour les modifications
            
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
    public function delete(Request $request, EntityManagerInterface $em, int $id): Response
    {
        // Récupérer le joker depuis la BDD
        $joker = $em->getRepository(Joker::class)->find($id);
        
        if ($joker) {
            // Vérification du token CSRF pour la sécurité
            if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
                $em->remove($joker);  // Prépare la suppression
                $em->flush();         // Exécute le DELETE en BDD
                
                $this->addFlash('success', 'Joker supprimé avec succès !');
            }
        } else {
            $this->addFlash('error', 'Joker introuvable.');
        }

        return $this->redirectToRoute('about');
    }
}
