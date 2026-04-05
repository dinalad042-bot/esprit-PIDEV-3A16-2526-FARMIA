<?php

namespace App\Controller\Web;

use App\Service\AuthService;
use App\Service\UserLogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, redirection vers dashboard par défaut
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard_default');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/signup', name: 'app_signup', methods: ['GET', 'POST'])]
    public function signup(Request $request, AuthService $authService, UserLogService $userLogService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard_default');
        }

        $user = new \App\Entity\User();
        $form = $this->createForm(\App\Form\RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();

            try {
                $data = [
                    'nom'       => $user->getNom(),
                    'prenom'    => $user->getPrenom(),
                    'email'     => $user->getEmail(),
                    'password'  => $password,
                    'telephone' => $user->getTelephone(),
                    'cin'       => $user->getCin(),
                    'adresse'   => $user->getAdresse(),
                    'role'      => $user->getRole(),
                ];

                $createdUser = $authService->signup($data);
                $userLogService->log($createdUser, 'SIGNUP_WEB', 'SUCCESS');
                
                $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            } catch (\InvalidArgumentException $e) {
                // Erreur de validation métier (email pris, mot de passe vide...)
                $error = $e->getMessage();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                // Violation de contrainte unique SQL (ex: CIN duplication) non détectée en amont
                $error = "Une contrainte unique SQL a été violée. Détail : " . $e->getMessage();
            } catch (\Exception $e) {
                $error = "Erreur système : " . $e->getMessage();
            }
        }

        return $this->render('security/signup.html.twig', [
            'form'  => $form->createView(),
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Intercepté par Symfony Security
    }

    #[Route('/access-denied', name: 'app_access_denied')]
    public function accessDenied(): Response
    {
        $this->addFlash('error', "Accès refusé. Vous n'avez pas les permissions nécessaires pour voir cette page.");
        return $this->redirectToRoute('dashboard_default');
    }
}
