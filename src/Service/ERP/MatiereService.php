<?php

namespace App\Service\ERP;

use App\Repository\ERP\MatiereRepository;

class MatiereService
{
    public function __construct(private MatiereRepository $repo) {}

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
    }

    public function getStockById(int $id): float
    {
        return $this->repo->getStockById($id);
    }

    public function findStockCritique(): array
    {
        return $this->repo->findStockCritique();
    }
}
