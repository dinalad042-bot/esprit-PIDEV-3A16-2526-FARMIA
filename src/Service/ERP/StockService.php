<?php

namespace App\Service\ERP;

use App\Repository\ERP\ServiceERPRepository;

class StockService
{
    public function __construct(private ServiceERPRepository $serviceRepository) {}

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
    }

    public function getStockById(int $idService): int
    {
        return $this->serviceRepository->getStockById($idService);
    }

    public function findStockCritique(): array
    {
        return $this->serviceRepository->findStockCritique();
    }
}
