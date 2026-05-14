<?php

namespace App\Repository;

use App\Entity\AnimalSante;
use App\Entity\Animal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnimalSante>
 *
 * @method AnimalSante|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnimalSante|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnimalSante[]    findAll()
 * @method AnimalSante[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnimalSanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnimalSante::class);
    }

    /**
     * Sauvegarde une entité de santé
     */
    public function save(AnimalSante $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une entité de santé
     */
    public function remove(AnimalSante $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupère tout l'historique médical d'un animal (vaccins, etc.) trié par date
     * @return AnimalSante[]
     */
    public function findByAnimalOrdered(Animal $animal): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.animal = :val')
            ->setParameter('val', $animal)
            ->orderBy('a.dateEvenement', 'DESC')
            ->getQuery()
            ->getResult();
    }
}