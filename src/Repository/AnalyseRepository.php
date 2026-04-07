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
}