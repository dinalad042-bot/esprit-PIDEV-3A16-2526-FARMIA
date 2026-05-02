<?php

namespace App\Repository\ERP;

use App\Entity\ERP\Achat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AchatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Achat::class);
    }

    public function findAllWithLignes(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.lignes', 'l')->addSelect('l')
            ->leftJoin('l.matiere', 'm')->addSelect('m')
            ->orderBy('a.dateAchat', 'DESC')
            ->getQuery()->getResult();
    }

    public function findByIdWithLignes(int $id): ?Achat
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.lignes', 'l')->addSelect('l')
            ->leftJoin('l.matiere', 'm')->addSelect('m')
            ->where('a.idAchat = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }
}
