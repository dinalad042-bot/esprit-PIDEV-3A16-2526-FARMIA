<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @return User[]
     */
    public function findAllUsers(): array
    {
        return $this->findAll();
    }

    /**
     * Search users by name, email, or cin, and filter by role.
     *
     * @return User[]
     */
    public function searchAndFilter(?string $query = null, ?string $role = null): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($query) {
            $qb->andWhere('u.nom LIKE :q OR u.prenom LIKE :q OR u.email LIKE :q OR u.cin LIKE :q')
               ->setParameter('q', '%' . $query . '%');
        }

        if ($role && $role !== 'ALL') {
            $qb->andWhere('u.role = :role')
               ->setParameter('role', $role);
        }

        return $qb->orderBy('u.id', 'DESC')->getQuery()->getResult();
    }

    /**
     * Get user distribution by role for statistics.
     *
     * @return array<int, array<string, mixed>>
     */
    public function countUsersByRole(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.role, COUNT(u.id) as count')
            ->groupBy('u.role')
            ->getQuery()
            ->getResult();
    }

    public function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.email = :email')
            ->setParameter('email', strtolower(trim($email)));

        if ($excludeId !== null) {
            $qb->andWhere('u.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function existsByCin(string $cin, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.cin = :cin')
            ->setParameter('cin', str_replace(' ', '', trim($cin)));

        if ($excludeId !== null) {
            $qb->andWhere('u.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function existsByTelephone(string $telephone, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.telephone = :telephone')
            ->setParameter('telephone', str_replace(' ', '', trim($telephone)));

        if ($excludeId !== null) {
            $qb->andWhere('u.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}