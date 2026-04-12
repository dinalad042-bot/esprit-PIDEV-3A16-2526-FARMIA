<?php

namespace App\Repository;

use App\Entity\Conseil;
use App\Enum\Priorite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConseilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conseil::class);
    }

    public function findByAnalyseId(int $id): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.analyse = :id')
            ->setParameter('id', $id)
            ->getQuery()->getResult();
    }

    public function findByPriorite(string $priorite): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.prioriteRaw = :p')
            ->setParameter('p', $priorite)
            ->getQuery()->getResult();
    }

    public function search(string $term, ?string $priorite = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.descriptionConseil LIKE :term')
            ->setParameter('term', '%'.$term.'%');
        if ($priorite) {
            $qb->andWhere('c.prioriteRaw = :p')->setParameter('p', $priorite);
        }
        return $qb->getQuery()->getResult();
    }

    public function getPriorityStats(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.prioriteRaw AS priorite, COUNT(c.id) AS total')
            ->groupBy('c.prioriteRaw')
            ->getQuery()->getResult();
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()->getSingleScalarResult();
    }

    public function countByTechnicien(int $technicienId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.analyse', 'a')
            ->andWhere('a.technicien = :techId')
            ->setParameter('techId', $technicienId)
            ->getQuery()->getSingleScalarResult();
    }

    public function countByTechnicienAndPriorite(int $technicienId, string $priorite): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.analyse', 'a')
            ->andWhere('a.technicien = :techId')
            ->andWhere('c.prioriteRaw = :priorite')
            ->setParameter('techId', $technicienId)
            ->setParameter('priorite', $priorite)
            ->getQuery()->getSingleScalarResult();
    }

    public function findByExpert(int $technicienId, ?string $search = null, ?string $priorite = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.analyse', 'a')
            ->andWhere('a.technicien = :techId')
            ->setParameter('techId', $technicienId)
            ->orderBy('c.id', 'DESC');

        if ($search) {
            $qb->andWhere('c.descriptionConseil LIKE :term')
               ->setParameter('term', '%'.$search.'%');
        }

        if ($priorite) {
            $qb->andWhere('c.prioriteRaw = :p')
               ->setParameter('p', $priorite);
        }

        return $qb->getQuery()->getResult();
    }
}
