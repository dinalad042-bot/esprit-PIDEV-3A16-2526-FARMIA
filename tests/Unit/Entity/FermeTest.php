<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Ferme;
use App\Entity\User;
use App\Entity\Animal;
use App\Entity\Plante;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Ferme entity.
 *
 * TEST: Entity behavior without database
 * Reason: Validate getters/setters and business logic in isolation
 * Fat tail covered: Null handling, type safety, relationship management
 *
 * @covers \App\Entity\Ferme
 */
class FermeTest extends TestCase
{
    /**
     * TEST: Constructor initializes collections and timestamps
     */
    public function testConstructorInitializesCollectionsAndTimestamps(): void
    {
        $ferme = new Ferme();

        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $ferme->getAnalyses());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $ferme->getPlantes());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $ferme->getAnimals());
        $this->assertNotNull($ferme->getCreatedAt());
        $this->assertNotNull($ferme->getUpdatedAt());
    }

    /**
     * TEST: All getters and setters work correctly
     */
    public function testGettersAndSetters(): void
    {
        $ferme = new Ferme();

        $ferme->setNomFerme('Ma Ferme');
        $this->assertEquals('Ma Ferme', $ferme->getNomFerme());

        $ferme->setLieu('Tunis');
        $this->assertEquals('Tunis', $ferme->getLieu());

        $ferme->setSurface(150.5);
        $this->assertEquals(150.5, $ferme->getSurface());

        $ferme->setLatitude(36.8);
        $this->assertEquals(36.8, $ferme->getLatitude());

        $ferme->setLongitude(10.18);
        $this->assertEquals(10.18, $ferme->getLongitude());
    }

    /**
     * TEST: ID alias returns same as getId
     */
    public function testIdFermeAliasReturnsSameAsGetId(): void
    {
        $ferme = new Ferme();
        $this->assertNull($ferme->getId());
        $this->assertNull($ferme->getIdFerme());
    }

    /**
     * TEST: User bidirectional relationship
     */
    public function testUserBidirectionalRelationship(): void
    {
        $ferme = new Ferme();
        $user = new User();

        $ferme->setUser($user);
        $this->assertSame($user, $ferme->getUser());

        $ferme->setUser(null);
        $this->assertNull($ferme->getUser());
    }

    /**
     * TEST: Add animal sets ferme on animal
     */
    public function testAddAnimalSetsFermeOnAnimal(): void
    {
        $ferme = new Ferme();
        $animal = new Animal();

        $ferme->addAnimal($animal);

        $this->assertTrue($ferme->getAnimals()->contains($animal));
        $this->assertSame($ferme, $animal->getFerme());
    }

    /**
     * TEST: Add plante sets ferme on plante
     */
    public function testAddPlanteSetsFermeOnPlante(): void
    {
        $ferme = new Ferme();
        $plante = new Plante();

        $ferme->addPlante($plante);

        $this->assertTrue($ferme->getPlantes()->contains($plante));
        $this->assertSame($ferme, $plante->getFerme());
    }

    /**
     * TEST: __toString returns nomFerme and lieu
     */
    public function testToStringReturnsNomFermeAndLieu(): void
    {
        $ferme = new Ferme();
        $ferme->setNomFerme('Belle Ferme');
        $ferme->setLieu('Sfax');

        $this->assertEquals('Belle Ferme - Sfax', (string) $ferme);
    }
}
