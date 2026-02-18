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
    #[Route("/about", name:"about")]
    public function about(Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer TOUS les joker templates depuis la base de données
        $allJokers = $em->getRepository(JokerTemplate::class)->findAll();
        
        // Créer le formulaire de filtre
        $filterForm = $this->createForm(JokerFilterType::class);
        $filterForm->handleRequest($request);
        
        // Filtrer les jokers selon les critères
        $filteredJokers = $this->filterJokers($allJokers, $filterForm->getData());
        
        return $this->render("about.html.twig", [
            'filterForm' => $filterForm->createView(),
            'jokers' => $filteredJokers,
            'totalJokers' => count($allJokers)
        ]);
    }
    
    private function filterJokers(array $jokers, ?array $filters): array
    {
        if (!$filters) {
            return $jokers;
        }
        
        // Filtrage
        $filtered = array_filter($jokers, function(JokerTemplate $joker) use ($filters) {
            // Filtre par nom
            if (!empty($filters['nom'])) {
                $searchTerm = strtolower($filters['nom']);
                $jokerName = strtolower($joker->getNom());
                if (strpos($jokerName, $searchTerm) === false) {
                    return false;
                }
            }
            
            // Filtre par rareté (comparer avec la valeur de l'ENUM)
            if (!empty($filters['rarete'])) {
                if ($joker->getRarete()->value !== $filters['rarete']) {
                    return false;
                }
            }
            
            return true;
        });
        
        // Tri
        if (!empty($filters['tri'])) {
            $filtered = array_values($filtered); // Réindexer
            
            switch ($filters['tri']) {
                case 'nom_asc':
                    usort($filtered, fn($a, $b) => strcasecmp($a->getNom(), $b->getNom()));
                    break;
                case 'nom_desc':
                    usort($filtered, fn($a, $b) => strcasecmp($b->getNom(), $a->getNom()));
                    break;
                case 'rarete_asc':
                    $order = ['commun' => 1, 'uncommun' => 2, 'rare' => 3, 'legendary' => 4];
                    usort($filtered, fn($a, $b) => ($order[$a->getRarete()->value] ?? 0) <=> ($order[$b->getRarete()->value] ?? 0));
                    break;
                case 'rarete_desc':
                    $order = ['legendary' => 1, 'rare' => 2, 'uncommun' => 3, 'commun' => 4];
                    usort($filtered, fn($a, $b) => ($order[$a->getRarete()->value] ?? 0) <=> ($order[$b->getRarete()->value] ?? 0));
                    break;
            }
        }
        
        return $filtered;
    }

    #[Route("/joker/new", name:"joker_new")]
    public function newJoker(Request $request, EntityManagerInterface $em): Response
    {
        // 1. Instancier une nouvelle entité JokerTemplate
        $jokerTemplate = new JokerTemplate();
        
        // 2. Créer le formulaire à partir du Type
        $form = $this->createForm(JokerTemplateType::class, $jokerTemplate);
        
        // 3. Écouter la requête
        $form->handleRequest($request);
        
        // 4. Vérifier si le formulaire a été soumis ET valide
        if ($form->isSubmitted() && $form->isValid()) {
            // 5. Récupérer les données validées
            $validatedJoker = $form->getData();
            
            // 6. Sauvegarder en base de données
            $em->persist($validatedJoker);
            $em->flush();
            
            // Message de succès
            $this->addFlash('success', 'Le joker template "' . $validatedJoker->getNom() . '" a été créé avec succès !');
            
            // Rediriger vers la liste
            return $this->redirectToRoute('about');
        }
        
        // Afficher le formulaire
        return $this->render('joker/new.html.twig', [
            'jokerForm' => $form->createView()
        ]);
    }

    #[Route("/hello/{name}", name:"hello")]
    public function hello($name): Response
    {
        return $this->render("hello.html.twig", ["name"=>ucfirst($name)]);
    }


    #[Route("/random", name:"random")]
    public function random(): Response
    {
        $quotes = [
            "follow the white rabbit",
            "may the force be with you",
            "I'll be back",
            "you shall not pass"
        ];
        $randomQuote = $quotes[random_int(0,sizeof($quotes)-1)];
        return $this->render("random.html.twig", ["quote"=>$randomQuote, "allquotes"=>$quotes]);
    }
}
?>