<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserFace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserFace>
 */
class UserFaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFace::class);
    }

    /**
     * Retourne le visage actif d'un utilisateur, ou null s'il n'en a pas.
     */
    public function findActiveByUser(User $user): ?UserFace
    {
        return $this->createQueryBuilder('uf')
            ->andWhere('uf.user = :user')
            ->andWhere('uf.isActive = true')
            ->setParameter('user', $user)
            ->orderBy('uf.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne tous les enregistrements de visages actifs (pour re-entraîner le modèle).
     *
     * @return UserFace[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('uf')
            ->andWhere('uf.isActive = true')
            ->join('uf.user', 'u')
            ->addSelect('u')
            ->orderBy('uf.enrolledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un utilisateur a déjà un visage enregistré (actif ou non).
     */
    public function userHasFace(User $user): bool
    {
        $count = $this->createQueryBuilder('uf')
            ->select('COUNT(uf.id)')
            ->andWhere('uf.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    /**
     * Retourne tous les enregistrements de visages pour un utilisateur (historique).
     *
     * @return UserFace[]
     */
    public function findAllByUser(User $user): array
    {
        return $this->createQueryBuilder('uf')
            ->andWhere('uf.user = :user')
            ->setParameter('user', $user)
            ->orderBy('uf.enrolledAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
