<?php

namespace App\Repository\ERP;

use App\Entity\ERP\Matiere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Matiere::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.nom', 'ASC')
            ->getQuery()->getResult();
    }

    public function findStockCritique(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.seuilCritique > 0')
            ->andWhere('m.stock <= m.seuilCritique')
            ->orderBy('m.stock', 'ASC')
            ->getQuery()->getResult();
    }

    public function getStockById(int $id): float
    {
        $r = $this->getEntityManager()->getConnection()
            ->fetchOne('SELECT stock FROM erp_matiere WHERE id_matiere = :id', ['id' => $id]);
        return $r !== false ? (float) $r : 0.0;
    }

    public function increaseStock(int $id, float $qty): void
    {
        $this->getEntityManager()->getConnection()->executeStatement(
            'UPDATE erp_matiere SET stock = stock + :qty WHERE id_matiere = :id',
            ['qty' => $qty, 'id' => $id]
        );
    }

    public function decreaseStockGuarded(int $id, float $qty): int
    {
        return (int) $this->getEntityManager()->getConnection()->executeStatement(
            'UPDATE erp_matiere SET stock = stock - :qty WHERE id_matiere = :id AND stock >= :qty',
            ['qty' => $qty, 'id' => $id]
        );
    }
}
