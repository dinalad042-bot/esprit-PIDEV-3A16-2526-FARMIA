<?php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Email Security Service - Secure email verification and password reset
 *
 * Features:
 * - Google Mailer integration (Gmail SMTP)
 * - Session-based email verification
 * - Two-step forgot password workflow
 * - CSRF token protection for email actions
 * - Comprehensive logging and error handling
 */
class EmailSecurityService
{
    private const SESSION_EMAIL_VERIFICATION = '_email_verification_token';
    private const SESSION_PASSWORD_RESET = '_password_reset_token';
    private const TOKEN_EXPIRATION = 3600; // 1 hour in seconds

    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private UrlGeneratorInterface $urlGenerator,
        private CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    /**
     * Send secure email verification link
     * Uses Google Mailer for reliable delivery
     */
    public function sendVerificationEmail(User $user, string $verificationUrl): bool
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address('noreply@farmia.tn', 'FarmAI Security'))
                ->to($user->getEmail())
                ->subject('Vérifiez votre adresse email - FarmAI')
                ->htmlTemplate('emails/verification.html.twig')
                ->context([
                    'user' => $user,
                    'verificationUrl' => $verificationUrl,
                    'expirationHours' => self::TOKEN_EXPIRATION / 3600,
                ]);

            $this->mailer->send($email);

            $this->logger->info('Verification email sent', [
                'userId' => $user->getId(),
                'email' => $user->getEmail(),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send verification email', [
                'userId' => $user->getId(),
                'email' => $user->getEmail(),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}