<?php

namespace App\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Animal;
use App\Entity\Ferme;

class AnimalTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $animal = new Animal();

        $espece = 'Vache';
        $animal->setEspece($espece);
        $this->assertEquals($espece, $animal->getEspece());

        $etatSante = 'Bon';
        $animal->setEtatSante($etatSante);
        $this->assertEquals($etatSante, $animal->getEtatSante());
    }

    public function testFermeRelationship(): void
    {
        $animal = new Animal();
        $ferme = new Ferme();
        // Attention : vérifie si tes méthodes sont setNom() ou setNomFerme() dans ton entité
        if (method_exists($ferme, 'setNom')) {
            $ferme->setNom('Ma Ferme');
        }

        $animal->setFerme($ferme);
        $this->assertSame($ferme, $animal->getFerme());
    }
}