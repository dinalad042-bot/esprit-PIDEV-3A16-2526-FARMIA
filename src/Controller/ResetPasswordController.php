<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function request(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_login'); // Ou dashboard
        }

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Generate a 6-digit code
                $resetCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $expiresAt = new \DateTime('+15 minutes');

                $user->setResetCode($resetCode);
                $user->setResetCodeExpiresAt($expiresAt);
                $entityManager->flush();

                // Send Email
                $emailMessage = (new TemplatedEmail())
                    ->from(new Address('aymen.bensalem2002@gmail.com', 'FarmIA Security'))
                    ->to($user->getEmail())
                    ->subject('Votre code de réinitialisation de mot de passe')
                    ->htmlTemplate('emails/reset_password.html.twig')
                    ->context([
                        'resetCode' => $resetCode,
                        'user' => $user,
                    ]);

                $mailer->send($emailMessage);
            }

            // Always add a flash message even if the user doesn't exist to prevent email enumeration
            $this->addFlash('success', 'Si un compte existe avec cet email, un code à 6 chiffres a été envoyé.');
            
            return $this->redirectToRoute('app_reset_password_verify', ['email' => $email]);
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/verify', name: 'app_reset_password_verify')]
    public function verify(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $email = $request->query->get('email', $request->request->get('email'));

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->render('security/reset_password.html.twig', ['email' => $email]);
            }

            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Utilisateur introuvable.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // Check code and expiration
            if ($user->getResetCode() !== $code) {
                $this->addFlash('error', 'Le code de vérification est incorrect.');
                return $this->render('security/reset_password.html.twig', ['email' => $email]);
            }

            if ($user->getResetCodeExpiresAt() < new \DateTime()) {
                $this->addFlash('error', 'Le code de vérification a expiré. Veuillez refaire une demande.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // Everything is good, update password
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            // Clear reset code
            $user->setResetCode(null);
            $user->setResetCodeExpiresAt(null);

            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'email' => $email,
        ]);
    }
}
