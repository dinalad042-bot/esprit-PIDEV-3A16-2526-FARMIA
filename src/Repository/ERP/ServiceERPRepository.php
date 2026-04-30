<?php

namespace App\Repository\ERP;

use App\Entity\ERP\ServiceERP;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceERPRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceERP::class);
    }

    public function findAllOrderedById(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.idService', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findStockCritique(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.seuilCritique > 0')
            ->andWhere('s.stock <= s.seuilCritique')
            ->orderBy('s.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getStockById(int $idService): int
    {
        $result = $this->getEntityManager()
            ->getConnection()
            ->fetchOne('SELECT stock FROM erp_service WHERE id_service = :id', ['id' => $idService]);
        return $result !== false ? (int) $result : 0;
    }

    public function increaseStock(int $idService, int $quantity): void
    {
        $this->getEntityManager()
            ->getConnection()
            ->executeStatement(
                'UPDATE erp_service SET stock = stock + :qty WHERE id_service = :id',
                ['qty' => $quantity, 'id' => $idService]
            );
    }

    public function decreaseStockGuarded(int $idService, int $quantity): int
    {
        return (int) $this->getEntityManager()
            ->getConnection()
            ->executeStatement(
                'UPDATE erp_service SET stock = stock - :qty WHERE id_service = :id AND stock >= :qty',
                ['qty' => $quantity, 'id' => $idService]
            );
    }
}
