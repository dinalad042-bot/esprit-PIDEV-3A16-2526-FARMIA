<?php

namespace App\Repository\ERP;

use App\Entity\ERP\Vente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vente::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.lignes', 'l')->addSelect('l')
            ->leftJoin('l.produit', 'p')->addSelect('p')
            ->orderBy('v.idVente', 'DESC')
            ->getQuery()->getResult();
    }

    public function findByIdWithLignes(int $id): ?Vente
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.lignes', 'l')->addSelect('l')
            ->leftJoin('l.produit', 'p')->addSelect('p')
            ->where('v.idVente = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }
}
