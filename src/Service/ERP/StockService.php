<?php

namespace App\Service\ERP;

use App\Repository\ERP\ServiceERPRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class StockService
{
    public function __construct(
        private ServiceERPRepository $serviceRepository,
        private ERPEmailService $emailService,
        private Security $security,
        private LoggerInterface $logger
    ) {}

    public function increaseStock(int $idService, int $quantity): void
    {
        if ($quantity <= 0) return;
        $this->serviceRepository->increaseStock($idService, $quantity);
    }

    public function decreaseStock(int $idService, int $quantity): void
    {
        if ($quantity <= 0) return;

        $affected = $this->serviceRepository->decreaseStockGuarded($idService, $quantity);
        if ($affected === 0) {
            throw new \RuntimeException("Stock insuffisant pour le service {$idService}");
        }

        $this->checkAndSendAlert($idService);
    }

    public function getStockById(int $idService): int
    {
        return $this->serviceRepository->getStockById($idService);
    }

    public function findStockCritique(): array
    {
        return $this->serviceRepository->findStockCritique();
    }

    private function checkAndSendAlert(int $idService): void
    {
        $service = $this->serviceRepository->find($idService);
        if (!$service) return;

        $stock  = $service->getStock();
        $seuil  = $service->getSeuilCritique();
        $nom    = $service->getNom();

        $recipientEmail = $this->getRecipientEmail();
        if (!$recipientEmail) return;

        $recipientName = $this->getRecipientName();

        try {
            if ($stock <= 0) {
                $this->emailService->sendStockZeroAlert($nom, $idService, $recipientEmail, $recipientName);
                $this->logger->info('[ERP] Zero-stock email sent for service', ['service' => $nom]);
            } elseif ($seuil > 0 && $stock <= $seuil) {
                $this->emailService->sendStockCritiqueAlert($nom, $idService, $stock, $seuil, $recipientEmail, $recipientName);
                $this->logger->info('[ERP] Critical-stock email sent for service', ['service' => $nom, 'stock' => $stock, 'seuil' => $seuil]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('[ERP] Stock alert email failed for service', ['service' => $nom, 'error' => $e->getMessage()]);
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
