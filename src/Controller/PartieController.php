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
    /**
     * Liste toutes les parties de l'utilisateur connecté
     */
    #[Route('/', name: 'partie_index')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer uniquement les parties de l'utilisateur connecté
        $user = $this->getUser();
        $parties = $em->getRepository(Partie::class)->findBy(['user' => $user]);
        
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
        // Utiliser l'utilisateur connecté
        $user = $this->getUser();

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
        // Vérifier que la partie appartient à l'utilisateur connecté
        if ($partie->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette partie.');
        }
        
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

    /**
     * Toggle l'observatoire
     */
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

    /**
     * Modifier le compteurStack d'un joker
     */
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

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('joker_stack_update_' . $jokerId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('partie_show', ['id' => $partieId]);
        }

        // Mettre à jour le stack (minimum 0)
        $newStack = max(0, $jokerInstance->getCompteurStack() + $amount);
        $jokerInstance->setCompteurStack($newStack);
        
        $em->flush();

        $this->addFlash('success', "Stack de {$jokerInstance->getJokerTemplate()->getNom()} mis à jour : {$newStack}");

        return $this->redirectToRoute('partie_show', ['id' => $partieId]);
    }

    /**
     * Exporter une partie en JSON
     */
    #[Route('/{id}/export', name: 'partie_export', requirements: ['id' => '\d+'])]
    public function export(Partie $partie): Response
    {
        // Vérifier que l'utilisateur est propriétaire de la partie
        if ($partie->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas exporter cette partie.');
        }

        // Construire la structure JSON
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

        // Exporter les jokers
        foreach ($partie->getJokers() as $jokerInstance) {
            $data['jokers'][] = [
                'templateId' => $jokerInstance->getJokerTemplate()->getId(),
                'etat' => $jokerInstance->getEtat()?->value,
                'ordre' => $jokerInstance->getOrdre(),
                'compteurStack' => $jokerInstance->getCompteurStack(),
            ];
        }

        // Exporter les cartes
        foreach ($partie->getCartes() as $carte) {
            $data['cartes'][] = [
                'number' => $carte->getNumber()->value,
                'color' => $carte->getColor()->value,
                'status' => $carte->getStatus()->value,
                'seal' => $carte->getSeal() ? $carte->getSeal()->value : null,
                'matter' => $carte->getMatter()->value,
            ];
        }

        // Exporter les consommables
        foreach ($partie->getConsommables() as $consommable) {
            $data['consommables'][] = [
                'name' => $consommable->getName(),
                'description' => $consommable->getDescription(),
                'category' => $consommable->getCategory()->value,
                'type' => $consommable->getType()->value,
                'status' => $consommable->getStatus()->value,
            ];
        }

        // Exporter les hand levels
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

        // Générer le JSON formaté (fait pour rendre le json plus l'utilisateur qui serait curieux de regader le contenu)
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Créer un nom de fichier basé sur l'identifiant de la partie (ça fais du régex pour remplacer les caractères spéciaux par des underscores, simple sécurité)
        $filename = 'partie_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $partie->getIdentifiant()) . '.json';

        // Retourner le fichier JSON en téléchargement (on le voie justement avec le code 200, qui veut dire que tout s'est bien passé)
        return new Response(
            $jsonContent,
            200,
            [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    /**
     * Importer une partie depuis un fichier JSON
     */
    // ça prend la méthode get et poste. En get ça affiche le formulaire d'import, et en post ça traite le fichier uploadé
    #[Route('/import', name: 'partie_import', methods: ['GET', 'POST'])]
    public function import(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            /** @var UploadedFile $file */
            $file = $request->files->get('json_file');
            // vérifie qu'il y a un fichier uploadé
            if (!$file) {
                $this->addFlash('error', 'Aucun fichier sélectionné.');
                return $this->redirectToRoute('partie_import');
            }

            // Vérifier que c'est bien un fichier JSON (via l'extension)
            if ($file->getClientOriginalExtension() !== 'json') {
                $this->addFlash('error', 'Le fichier doit être au format JSON.');
                return $this->redirectToRoute('partie_import');
            }

            // Vérification MIME (sécurité supplémentaire - vérifie le contenu réel du fichier)
            // application/json = JSON standard, text/plain = parfois utilisé pour .json selon l'OS
            if (!in_array($file->getMimeType(), ['application/json', 'text/plain'])) {
                $this->addFlash('error', 'Type de fichier invalide. Seuls les fichiers JSON sont acceptés.');
                return $this->redirectToRoute('partie_import');
            }

            // Lire le contenu du fichier
            $jsonContent = file_get_contents($file->getPathname());
            $data = json_decode($jsonContent, true);

            // si l'import a échoué, alors message d'erreur et on redirige vers la page d'import (ça veut dire que le json est mal formé, ou que le contenu n'est pas du json du tout)
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addFlash('error', 'Fichier JSON invalide : ' . json_last_error_msg());
                return $this->redirectToRoute('partie_import');
            }

            // Valider la structure du JSON (vérifie que toutes les sections sont présentes. Sinon ce n'est pas le bon format et on ne prend pas, car ça veut dire que le fichier à été modifié d'une mauvaise manière)
            if (!isset($data['partie']) || !isset($data['jokers']) || !isset($data['cartes']) || !isset($data['consommables'])) {
                $this->addFlash('error', 'Structure JSON invalide. Sections manquantes.');
                return $this->redirectToRoute('partie_import');
            }

            try {
                // Créer le HandLevel
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
                    // Valeurs par défaut
                    $handLevel->setHighCard(0)->setPair(0)->setTwoPair(0)->setThreeOfAKind(0)
                        ->setStraight(0)->setFlush(0)->setFullHouse(0)->setFourOfAKind(0)
                        ->setStraightFlush(0)->setRoyalFlush(0);
                }
                $em->persist($handLevel);

                // Créer la partie
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

                // Importer les jokers
                foreach ($data['jokers'] as $jokerData) {
                    $template = $em->getRepository(JokerTemplate::class)->find($jokerData['templateId']);
                    if (!$template) {
                        $this->addFlash('warning', "Joker template ID {$jokerData['templateId']} introuvable, ignoré.");
                        continue;
                    }

                    $jokerInstance = new JokerInstance();
                    $jokerInstance->setJokerTemplate($template);
                    $jokerInstance->setPartie($partie);
                    // État optionnel - restaurer tel quel depuis l'export (null = état de base)
                    if (array_key_exists('etat', $jokerData)) {
                        $jokerInstance->setEtat($jokerData['etat'] !== null ? EtatJoker::from($jokerData['etat']) : null);
                    }
                    $jokerInstance->setOrdre($jokerData['ordre'] ?? 0);
                    $jokerInstance->setCompteurStack($jokerData['compteurStack'] ?? 0);
                    $em->persist($jokerInstance);
                }

                // Importer les cartes
                $carteRepository = $em->getRepository(Carte::class);
                foreach ($data['cartes'] as $carteData) {
                    // Chercher ou créer la carte
                    $carte = $carteRepository->findOneBy([
                        'number' => CarteNumber::from($carteData['number']),
                        'color' => CarteColor::from($carteData['color']),
                        'status' => CarteStatus::from($carteData['status']),
                        'seal' => $carteData['seal'] ? CarteStatusSeal::from($carteData['seal']) : null,
                        'matter' => CarteStatusMatter::from($carteData['matter']),
                    ]);

                    if (!$carte) {
                        $carte = new Carte();
                        $carte->setNumber(CarteNumber::from($carteData['number']));
                        $carte->setColor(CarteColor::from($carteData['color']));
                        $carte->setStatus(CarteStatus::from($carteData['status']));
                        $carte->setSeal($carteData['seal'] ? CarteStatusSeal::from($carteData['seal']) : null);
                        $carte->setMatter(CarteStatusMatter::from($carteData['matter']));
                        $em->persist($carte);
                    }

                    $partie->addCarte($carte);
                }

                // Importer les consommables
                $consommableRepository = $em->getRepository(Consommable::class);
                foreach ($data['consommables'] as $consommableData) {
                    // Chercher ou créer le consommable
                    $consommable = $consommableRepository->findOneBy([
                        'name' => $consommableData['name'],
                        'category' => ConsommableCategory::from($consommableData['category']),
                        'type' => ConsommableType::from($consommableData['type']),
                        'status' => ConsommableStatus::from($consommableData['status']),
                    ]);

                    if (!$consommable) {
                        $consommable = new Consommable();
                        $consommable->setName($consommableData['name']);
                        $consommable->setDescription($consommableData['description'] ?? '');
                        $consommable->setCategory(ConsommableCategory::from($consommableData['category']));
                        $consommable->setType(ConsommableType::from($consommableData['category']));
                        $consommable->setStatus(ConsommableStatus::from($consommableData['status']));
                        $em->persist($consommable);
                    }

                    $partie->addConsommable($consommable);
                }

                $em->flush();

                $this->addFlash('success', 'Partie importée avec succès !');
                return $this->redirectToRoute('partie_show', ['id' => $partie->getId()]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
                return $this->redirectToRoute('partie_import');
            }
        }

        // Afficher le formulaire d'upload
        return $this->render('partie/import.html.twig');
    }
}
