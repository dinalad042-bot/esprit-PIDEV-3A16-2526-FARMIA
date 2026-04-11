<?php

namespace App\Controller\Web;

use App\Service\AuthService;
use App\Service\UserLogService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
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
    public function signup(Request $request, MailerInterface $mailer): Response
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
            $email = $user->getEmail();

            // Génération d'un code unique à 6 chiffres
            $verificationCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Stockage temporaire en Session (pour ne pas créer de compte inutile)
            $signupData = [
                'nom'       => $user->getNom(),
                'prenom'    => $user->getPrenom(),
                'email'     => $email,
                'password'  => $password,
                'telephone' => $user->getTelephone(),
                'cin'       => $user->getCin(),
                'adresse'   => $user->getAdresse(),
                'latitude'  => $user->getLatitude(),
                'longitude' => $user->getLongitude(),
                'role'      => $user->getRole(),
                'verification_code' => $verificationCode,
                'expires_at' => (new \DateTime('+15 minutes'))->format('Y-m-d H:i:s'),
            ];

            $request->getSession()->set('pending_signup_data', $signupData);

            try {
                // Envoi de l'email de vérification
                $emailMessage = (new TemplatedEmail())
                    ->from(new Address('aymen.bensalem2002@gmail.com', 'FarmIA Security'))
                    ->to($email)
                    ->subject('Vérifiez votre adresse email - Inscription FarmIA')
                    ->htmlTemplate('emails/signup_verify.html.twig')
                    ->context([
                        'verificationCode' => $verificationCode,
                        'user' => $user,
                    ]);

                $mailer->send($emailMessage);

                $this->addFlash('success', 'Un code de vérification a été envoyé à votre adresse email.');
                return $this->redirectToRoute('app_signup_verify');

            } catch (\Exception $e) {
                $error = "Erreur lors de l'envoi de l'email : " . $e->getMessage();
            }
        }

        return $this->render('security/signup.html.twig', [
            'form'  => $form->createView(),
            'error' => $error,
        ]);
    }

    #[Route('/signup/verify', name: 'app_signup_verify', methods: ['GET', 'POST'])]
    public function signupVerify(Request $request, AuthService $authService, UserLogService $userLogService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard_default');
        }

        $session = $request->getSession();
        $signupData = $session->get('pending_signup_data');

        // Si pas de données en session, c'est qu'il n'y a pas d'inscription en attente
        if (!$signupData) {
            $this->addFlash('error', 'Aucune inscription en attente ou le délai est dépassé. Veuillez réessayer.');
            return $this->redirectToRoute('app_signup');
        }

        $email = $signupData['email'];
        $error = null;

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');

            if ($code === $signupData['verification_code']) {
                $expiresAt = new \DateTime($signupData['expires_at']);
                
                if (new \DateTime() > $expiresAt) {
                    $error = "Le code de vérification a expiré. Veuillez refaire l'inscription.";
                    $session->remove('pending_signup_data');
                } else {
                    // C'est valide ! On lance la vraie création du compte.
                    try {
                        $createdUser = $authService->signup($signupData);
                        $userLogService->log($createdUser, 'SIGNUP_WEB', 'SUCCESS');
                        
                        // Nettoyage session
                        $session->remove('pending_signup_data');

                        $this->addFlash('success', 'Adresse email vérifiée. Inscription réussie ! Vous pouvez maintenant vous connecter.');
                        return $this->redirectToRoute('app_login');

                    } catch (\InvalidArgumentException $e) {
                        $error = $e->getMessage();
                    } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                        $error = "Une contrainte d'unicité n'est pas respectée (ex: Email ou CIN déjà utilisé).";
                    } catch (\Exception $e) {
                        $error = "Erreur système lors de la création : " . $e->getMessage();
                    }
                }
            } else {
                $error = "Le code de vérification est incorrect.";
            }
        }

        return $this->render('security/signup_verify.html.twig', [
            'email' => $email,
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
