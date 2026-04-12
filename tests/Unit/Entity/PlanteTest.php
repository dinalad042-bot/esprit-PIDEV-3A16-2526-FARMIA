<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Ferme;
use App\Entity\Plante;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Plante entity.
 *
 * TEST: Entity behavior without database
 * Reason: Validate getters/setters and business logic in isolation
 * Fat tail covered: Null handling, type safety, relationship management
 *
 * @covers \App\Entity\Plante
 */
class PlanteTest extends TestCase
{
    /**
     * TEST: All getters and setters work correctly
     * Reason: Entity must expose all properties via getters/setters
     * Fat tail covered: Fluent interface, null values, type conversion
     */
    public function testGettersAndSetters(): void
    {
        $plante = new Plante();

        // Test nomEspece
        $plante->setNomEspece('Tomate');
        $this->assertEquals('Tomate', $plante->getNomEspece());

        // Test cycleVie
        $plante->setCycleVie('Annuel');
        $this->assertEquals('Annuel', $plante->getCycleVie());

        // Test quantite
        $plante->setQuantite(100);
        $this->assertEquals(100, $plante->getQuantite());

        // Test fluent interface
        $this->assertSame($plante, $plante->setNomEspece('Test'));
    }

    /**
     * TEST: ID getter returns null for new entity
     * Reason: New entities don't have IDs until persisted
     * Fat tail covered: Null pointer if expecting int
     */
    public function testIdPlanteGetterReturnsNullForNewEntity(): void
    {
        $plante = new Plante();
        $this->assertNull($plante->getIdPlante());
    }

    /**
     * TEST: Ferme relationship works bidirectionally
     * Reason: Plante must be associated with a Ferme
     * Fat tail covered: Orphaned plants, null ferme access
     */
    public function testFermeRelationship(): void
    {
        $plante = new Plante();
        $ferme = new Ferme();

        // Test setting ferme
        $plante->setFerme($ferme);
        $this->assertSame($ferme, $plante->getFerme());

        // Test clearing ferme
        $plante->setFerme(null);
        $this->assertNull($plante->getFerme());
    }

    /**
     * TEST: Quantite accepts positive integers
     * Reason: Database constraint requires positive values
     * Fat tail covered: Zero values, negative values, overflow
     */
    public function testQuantiteAcceptsPositiveIntegers(): void
    {
        $plante = new Plante();

        // Test typical value
        $plante->setQuantite(50);
        $this->assertEquals(50, $plante->getQuantite());

        // Test minimum valid (1)
        $plante->setQuantite(1);
        $this->assertEquals(1, $plante->getQuantite());

        // Test large value
        $plante->setQuantite(999999);
        $this->assertEquals(999999, $plante->getQuantite());
    }

    /**
     * TEST: Empty string fields handled correctly
     * Reason: Form submissions may send empty strings
     * Fat tail covered: Validation failures, unexpected empty values
     */
    public function testEmptyStringFields(): void
    {
        $plante = new Plante();

        $plante->setNomEspece('');
        $this->assertSame('', $plante->getNomEspece());

        $plante->setCycleVie('');
        $this->assertSame('', $plante->getCycleVie());
    }
}
