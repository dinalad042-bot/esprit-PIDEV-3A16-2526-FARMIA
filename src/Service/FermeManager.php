<?php
namespace App\Service;

use App\Entity\Ferme;

class FermeManager
{
    public function validate(Ferme $ferme): bool
    {
        // 1. On vérifie le nom
        if (empty($ferme->getNomFerme())) {
            throw new \InvalidArgumentException('Nom manquant');
        }

        // 2. On vérifie la surface
        if ($ferme->getSurface() === null || $ferme->getSurface() <= 0) {
            throw new \InvalidArgumentException('Surface invalide');
        }

        return true;
    }
}