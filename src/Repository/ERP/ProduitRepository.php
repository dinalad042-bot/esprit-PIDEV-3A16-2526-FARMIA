<?php

namespace App\Repository\ERP;

use App\Entity\ERP\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function findAllWithRecette(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.recette', 'r')
            ->addSelect('r')
            ->leftJoin('r.matiere', 'm')
            ->addSelect('m')
            ->orderBy('p.nom', 'ASC')
            ->getQuery()->getResult();
    }
}
