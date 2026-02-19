<?php

namespace App\Controller;

use App\Entity\Partie;
use App\Entity\User;
use App\Entity\JokerInstance;
use App\Entity\JokerTemplate;
use App\Entity\HandLevel;
use App\Entity\Carte;
use App\Entity\Consommable;
use App\Enum\EtatJoker;
use App\Enum\CarteNumber;
use App\Enum\CarteColor;
use App\Enum\CarteStatus;
use App\Enum\CarteStatusSeal;
use App\Enum\CarteStatusMatter;
use App\Enum\ConsommableCategory;
use App\Enum\ConsommableType;
use App\Enum\ConsommableStatus;
use App\Form\JokerInstanceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/partie')]
class PartieController extends AbstractController
{
    //Afficher toutes les parties de l'utilisateur connecté
    #[Route('/', name: 'partie_index')]
    public function index(EntityManagerInterface $em): Response
    {
        // On récupère seulement les parties qui appartiennent à l'utilisateur actuel (pas celles des autres !)
        $user = $this->getUser();
        $parties = $em->getRepository(Partie::class)->findBy(['user' => $user]);
        
        return $this->render('partie/index.html.twig', [
            'parties' => $parties,
        ]);
    }

    //Créer une nouvelle partie (avec les valeurs par défaut du jeu Balatro)
    #[Route('/new', name: 'partie_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Créer un HandLevel pour stocker les niveaux de mains (pair, brelan, etc.) - tous à 0 au départ
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

