<?php

namespace App\Repository;

use App\Entity\Animal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Animal>
 *
 * @method Animal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Animal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Animal[]    findAll()
 * @method Animal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnimalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animal::class);
    }

    /**
     * Récupère les animaux filtrés par recherche (espèce ou état de santé) et triés.
     * * @param string|null $search    Le terme de recherche
     * @param string      $sort      La colonne de tri
     * @param string      $direction La direction (ASC/DESC)
     * @return Animal[]
     */
    public function findBySearchAndSort(?string $search, string $sort, string $direction): array
    {
        $qb = $this->createQueryBuilder('a');

        // 1. Filtrage par recherche (sur espèce ou état de santé)
        if ($search) {
            $qb->andWhere('a.espece LIKE :val OR a.etat_sante LIKE :val')
               ->setParameter('val', '%' . $search . '%');
        }

        // 2. Sécurisation des colonnes de tri (Whitelist)
        // On utilise 'espece' car 'type' n'existe pas dans votre entité
        $allowedSorts = ['espece', 'etat_sante', 'dateNaissance'];
        
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'espece'; 
        }

        // 3. Sécurisation de la direction
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        // 4. Application du tri dynamique
        $qb->orderBy('a.' . $sort, $direction);

        return $qb->getQuery()->getResult();
    }

    /**
     * Exemple : Compter le nombre d'animaux par état de santé
     */
    public function countByHealthStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('count(a.id_animal)') // Assurez-vous que c'est id_animal ou id selon votre entité
            ->andWhere('a.etat_sante = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countBySpecies(): array
{
    $results = $this->createQueryBuilder('a')
        ->select('a.species, COUNT(a.id) as count')
        ->groupBy('a.species')
        ->getQuery()
        ->getResult();

    $data = [];
    foreach ($results as $res) {
        $data[$res['species']] = $res['count'];
    }
    return $data;
}
}