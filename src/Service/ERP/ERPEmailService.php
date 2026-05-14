<?php

namespace App\Service\ERP;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ERPEmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromAddress = 'noreply@farmia-desk.local'
    ) {}

    /**
     * Sends a stock-zero alert to the given recipient email.
     */
    public function sendStockZeroAlert(string $serviceName, int $serviceId, string $recipientEmail, string $recipientName = ''): void
    {
        $greeting = $recipientName ? "Bonjour {$recipientName}," : 'Bonjour,';
        $subject = "🚨 Stock épuisé — {$serviceName}";
        $body = <<<TEXT
{$greeting}

Alerte stock : le stock d'un service vient d'atteindre 0.

Détails :
- Service : {$serviceName}
- ID : {$serviceId}
- Statut : Stock épuisé (0)

Action recommandée :
Merci de procéder au réapprovisionnement dès que possible afin d'éviter toute rupture de vente.

Cordialement,
FarmIADesk (système)
TEXT;
        $this->send($subject, $body, $recipientEmail);
    }

    /**
     * Sends a critical stock alert to the given recipient email.
     */
    public function sendStockCritiqueAlert(string $serviceName, int $serviceId, int $stock, int $seuil, string $recipientEmail, string $recipientName = ''): void
    {
        $greeting = $recipientName ? "Bonjour {$recipientName}," : 'Bonjour,';
        $subject = "⚠️ Stock critique — {$serviceName}";
        $body = <<<TEXT
{$greeting}

Alerte stock critique : le stock d'un service est passé sous le seuil critique.

Détails :
- Service : {$serviceName}
- ID : {$serviceId}
- Stock actuel : {$stock}
- Seuil critique : {$seuil}

Action recommandée :
Merci de procéder au réapprovisionnement dès que possible.

Cordialement,
FarmIADesk (système)
TEXT;
        $this->send($subject, $body, $recipientEmail);
    }

    private function send(string $subject, string $body, string $to): void
    {
        $email = (new Email())
            ->from($this->fromAddress)
            ->to($to)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }
}
