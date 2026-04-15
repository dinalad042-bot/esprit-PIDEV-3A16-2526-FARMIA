<?php

namespace App\Repository;

use App\Entity\Analyse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AnalyseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Analyse::class);
    }

    public function findByTechnicienId(int $id): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.technicien = :id')
            ->setParameter('id', $id)
            ->orderBy('a.dateAnalyse', 'DESC')
            ->getQuery()->getResult();
    }

    public function findByFermeId(int $id): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.ferme = :id')
            ->setParameter('id', $id)
            ->orderBy('a.dateAnalyse', 'DESC')
            ->getQuery()->getResult();
    }

    public function search(string $term): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.resultatTechnique LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('a.dateAnalyse', 'DESC')
            ->getQuery()->getResult();
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()->getSingleScalarResult();
    }

    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.dateAnalyse', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    public function getAnalysisPerFarmStats(): array
    {
        return $this->createQueryBuilder('a')
            ->select('f.nomFerme AS ferme, COUNT(a.id) AS total')
            ->join('a.ferme', 'f')
            ->groupBy('f.id')
            ->orderBy('total', 'DESC')
            ->getQuery()->getResult();
    }

    public function countByTechnicienThisMonth(int $technicienId): int
    {
        $startOfMonth = new \DateTime('first day of this month');
        $endOfMonth = new \DateTime('last day of this month 23:59:59');

        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.technicien = :techId')
            ->andWhere('a.dateAnalyse >= :start')
            ->andWhere('a.dateAnalyse <= :end')
            ->setParameter('techId', $technicienId)
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->getQuery()->getSingleScalarResult();
    }

    public function countByTechnicien(int $technicienId): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.technicien = :techId')
            ->setParameter('techId', $technicienId)
            ->getQuery()->getSingleScalarResult();
    }

    public function searchByTechnicien(int $technicienId, string $term): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.technicien = :techId')
            ->andWhere('a.resultatTechnique LIKE :term')
            ->setParameter('techId', $technicienId)
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('a.dateAnalyse', 'DESC')
            ->getQuery()->getResult();
    }

    public function findPendingRequests(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->orderBy('a.dateAnalyse', 'DESC')
            ->getQuery()->getResult();
    }

    public function countPendingRequests(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.statut = :statut')
            ->setParameter('statut', 'en_attente')
            ->getQuery()->getSingleScalarResult();
    }

    public function findByDemandeur(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.demandeur = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.dateAnalyse', 'DESC')
            ->getQuery()->getResult();
    }

    public function save(Analyse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Analyse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
