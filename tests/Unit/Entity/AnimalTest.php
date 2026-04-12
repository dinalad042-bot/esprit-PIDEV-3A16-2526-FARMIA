<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Animal;
use App\Entity\Ferme;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Animal entity.
 *
 * @covers \App\Entity\Animal
 */
class AnimalTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $animal = new Animal();

        $animal->setEspece('Vache');
        $this->assertEquals('Vache', $animal->getEspece());

        $animal->setEtatSante('Bonne santé');
        $this->assertEquals('Bonne santé', $animal->getEtatSante());

        $date = new \DateTime('2020-01-15');
        $animal->setDateNaissance($date);
        $this->assertSame($date, $animal->getDateNaissance());
    }

    public function testIdAnimalGetter(): void
    {
        $animal = new Animal();
        $this->assertNull($animal->getIdAnimal());
    }

    public function testFermeRelationship(): void
    {
        $animal = new Animal();
        $ferme = new Ferme();

        $animal->setFerme($ferme);
        $this->assertSame($ferme, $animal->getFerme());

        $animal->setFerme(null);
        $this->assertNull($animal->getFerme());
    }

    public function testDateNaissanceAcceptsDateTime(): void
    {
        $animal = new Animal();

        $dateTime = new \DateTime('2020-06-15');
        $animal->setDateNaissance($dateTime);
        $this->assertInstanceOf(\DateTimeInterface::class, $animal->getDateNaissance());

        $dateImmutable = new \DateTimeImmutable('2019-03-20');
        $animal->setDateNaissance($dateImmutable);
        $this->assertInstanceOf(\DateTimeInterface::class, $animal->getDateNaissance());
    }
}
