<?php

namespace App\Service\ERP;

use App\Repository\ERP\MatiereRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MatiereService
{
    public function __construct(
        private MatiereRepository $repo,
        private ERPEmailService $emailService,
        private Security $security,
        private LoggerInterface $logger
    ) {}

    public function increaseStock(int $id, float $qty): void
    {
        if ($qty <= 0) return;
        $this->repo->increaseStock($id, $qty);
    }

    public function decreaseStock(int $id, float $qty): void
    {
        if ($qty <= 0) return;

        $affected = $this->repo->decreaseStockGuarded($id, $qty);
        if ($affected === 0) {
            throw new \RuntimeException("Stock insuffisant pour la matière {$id}");
        }

        $this->checkAndSendAlert($id);
    }

    public function getStockById(int $id): float
    {
        return $this->repo->getStockById($id);
    }

    public function findStockCritique(): array
    {
        return $this->repo->findStockCritique();
    }

    private function checkAndSendAlert(int $id): void
    {
        $matiere = $this->repo->find($id);
        if (!$matiere) return;

        $stock = $matiere->getStock();
        $seuil = $matiere->getSeuilCritique();
        $nom   = $matiere->getNom();

        $recipientEmail = $this->getRecipientEmail();
        if (!$recipientEmail) return;

        $recipientName = $this->getRecipientName();

        try {
            if ($stock <= 0) {
                $this->emailService->sendStockZeroAlert($nom, $id, $recipientEmail, $recipientName);
                $this->logger->info('[ERP] Zero-stock email sent for matiere', ['matiere' => $nom]);
            } elseif ($seuil > 0 && $stock <= $seuil) {
                $this->emailService->sendStockCritiqueAlert($nom, $id, (int) $stock, (int) $seuil, $recipientEmail, $recipientName);
                $this->logger->info('[ERP] Critical-stock email sent for matiere', ['matiere' => $nom, 'stock' => $stock, 'seuil' => $seuil]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('[ERP] Stock alert email failed for matiere', ['matiere' => $nom, 'error' => $e->getMessage()]);
        }
    }

    private function getRecipientEmail(): ?string
    {
        $user = $this->security->getUser();
        return ($user && method_exists($user, 'getEmail')) ? $user->getEmail() : null;
    }

    private function getRecipientName(): string
    {
        $user = $this->security->getUser();
        if (!$user) return '';
        $parts = [];
        if (method_exists($user, 'getPrenom') && $user->getPrenom()) $parts[] = $user->getPrenom();
        if (method_exists($user, 'getNom') && $user->getNom()) $parts[] = $user->getNom();
        return implode(' ', $parts);
    }
}
