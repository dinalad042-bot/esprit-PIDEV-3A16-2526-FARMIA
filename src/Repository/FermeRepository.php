<?php

namespace App\Repository;

use App\Entity\Ferme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ferme>
 *
 * @method Ferme|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ferme|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ferme[]    findAll()
 * @method Ferme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FermeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ferme::class);
    }

    /**
     * Récupère les fermes filtrées par un terme de recherche et triées dynamiquement.
     * * @param string|null $search    Le terme à chercher (nom ou lieu)
     * @param string      $sort      La colonne de tri (nom_ferme, lieu, surface)
     * @param string      $direction La direction du tri (ASC ou DESC)
     * @return Ferme[]
     */
    public function findBySearchAndSort(?string $search, string $sort, string $direction): array
    {
        $qb = $this->createQueryBuilder('f');

        // 1. Logique de filtrage (Recherche)
        if ($search) {
            $qb->andWhere('f.nom_ferme LIKE :val OR f.lieu LIKE :val')
               ->setParameter('val', '%' . $search . '%');
        }

        // 2. Sécurisation du tri (White-listing)
        // On vérifie que la colonne demandée existe bien dans l'entité
        $allowedSorts = ['nom_ferme', 'lieu', 'surface'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'nom_ferme'; // Valeur par défaut si le paramètre est invalide
        }

        // 3. Sécurisation de la direction
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        // 4. Application du tri
        $qb->orderBy('f.' . $sort, $direction);

        return $qb->getQuery()->getResult();
    }

    /**
     * Exemple : Ajouter une méthode personnalisée si nécessaire 
     * pour compter le nombre total d'hectares par exemple.
     */
    public function getTotalSurface(): float
    {
        return (float) $this->createQueryBuilder('f')
            ->select('SUM(f.surface)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}