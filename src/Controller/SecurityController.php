<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // Page d'accueil publique (accessible même sans être connecté)
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('security/home.html.twig');
    }

    // Formulaire de connexion
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si on est déjà connecté, on va direct aux parties (pas besoin de se reconnecter !)
        if ($this->getUser()) {
            return $this->redirectToRoute('partie_index');
        }

        // Récupérer l'erreur de connexion pour l'afficher (si mauvais mot de passe par exemple)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Garder le dernier nom d'utilisateur saisi (pour pas avoir à le retaper)
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // Déconnexion (gérée automatiquement par Symfony, pas besoin de coder)
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette fonction ne sera jamais exécutée car le firewall intercepte avant
        throw new \LogicException('Cette méthode peut être vide car elle sera interceptée par la clé logout de votre firewall.');
    }

    // Formulaire d'inscription pour créer un nouveau compte
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $em
    ): Response {
        // Si on est déjà connecté, on va direct aux parties (pas besoin de s'inscrire deux fois !)
        if ($this->getUser()) {
            return $this->redirectToRoute('partie_index');
        }

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            // Validations simples avant de créer le compte
            $errors = [];

            if (empty($username) || strlen($username) < 3) {
                $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
            }

            if (empty($password) || strlen($password) < 6) {
                $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
            }

            if ($password !== $passwordConfirm) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }

            // Vérifier si quelqu'un d'autre utilise déjà ce nom
            $existingUser = $em->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($existingUser) {
                $errors[] = "Ce nom d'utilisateur est déjà pris.";
            }

            if (empty($errors)) {
                // Tout est OK, on crée le compte
                $user = new User();
                $user->setUsername($username);
                
                // Hasher le mot de passe (on ne stocke JAMAIS les mots de passe en clair ! On est pas des hommes des cavernes)
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/register.html.twig', [
                'errors' => $errors,
                'username' => $username,
            ]);
        }

        return $this->render('security/register.html.twig', [
            'errors' => [],
        ]);
    }
}
