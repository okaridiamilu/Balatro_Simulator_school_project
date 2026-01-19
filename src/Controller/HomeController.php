<?php

namespace App\Controller;

use App\Entity\Joker;
use App\Form\JokerFilterType;
use App\Form\JokerType;
use Symfony\Bridge\Twig\Validator\Constraints\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



class HomeController extends AbstractController
{
    #[Route("/", name:"base")]
    public function base(): Response 
    {
        return $this->render("base.html.twig");
    }
    
    #[Route("/about", name:"about")]
    public function about(Request $request): Response
    {
        // Créer des jokers de démonstration (normalement depuis une base de données)
        $allJokers = $this->createSampleJokers();
        
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
    
    private function createSampleJokers(): array
    {
        $jokers = [];
        
        $joker1 = new Joker();
        $joker1->setNom('Joker Vampire')
            ->setEtat('foil')
            ->setRarete('rare')
            ->setDescription('Un joker effrayant avec des crocs acérés')
            ->setEffet('Gagne +3 mult pour chaque carte cœur jouée');
        $jokers[] = $joker1;
        
        $joker2 = new Joker();
        $joker2->setNom('Baron')
            ->setEtat('normale')
            ->setRarete('uncommon')
            ->setDescription('Un aristocrate calculateur')
            ->setEffet('Chaque Roi joué donne x1.5 mult');
        $jokers[] = $joker2;
        
        $joker3 = new Joker();
        $joker3->setNom('Triboulet')
            ->setEtat('polychrome')
            ->setRarete('legendary')
            ->setDescription('Le fou du roi le plus puissant')
            ->setEffet('Les Rois et Dames jouées donnent x2 mult');
        $jokers[] = $joker3;
        
        $joker4 = new Joker();
        $joker4->setNom('Joker Greedy')
            ->setEtat('chromatique')
            ->setRarete('rare')
            ->setDescription('Un joker avide de cartes de diamants')
            ->setEffet('+4 mult par carte diamant dans la main jouée');
        $jokers[] = $joker4;
        
        $joker5 = new Joker();
        $joker5->setNom('Scary Face')
            ->setEtat('normale')
            ->setRarete('commun')
            ->setDescription('Un visage terrifiant qui fait peur aux figures')
            ->setEffet('Les cartes figure donnent +30 jetons');
        $jokers[] = $joker5;
        
        $joker6 = new Joker();
        $joker6->setNom('Abstract Joker')
            ->setEtat('foil')
            ->setRarete('uncommon')
            ->setDescription('Un joker abstrait et minimaliste')
            ->setEffet('+3 mult par carte Joker possédée');
        $jokers[] = $joker6;
        
        return $jokers;
    }
    
    private function filterJokers(array $jokers, ?array $filters): array
    {
        if (!$filters) {
            return $jokers;
        }
        
        return array_filter($jokers, function(Joker $joker) use ($filters) {
            // Filtre par nom
            if (!empty($filters['nom'])) {
                $searchTerm = strtolower($filters['nom']);
                $jokerName = strtolower($joker->getNom());
                if (strpos($jokerName, $searchTerm) === false) {
                    return false;
                }
            }
            
            // Filtre par état
            if (!empty($filters['etat'])) {
                if ($joker->getEtat() !== $filters['etat']) {
                    return false;
                }
            }
            
            // Filtre par rareté
            if (!empty($filters['rarete'])) {
                if ($joker->getRarete() !== $filters['rarete']) {
                    return false;
                }
            }
            
            return true;
        });
    }

    #[Route("/joker/new", name:"joker_new")]
    public function newJoker(Request $request): Response
    {
        // 1. Instancier une nouvelle entité
        $joker = new Joker();
        
        // 2. Créer le formulaire à partir du Type
        $form = $this->createForm(JokerType::class, $joker);
        
        // 3. Écouter la requête
        $form->handleRequest($request);
        
        // 4. Vérifier si le formulaire a été soumis ET valide
        if ($form->isSubmitted() && $form->isValid()) {
            // 5. Récupérer les données validées
            $validatedJoker = $form->getData();
            
            // 6. Les données sont validées !
            // Note: Sans base de données, le joker n'est pas sauvegardé
            // Il existe seulement en mémoire pendant cette requête
            
            // Message de succès
            $this->addFlash('success', 'Le joker "' . $validatedJoker->getNom() . '" a été validé avec succès ! (Note: non sauvegardé car pas de BDD)');
            
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