    //Afficher tous les détails d'une partie (jokers, cartes, consommables, stats, etc.)
    #[Route('/{id}', name: 'partie_show', requirements: ['id' => '\d+'])]
    public function show(Partie $partie, Request $request, EntityManagerInterface $em): Response
    {
        // Sécurité : vérifier que c'est bien NOTRE partie (pas celle de quelqu'un d'autre)
        if ($partie->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette partie.');
        }
        
        // Si on vient d'ajouter un joker via le formulaire, on le traite ici
        if ($request->isMethod('POST') && $request->request->has('joker_instance')) {
            $data = $request->request->all('joker_instance');
            
            if (!$this->isCsrfTokenValid('joker_instance', $data['_token'])) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
            }
            
            // Récupérer le modèle de joker (le template = la définition du joker dans le catalogue)
            $jokerTemplate = $em->getRepository(JokerTemplate::class)->find($data['jokerTemplate']);
            
            if (!$jokerTemplate) {
                $this->addFlash('error', 'Joker template non trouvé.');
                return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
            }
            
            // Les jokers négatifs ont une propriété spéciale : ils augmentent le nombre de slots disponibles !
            $isNegatif = !empty($data['etat']) && $data['etat'] === 'negatif';
            
            // Vérifier qu'on a de la place pour ajouter le joker (SAUF si c'est un joker négatif qui crée son propre slot)
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

    //Retirer un joker de la partie
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

    //Déplacer un joker à gauche ou à droite dans la liste (pour réorganiser l'ordre des jokers)
    #[Route('/{partieId}/joker/{jokerId}/move/{direction}', name: 'partie_joker_move', methods: ['POST'])]
    public function moveJoker(
        int $partieId,
        int $jokerId,
        string $direction,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('joker_move_' . $jokerId, $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_show', ['id' => $partieId]);
        }

        $jokerInstance = $em->getRepository(JokerInstance::class)->find($jokerId);
        
        if (!$jokerInstance || $jokerInstance->getPartie()->getId() !== $partieId) {
            $this->addFlash('error', 'Joker non trouvé.');
            return $this->redirectToRoute('partie_show', ['id' => $partieId]);
        }

        $partie = $jokerInstance->getPartie();
        $currentOrdre = $jokerInstance->getOrdre();

        // On va trouver le joker adjacent (celui juste à côté) pour échanger leur position
        $repository = $em->getRepository(JokerInstance::class);
        
        if ($direction === 'left') {
            // Chercher le joker juste avant (celui avec l'ordre le plus proche mais inférieur)
            $adjacentJoker = $repository->createQueryBuilder('j')
                ->where('j.partie = :partie')
                ->andWhere('j.ordre < :ordre')
                ->setParameter('partie', $partie)
                ->setParameter('ordre', $currentOrdre)
                ->orderBy('j.ordre', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } else {
            // Chercher le joker juste après (celui avec l'ordre le plus proche mais supérieur)
            $adjacentJoker = $repository->createQueryBuilder('j')
                ->where('j.partie = :partie')
                ->andWhere('j.ordre > :ordre')
                ->setParameter('partie', $partie)
                ->setParameter('ordre', $currentOrdre)
                ->orderBy('j.ordre', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        if (!$adjacentJoker) {
            $this->addFlash('error', 'Impossible de déplacer le joker dans cette direction.');
            return $this->redirectToRoute('partie_show', ['id' => $partieId]);
        }

        // On échange simplement les numéros d'ordre entre les deux jokers (comme échanger deux cartes dans un jeu)
        $adjacentOrdre = $adjacentJoker->getOrdre();
        $jokerInstance->setOrdre($adjacentOrdre);
        $adjacentJoker->setOrdre($currentOrdre);

        $em->flush();

        $this->addFlash('success', 'Joker déplacé.');

        return $this->redirectToRoute('partie_show', ['id' => $partieId]);
    }

    //Supprimer une partie complète
    #[Route('/{id}/delete', name: 'partie_delete', methods: ['POST'])]
    public function delete(Partie $partie, EntityManagerInterface $em): Response
    {
        $em->remove($partie);
        $em->flush();
        
        $this->addFlash('success', 'Partie supprimée avec succès.');
        
        return $this->redirectToRoute('partie_index');
    }

    //Modifier le nom de la partie
    #[Route('/{id}/update-name', name: 'partie_update_name', methods: ['POST'])]
    public function updateName(Partie $partie, Request $request, EntityManagerInterface $em): Response
    {
        // Validation du token CSRF (protection contre les failles de sécurité)
        if (!$this->isCsrfTokenValid('update_name_' . $partie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }
        
        $newName = $request->request->get('identifiant');
        
        // Validation simple
        if (empty($newName) || strlen($newName) > 100) {
            $this->addFlash('error', 'Le nom de la partie doit contenir entre 1 et 100 caractères.');
            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }
        
        $partie->setIdentifiant($newName);
        $em->flush();
        
        $this->addFlash('success', 'Nom de la partie modifié avec succès.');
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    //Modifier l'argent de la partie (ajouter ou enlever des $)
    #[Route('/{id}/money/{amount}', name: 'partie_money_update')]
    public function updateMoney(Partie $partie, int $amount, EntityManagerInterface $em): Response
    {
        $partie->setMoney($partie->getMoney() + $amount);
        $em->flush();
        
        $message = $amount > 0 ? "+$amount $" : "$amount $";
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    //Modifier le nombre de cartes qu'on peut avoir en main
    #[Route('/{id}/hand/{amount}', name: 'partie_hand_update')]
    public function updateHand(Partie $partie, int $amount, EntityManagerInterface $em): Response
    {
        $newValue = $partie->getHand() + $amount;
        if ($newValue < 1) {
            $newValue = 1; // On ne peut pas avoir moins de 1 carte en main (sinon on ne peut plus jouer !)
        }
        $partie->setHand($newValue);
        $em->flush();
        
        $message = $amount > 0 ? "+$amount main" : "$amount main";
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    //Modifier le nombre de défausses disponibles par tour
    #[Route('/{id}/discard/{amount}', name: 'partie_discard_update')]
    public function updateDiscard(Partie $partie, int $amount, EntityManagerInterface $em): Response
    {
        $newValue = $partie->getDiscard() + $amount;
        if ($newValue < 0) {
            $newValue = 0; // On peut avoir 0 défausses (c'est dur mais possible !)
        }
        $partie->setDiscard($newValue);
        $em->flush();
        
        $message = $amount > 0 ? "+$amount défausse" : "$amount défausse";
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    //Modifier le nombre de slots pour les jokers (combien de jokers on peut avoir en même temps)
    #[Route('/{id}/joker-slots/{amount}', name: 'partie_joker_slots_update')]
    public function updateJokerSlots(Partie $partie, int $amount, EntityManagerInterface $em): Response
    {
        $newValue = $partie->getJokerSlots() + $amount;
        if ($newValue < 1) {
            $newValue = 1; // Minimum 1 slot sinon on ne peut plus avoir de jokers
        }
        if ($newValue > 10) {
            $newValue = 10; // Maximum 10 slots pour éviter les abus (et c'est déjà énorme !)
        }
        $partie->setJokerSlots($newValue);
        $em->flush();
        
        $message = $amount > 0 ? "+$amount slot joker" : "$amount slot joker";
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    //API pour chercher des jokers par nom (utilisée par l'autocomplétion en AJAX)
    #[Route('/api/jokers/search', name: 'api_jokers_search')]
    public function searchJokers(Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer le texte de recherche (q = query)
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

    //Activer ou désactiver l'observatoire (qui donne +1 slot de joker quand il est actif)
    #[Route('/{id}/toggle-observatoire', name: 'partie_toggle_observatoire', methods: ['POST'])]
    public function toggleObservatoire(Partie $partie, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('toggle_observatoire_' . $partie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
        }

        $partie->setObservatoireActif(!$partie->isObservatoireActif());
        $em->flush();

        $status = $partie->isObservatoireActif() ? 'activé' : 'désactivé';
        $this->addFlash('success', "L'observatoire a été {$status}.");
        
        return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);
    }

    //Modifier le compteur de stacks d'un joker (pour les jokers qui gagnent en puissance au fil du temps)
    #[Route('/{partieId}/joker/{jokerId}/stack/{amount}', name: 'partie_joker_stack_update', methods: ['POST'])]
    public function updateJokerStack(
        int $partieId,
        int $jokerId,
        int $amount,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $partie = $em->getRepository(Partie::class)->find($partieId);
        
        if (!$partie) {
            throw $this->createNotFoundException('Partie non trouvée');
        }

        // Trouver le joker dans la partie
        $jokerInstance = null;
        foreach ($partie->getJokers() as $joker) {
            if ($joker->getId() === $jokerId) {
                $jokerInstance = $joker;
                break;
            }
        }

        if (!$jokerInstance) {
            $this->addFlash('error', 'Joker non trouvé dans cette partie.');
            return $this->redirectToRoute('partie_show', ['id' => $partieId]);
        }

        if (!$this->isCsrfTokenValid('joker_stack_update_' . $jokerId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_show', ['id' => $partieId]);
        }

        // Modifier le nombre de stacks (minimum 0, on ne peut pas avoir de stacks négatifs)
        $newStack = max(0, $jokerInstance->getCompteurStack() + $amount);
        $jokerInstance->setCompteurStack($newStack);
        
        $em->flush();

        $this->addFlash('success', "Stack de {$jokerInstance->getJokerTemplate()->getNom()} mis à jour : {$newStack}");

        return $this->redirectToRoute('partie_show', ['id' => $partieId]);
    }

    //Exporter une partie en fichier JSON (pour faire des sauvegardes ou partager avec d'autres)
    #[Route('/{id}/export', name: 'partie_export', requirements: ['id' => '\d+'])]
    public function export(Partie $partie): Response
    {
        // Vérifier que c'est bien notre partie qu'on veut exporter
        if ($partie->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas exporter cette partie.');
        }

        // Construire la structure JSON avec toutes les données de la partie
        $data = [
            'partie' => [
                'identifiant' => $partie->getIdentifiant(),
                'money' => $partie->getMoney(),
                'hand' => $partie->getHand(),
                'discard' => $partie->getDiscard(),
                'totalJokerSlots' => $partie->getTotalJokerSlots(),
                'observatoireActif' => $partie->isObservatoireActif(),
            ],
            'jokers' => [],
            'cartes' => [],
            'consommables' => [],
            'handLevel' => null,
        ];

        // Exporter les jokers (juste les infos nécessaires, pas tout l'objet)
        foreach ($partie->getJokers() as $jokerInstance) {
            $data['jokers'][] = [
                'templateId' => $jokerInstance->getJokerTemplate()->getId(),
                'etat' => $jokerInstance->getEtat()?->value,
                'ordre' => $jokerInstance->getOrdre(),
                'compteurStack' => $jokerInstance->getCompteurStack(),
            ];
        }

        // Exporter les cartes du deck
        foreach ($partie->getCartes() as $carte) {
            $data['cartes'][] = [
                'number' => $carte->getNumber()->value,
                'color' => $carte->getColor()->value,
                'status' => $carte->getStatus()->value,
                'seal' => $carte->getSeal() ? $carte->getSeal()->value : null,
                'matter' => $carte->getMatter()->value,
            ];
        }

        // Exporter les consommables (tarots, planètes, spectres)
        foreach ($partie->getConsommables() as $consommable) {
            $data['consommables'][] = [
                'name' => $consommable->getName(),
                'description' => $consommable->getDescription(),
                'category' => $consommable->getCategory()->value,
                'type' => $consommable->getType()->value,
                'status' => $consommable->getStatus()->value,
            ];
        }

        // Exporter les niveaux de mains (pair, brelan, quinte, etc.)
        if ($partie->getHandLevel()) {
            $handLevel = $partie->getHandLevel();
            $data['handLevel'] = [
                'highCard' => $handLevel->getHighCard(),
                'pair' => $handLevel->getPair(),
                'twoPair' => $handLevel->getTwoPair(),
                'threeOfAKind' => $handLevel->getThreeOfAKind(),
                'straight' => $handLevel->getStraight(),
                'flush' => $handLevel->getFlush(),
                'fullHouse' => $handLevel->getFullHouse(),
                'fourOfAKind' => $handLevel->getFourOfAKind(),
                'straightFlush' => $handLevel->getStraightFlush(),
                'royalFlush' => $handLevel->getRoyalFlush(),
            ];
        }

        // Générer le JSON bien formaté (avec indentation pour que ce soit lisible par un humain)
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Créer un nom de fichier propre à partir de l'identifiant (on remplace les caractères bizarres par des underscores)
        $filename = 'partie_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $partie->getIdentifiant()) . '.json';

        // Renvoyer le fichier en téléchargement direct (status 200 = tout va bien)
        return new Response(
            $jsonContent,
            200,
            [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    //Importer une partie depuis un fichier JSON (pour restaurer une sauvegarde ou récupérer une partie partagée)
    // GET = afficher le formulaire d'upload, POST = traiter le fichier
    #[Route('/import', name: 'partie_import', methods: ['GET', 'POST'])]
    public function import(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            /** @var UploadedFile $file */
            $file = $request->files->get('json_file');
            if (!$file) {
                $this->addFlash('error', 'Aucun fichier sélectionné.');
                return $this->redirectToRoute('partie_import');
            }

            // Vérifier que c'est bien un fichier .json
            if ($file->getClientOriginalExtension() !== 'json') {
                $this->addFlash('error', 'Le fichier doit être au format JSON.');
                return $this->redirectToRoute('partie_import');
            }

            // Vérification MIME (sécurité supplémentaire - on vérifie le vrai contenu, pas juste l'extension)
            // application/json = JSON standard, text/plain = parfois utilisé pour les .json selon l'OS
            if (!in_array($file->getMimeType(), ['application/json', 'text/plain'])) {
                $this->addFlash('error', 'Type de fichier invalide. Seuls les fichiers JSON sont acceptés.');
                return $this->redirectToRoute('partie_import');
            }

            // Lire et décoder le contenu JSON
            $jsonContent = file_get_contents($file->getPathname());
            $data = json_decode($jsonContent, true);

            // Vérifier que le JSON est valide (pas corrompu ou mal formé)
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addFlash('error', 'Fichier JSON invalide : ' . json_last_error_msg());
                return $this->redirectToRoute('partie_import');
            }

            // Vérifier que le JSON a bien la bonne structure (avec les sections partie, jokers, cartes, consommables)
            if (!isset($data['partie']) || !isset($data['jokers']) || !isset($data['cartes']) || !isset($data['consommables'])) {
                $this->addFlash('error', 'Structure JSON invalide. Sections manquantes.');
                return $this->redirectToRoute('partie_import');
            }

            try {
                // Créer le HandLevel (les niveaux de mains)
                $handLevel = new HandLevel();
                if (isset($data['handLevel'])) {
                    $handLevel->setHighCard($data['handLevel']['highCard'] ?? 0);
                    $handLevel->setPair($data['handLevel']['pair'] ?? 0);
                    $handLevel->setTwoPair($data['handLevel']['twoPair'] ?? 0);
                    $handLevel->setThreeOfAKind($data['handLevel']['threeOfAKind'] ?? 0);
                    $handLevel->setStraight($data['handLevel']['straight'] ?? 0);
                    $handLevel->setFlush($data['handLevel']['flush'] ?? 0);
                    $handLevel->setFullHouse($data['handLevel']['fullHouse'] ?? 0);
                    $handLevel->setFourOfAKind($data['handLevel']['fourOfAKind'] ?? 0);
                    $handLevel->setStraightFlush($data['handLevel']['straightFlush'] ?? 0);
                    $handLevel->setRoyalFlush($data['handLevel']['royalFlush'] ?? 0);
                } else {
                    // Si pas de HandLevel dans le JSON, on met tout à 0
                    $handLevel->setHighCard(0)->setPair(0)->setTwoPair(0)->setThreeOfAKind(0)
                        ->setStraight(0)->setFlush(0)->setFullHouse(0)->setFourOfAKind(0)
                        ->setStraightFlush(0)->setRoyalFlush(0);
                }
                $em->persist($handLevel);

                // Recréer la partie avec toutes ses données
                $partie = new Partie();
                $partie->setUser($this->getUser());
                $partie->setIdentifiant($data['partie']['identifiant'] ?? 'Partie importée');
                $partie->setMoney($data['partie']['money'] ?? 0);
                $partie->setHand($data['partie']['hand'] ?? 8);
                $partie->setDiscard($data['partie']['discard'] ?? 3);
                $partie->setJokerSlots($data['partie']['totalJokerSlots'] ?? 5);
                $partie->setObservatoireActif($data['partie']['observatoireActif'] ?? false);
                $partie->setHandLevel($handLevel);
                $em->persist($partie);

                // Importer les jokers un par un
                foreach ($data['jokers'] as $jokerData) {
                    $template = $em->getRepository(JokerTemplate::class)->find($jokerData['templateId']);
                    if (!$template) {
                        $this->addFlash('warning', "Joker template ID {$jokerData['templateId']} introuvable, ignoré.");
                        continue; // Si le joker n'existe pas, on continue sans lui (au lieu de tout planter)
                    }

                    $jokerInstance = new JokerInstance();
                    $jokerInstance->setJokerTemplate($template);
                    $jokerInstance->setPartie($partie);
                    // Restaurer l'état exact du joker (null si c'était un joker de base)
                    if (array_key_exists('etat', $jokerData)) {
                        $jokerInstance->setEtat($jokerData['etat'] !== null ? EtatJoker::from($jokerData['etat']) : null);
                    }
                    $jokerInstance->setOrdre($jokerData['ordre'] ?? 0);
                    $jokerInstance->setCompteurStack($jokerData['compteurStack'] ?? 0);
                    $em->persist($jokerInstance);
                }

                // Importer les cartes du deck (on crée toujours de nouvelles cartes pour la partie)
                foreach ($data['cartes'] as $carteData) {
                    $carte = new Carte();
                    $carte->setNumber(CarteNumber::from($carteData['number']));
                    $carte->setColor(CarteColor::from($carteData['color']));
                    $carte->setStatus(CarteStatus::from($carteData['status']));
                    $carte->setSeal($carteData['seal'] ? CarteStatusSeal::from($carteData['seal']) : null);
                    $carte->setMatter(CarteStatusMatter::from($carteData['matter']));
                    $em->persist($carte);
                    $partie->addCarte($carte);
                }

                // Importer les consommables (on crée toujours de nouveaux consommables pour la partie)
                foreach ($data['consommables'] as $consommableData) {
                    $consommable = new Consommable();
                    $consommable->setName($consommableData['name']);
                    $consommable->setDescription($consommableData['description'] ?? '');
                    $consommable->setCategory(ConsommableCategory::from($consommableData['category']));
                    $consommable->setType(ConsommableType::from($consommableData['type']));
                    $consommable->setStatus(ConsommableStatus::from($consommableData['status']));
                    $em->persist($consommable);
                    $partie->addConsommable($consommable);
                }

                $em->flush();

                $this->addFlash('success', 'Partie importée avec succès !');
                return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);

            } catch (\Exception $e) {
                // Si quelque chose se passe mal, on affiche l'erreur au lieu de tout planter
                $this->addFlash('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
                return $this->redirectToRoute('partie_import');
            }
        }

        // Si on arrive ici c'est qu'on veut juste afficher le formulaire d'upload
        return $this->render('partie/import.html.twig');
    }
}
