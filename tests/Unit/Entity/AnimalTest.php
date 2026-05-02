<?php

namespace App\Tests\Unit\Entity;

use PHPUnit\\Framework\\TestCase;
use App\\Entity\\Animal;
use App\\Entity\\Ferme;

/**
 * Unit tests for the Animal entity.
 *
 * @package App\Tests\\Unit\\Entity
 */
class AnimalTest extends TestCase
{
    /**
     * Test all getters and setters for the Animal entity.
     *
     * @return void
     */
    public function testGettersAndSetters(): void
    {
        $animal = new Animal();

        // Test espece getter and setter
        $espece = 'Vache';
        $animal->setEspece($espece);
        $this->assertEquals($espece, $animal->getEspece());

        // Test etatSante getter and setter
        $etatSante = 'Bon';
        $animal->setEtatSante($etatSante);
        $this->assertEquals($etatSante, $animal->getEtatSante());

        // Test dateNaissance getter and setter
        $dateNaissance = new \DateTime('2020-01-15');
        $animal->setDateNaissance($dateNaissance);
        $this->assertEquals($dateNaissance, $animal->getDateNaissance());

        // Test fluent interface (return $this)
        $this->assertSame($animal, $animal->setEspece('Mouton'));
        $this->assertSame($animal, $animal->setEtatSante('Moyen'));
        $this->assertSame($animal, $animal->setDateNaissance(new \DateTime()));
    }

    /**
     * Test the getIdAnimal method.
     *
     * @return void
     */
    public function testIdAnimalGetter(): void
    {
        $animal = new Animal();

        // id_animal should be null for a new entity
        $this->assertNull($animal->getIdAnimal());
    }

    /**
     * Test the Ferme relationship (setFerme/getFerme).
     *
     * @return void
     */
    public function testFermeRelationship(): void
    {
        $animal = new Animal();
        $ferme = new Ferme();
        $ferme->setNomFerme('Ma Ferme');
        $ferme->setLieu('Tunis');

        // Test setFerme and getFerme
        $this->assertNull($animal->getFerme());

        $result = $animal->setFerme($ferme);
        $this->assertSame($animal, $result);
        $this->assertSame($ferme, $animal->getFerme());

        // Test setting ferme to null
        $animal->setFerme(null);
        $this->assertNull($animal->getFerme());
    }

    /**
     * Test that dateNaissance accepts DateTime objects.
     *
     * @return void
     */
    public function testDateNaissanceAcceptsDateTime(): void
    {
        $animal = new Animal();

        // Test with DateTime object
        $dateTime = new \DateTime('2019-06-20');
        $animal->setDateNaissance($dateTime);
        $this->assertInstanceOf(\DateTimeInterface::class, $animal->getDateNaissance());
        $this->assertEquals('2019-06-20', $animal->getDateNaissance()->format('Y-m-d'));

        // Test with DateTimeImmutable object
        $dateTimeImmutable = new \DateTimeImmutable('2021-03-10');
        $animal->setDateNaissance($dateTimeImmutable);
        $this->assertInstanceOf(\DateTimeInterface::class, $animal->getDateNaissance());
        $this->assertEquals('2021-03-10', $animal->getDateNaissance()->format('Y-m-d'));

        // Test with current date
        $now = new \DateTime();
        $animal->setDateNaissance($now);
        $this->assertSame($now, $animal->getDateNaissance());
    }

    /**
     * Test validation constraints conceptually.
     * This test documents the validation constraints that exist on the entity.
     * Note: Actual validation testing requires the Validator component.
     *
     * @return void
     */
    public function testValidationConstraints(): void
    {
        $animal = new Animal();

        // Test that entity accepts null values (before validation)
        // In actual usage, validation constraints would trigger:
        // - NotBlank on espece (min length 3)
        // - NotBlank on etat_sante
        // - NotNull on date_naissance (must be <= today)
        // - NotNull on ferme

        $this->assertNull($animal->getEspece());
        $this->assertNull($animal->getEtatSante());
        $this->assertNull($animal->getDateNaissance());
        $this->assertNull($animal->getFerme());

        // Set valid values
        $animal->setEspece('Mouton');
        $animal->setEtatSante('Excellent');
        $animal->setDateNaissance(new \DateTime('2020-01-01'));
        $ferme = new Ferme();
        $ferme->setNomFerme('Test Ferme');
        $ferme->setLieu('Test Lieu');
        $animal->setFerme($ferme);

        // Verify values are set (conceptually valid)
        $this->assertNotNull($animal->getEspece());
        $this->assertNotNull($animal->getEtatSante());
        $this->assertNotNull($animal->getDateNaissance());
        $this->assertNotNull($animal->getFerme());
        $this->assertGreaterThanOrEqual(3, strlen($animal->getEspece()));
    }
}
