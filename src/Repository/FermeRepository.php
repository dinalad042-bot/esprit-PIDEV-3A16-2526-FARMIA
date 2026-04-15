<?php

namespace App\Repository;

use App\Entity\Ferme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FermeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ferme::class);
    }

    /**
     * Find farms with search and sort functionality
     */
    public function findBySearchAndSort(string $search, string $sort, string $direction): array
    {
        $qb = $this->createQueryBuilder('f');

        // Search filter
        if (!empty($search)) {
            $qb->andWhere('f.nomFerme LIKE :search OR f.lieu LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Map sort fields to actual entity property names
        $sortFieldMap = [
            'idFerme' => 'id',
            'nomFerme' => 'nomFerme',
            'lieu' => 'lieu',
            'superficie' => 'surface',
        ];

        // Sort validation - only allow valid fields
        if (!isset($sortFieldMap[$sort])) {
            $sort = 'idFerme';
        }

        // Direction validation
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $qb->orderBy('f.' . $sortFieldMap[$sort], $direction);

        return $qb->getQuery()->getResult();
    }
}
