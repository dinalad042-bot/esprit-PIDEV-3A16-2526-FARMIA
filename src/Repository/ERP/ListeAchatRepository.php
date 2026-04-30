<?php

namespace App\Repository\ERP;

use App\Entity\ERP\ListeAchat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ListeAchatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeAchat::class);
    }

    public function findByAchat(int $idAchat): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.achat = :id')
            ->setParameter('id', $idAchat)
            ->getQuery()
            ->getResult();
    }
}
