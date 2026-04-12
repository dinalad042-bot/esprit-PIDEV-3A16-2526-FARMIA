<?php

namespace App\Repository;

use App\Entity\Plante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plante>
 */
class PlanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plante::class);
    }

    /**
     * Recherche et tri des plantes avec les noms de champs exacts
     */
    public function findBySearchAndSort(?string $search, string $sort, string $direction): array
    {
        $qb = $this->createQueryBuilder('p');

        // 1. Filtrage
        if ($search) {
            $qb->andWhere('p.nom_espece LIKE :val OR p.cycle_vie LIKE :val')
               ->setParameter('val', '%' . $search . '%');
        }

        // 2. Whitelist de tri (noms des propriétés dans l'entité)
        $allowedSorts = ['nom_espece', 'cycle_vie', 'quantite'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'nom_espece'; 
        }

        // 3. Direction
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $qb->orderBy('p.' . $sort, $direction);

        return $qb->getQuery()->getResult();
    }

    public function findByFerme(int $fermeId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.ferme', 'f')
            ->andWhere('f.id = :fermeId')
            ->setParameter('fermeId', $fermeId)
            ->orderBy('p.nom_espece', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
