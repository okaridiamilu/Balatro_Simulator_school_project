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
    /**
     * Page d'accueil (accessible à tous, connecté ou non)
     */
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('security/home.html.twig');
    }

    /**
     * Page de connexion
     */
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger vers les parties
        if ($this->getUser()) {
            return $this->redirectToRoute('partie_index');
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier username saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Déconnexion (géré automatiquement par Symfony)
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide, elle sera interceptée par le firewall
        throw new \LogicException('Cette méthode peut être vide car elle sera interceptée par la clé logout de votre firewall.');
    }

    /**
     * Page d'inscription
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $em
    ): Response {
        // Si l'utilisateur est déjà connecté, rediriger vers les parties
        if ($this->getUser()) {
            return $this->redirectToRoute('partie_index');
        }

        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            // Validation basique
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

            // Vérifier si l'utilisateur existe déjà
            $existingUser = $em->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($existingUser) {
                $errors[] = "Ce nom d'utilisateur est déjà pris.";
            }

            if (empty($errors)) {
                // Créer le nouvel utilisateur
                $user = new User();
                $user->setUsername($username);
                
                // Hasher le mot de passe
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
