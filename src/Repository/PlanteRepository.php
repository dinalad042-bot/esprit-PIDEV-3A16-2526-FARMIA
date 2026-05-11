<?php

namespace App\Repository;

use App\Entity\Plante;
use App\Entity\Ferme;
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
     * Filtré par les fermes de l'utilisateur connecté
     * @param string|null $search Le terme de recherche
     * @param string $sort La colonne de tri
     * @param string $direction La direction (ASC/DESC)
     * @param int[] $userFermeIds IDs des fermes de l'utilisateur
     */
    public function findBySearchAndSort(?string $search, string $sort, string $direction, array $userFermeIds = []): array
    {
        $qb = $this->createQueryBuilder('p');

        // Filter by user's farms only
        if (!empty($userFermeIds)) {
            $qb->andWhere('p.ferme IN (:fermeIds)')
               ->setParameter('fermeIds', $userFermeIds);
        }

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
            ->andWhere('p.ferme = :ferme')
            ->setParameter('ferme', $fermeId)
            ->getQuery()
            ->getResult();
    }

    public function findByFermeEntity(Ferme $ferme): array
    {
        return $this->findBy(['ferme' => $ferme]);
    }
}