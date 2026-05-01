<?php

namespace App\Repository;

use App\Entity\UserLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserLog>
 */
class UserLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLog::class);
    }

    /**
     * Retrieve all logs for a given user, ordered from newest to oldest.
     *
     * @return UserLog[]
     */
    public function findByUserId(int $userId): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('l.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
