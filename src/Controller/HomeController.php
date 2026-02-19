<?php

namespace App\Controller;

use App\Entity\JokerTemplate;
use App\Form\JokerFilterType;
use App\Form\JokerTemplateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Validator\Constraints\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



class HomeController extends AbstractController
{
    // Page "About" qui affiche le dictionnaire de tous les jokers (avec filtres par nom/rareté et tri)
    #[Route("/about", name:"about")]
    public function about(Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer tous les joker templates qui existent
        $allJokers = $em->getRepository(JokerTemplate::class)->findAll();
        
        // Créer le formulaire avec les filtres (nom, rareté, tri)
        $filterForm = $this->createForm(JokerFilterType::class);
        $filterForm->handleRequest($request);
        
        // Appliquer les filtres sur la liste de jokers
        $filteredJokers = $this->filterJokers($allJokers, $filterForm->getData());
        
        return $this->render("dictionary/dictionary.html.twig", [
            'filterForm' => $filterForm->createView(),
            'jokers' => $filteredJokers,
            'totalJokers' => count($allJokers)
        ]);
    }
    
    // Fonction qui filtre et trie les jokers selon les critères choisis
    private function filterJokers(array $jokers, ?array $filters): array
    {
        // Si pas de filtres, on renvoie tout
        if (!$filters) {
            return $jokers;
        }
        
        // Filtrage (on ne garde que les jokers qui correspondent aux critères)
        $filtered = array_filter($jokers, function(JokerTemplate $joker) use ($filters) {
            // Filtre par nom (recherche simple dans le nom du joker)
            if (!empty($filters['nom'])) {
                $searchTerm = strtolower($filters['nom']);
                $jokerName = strtolower($joker->getNom());
                if (strpos($jokerName, $searchTerm) === false) {
                    return false;
                }
            }
            
            // Filtre par rareté (commun, uncommun, rare, legendary)
            if (!empty($filters['rarete'])) {
                // Comparer avec la valeur de l'ENUM (pas l'objet entier)
                if ($joker->getRarete()->value !== $filters['rarete']) {
                    return false;
                }
            }
            
            return true;
        });
        
        // Tri (ordre alphabétique ou rareté croissant/décroissant)
        if (!empty($filters['tri'])) {
            $filtered = array_values($filtered); // Réindexer le tableau pour éviter les bugs de tri
            
            switch ($filters['tri']) {
                case 'nom_asc':
                    usort($filtered, fn($a, $b) => strcasecmp($a->getNom(), $b->getNom())); // A->Z
                    break;
                case 'nom_desc':
                    usort($filtered, fn($a, $b) => strcasecmp($b->getNom(), $a->getNom())); // Z->A
                    break;
                case 'rarete_asc':
                    // Ordre croissant : commun < uncommun < rare < legendary
                    $order = ['commun' => 1, 'uncommun' => 2, 'rare' => 3, 'legendary' => 4];
                    usort($filtered, fn($a, $b) => ($order[$a->getRarete()->value] ?? 0) <=> ($order[$b->getRarete()->value] ?? 0));
                    break;
                case 'rarete_desc':
                    // Ordre décroissant : legendary > rare > uncommun > commun
                    $order = ['legendary' => 1, 'rare' => 2, 'uncommun' => 3, 'commun' => 4];
                    usort($filtered, fn($a, $b) => ($order[$a->getRarete()->value] ?? 0) <=> ($order[$b->getRarete()->value] ?? 0));
                    break;
            }
        }
        
        return $filtered;
    }

    // Créer un nouveau joker template (route du TP, pas utilisée dans l'app finale)
    #[Route("/joker/new", name:"joker_new")]
    public function newJoker(Request $request, EntityManagerInterface $em): Response
    {
        // Instancier une nouvelle entité JokerTemplate vide
        $jokerTemplate = new JokerTemplate();
        
        // Créer le formulaire à partir du Type
        $form = $this->createForm(JokerTemplateType::class, $jokerTemplate);
        
        // Écouter la requête (si le formulaire a été soumis, les données sont injectées)
        $form->handleRequest($request);
        
        // Vérifier si le formulaire a été soumis ET est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le joker validé
            $validatedJoker = $form->getData();
            
            // Sauvegarder en base
            $em->persist($validatedJoker);
            $em->flush();
            
            $this->addFlash('success', 'Le joker template "' . $validatedJoker->getNom() . '" a été créé avec succès !');
            
            return $this->redirectToRoute('about');
        }
        
        return $this->render('joker/new.html.twig', [
            'jokerForm' => $form->createView()
        ]);
    }
}
?>