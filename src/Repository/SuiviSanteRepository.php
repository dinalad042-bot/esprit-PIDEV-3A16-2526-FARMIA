<?php

namespace App\Repository;

use App\Entity\SuiviSante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SuiviSante>
 *
 * @method SuiviSante|null find($id, $lockMode = null, $lockVersion = null)
 * @method SuiviSante|null findOneBy(array $criteria, array $orderBy = null)
 * @method SuiviSante[]    findAll()
 * @method SuiviSante[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuiviSanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuiviSante::class);
    }

    /**
     * Sauvegarde ou met à jour un suivi (Utile pour rester propre)
     */
    public function save(SuiviSante $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime un suivi
     */
    public function remove(SuiviSante $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupère l'historique trié par date décroissante (le plus récent en premier)
     * C'est ce qu'on utilise pour la timeline.
     * * @return SuiviSante[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.dateConsultation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les suivis spécifiques à un animal
     * * @return SuiviSante[]
     */
    public function findByAnimal(int $animalId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.animal = :val')
            ->setParameter('val', $animalId)
            ->orderBy('s.dateConsultation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}