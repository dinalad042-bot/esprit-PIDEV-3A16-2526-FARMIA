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
            'error'         => $error,
        ]);
    }

    #[Route('/signup', name: 'app_signup', methods: ['GET', 'POST'])]
    public function signup(Request $request, AuthService $authService, UserLogService $userLogService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard_default');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $passwordConfirm = $request->request->get('password_confirm');

            if ($password !== $passwordConfirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $data = [
                    'nom'       => $request->request->get('nom'),
                    'prenom'    => $request->request->get('prenom'),
                    'email'     => $request->request->get('email'),
                    'password'  => $password,
                    'telephone' => $request->request->get('telephone'),
                    'adresse'   => $request->request->get('adresse'),
                    'role'      => $request->request->get('role', 'ROLE_USER'),
                ];

                try {
                    $user = $authService->signup($data);
                    $userLogService->log($user, 'SIGNUP_WEB', 'SUCCESS');
                    
                    $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                    return $this->redirectToRoute('app_login');
                } catch (\InvalidArgumentException $e) {
                    // Erreur de validation métier (email pris, mot de passe vide...)
                    $error = $e->getMessage();
                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    // Violation de contrainte unique SQL (ex: CIN duplication) non détectée en amont
                    $error = "Une contrainte unique SQL a été violée (ex: Email ou CIN déjà existant). Détail : " . $e->getMessage();
                } catch (\Doctrine\DBAL\Exception\NotNullConstraintViolationException $e) {
                    // Champ 'not null' manquant en base
                    $error = "Un champ obligatoire manque pour la base de données. Détail : " . $e->getMessage();
                } catch (\Doctrine\DBAL\Exception $e) {
                    // Toute autre erreur SQL (colonne non trouvée, mauvaise table, etc.)
                    $error = "Erreur base de données (SQL Doctrine) : " . $e->getMessage();
                } catch (\Exception $e) {
                    // Erreur générale non SQL
                    $error = "Erreur système critique : " . $e->getMessage();
                }
            }
        }

        return $this->render('security/signup.html.twig', [
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
