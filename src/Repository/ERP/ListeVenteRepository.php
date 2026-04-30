<?php

namespace App\Repository\ERP;

use App\Entity\ERP\ListeVente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ListeVenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeVente::class);
    }

    public function findByVente(int $idVente): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.vente = :id')
            ->setParameter('id', $idVente)
            ->getQuery()
            ->getResult();
    }
}
