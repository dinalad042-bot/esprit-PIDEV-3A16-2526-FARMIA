<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Analyse;
use App\Entity\Conseil;
use App\Entity\User;
use App\Entity\Ferme;
use App\Entity\Animal;
use App\Entity\Plante;
use App\Enum\StatutAnalyse;
use PHPUnit\Framework\TestCase;

/**
 * TEST: Analyse Entity - Expert Module Core Entity
 * Reason: Verify all fields including new AI diagnosis fields work correctly
 * Fat tail covered: AI fields initialization, status workflow, bidirectional relationships
 *
 * @covers \App\Entity\Analyse
 */
class AnalyseTest extends TestCase
{
    /**
     * TEST: Analyse instantiation with default values
     * Reason: Constructor must initialize collections and default status
     */
    public function testDefaultValuesOnConstruction(): void
    {
        $analyse = new Analyse();

        $this->assertNotNull($analyse->getDateAnalyse());
        $this->assertInstanceOf(\DateTimeInterface::class, $analyse->getDateAnalyse());
        $this->assertEquals('en_attente', $analyse->getStatut());
        $this->assertEquals(StatutAnalyse::EN_ATTENTE, $analyse->getStatutEnum());
        $this->assertCount(0, $analyse->getConseils());
        $this->assertFalse($analyse->hasAiDiagnosis());
        $this->assertNull($analyse->getAiDiagnosisResult());
        $this->assertNull($analyse->getAiDiagnosisDate());
        $this->assertNull($analyse->getAiConfidenceScore());
    }

    /**
     * TEST: Getters and setters for basic fields
     * Reason: Verify standard entity field access
     */
    public function testGettersAndSetters(): void
    {
        $analyse = new Analyse();
        $date = new \DateTime('2026-04-14 10:00:00');

        $analyse->setDateAnalyse($date);
        $this->assertEquals($date, $analyse->getDateAnalyse());

        $analyse->setResultatTechnique('Test result');
        $this->assertEquals('Test result', $analyse->getResultatTechnique());

        $analyse->setImageUrl('https://example.com/image.jpg');
        $this->assertEquals('https://example.com/image.jpg', $analyse->getImageUrl());
    }

    /**
     * TEST: Status workflow transitions
     * Reason: Expert module requires status workflow
     */
    public function testStatusTransitions(): void
    {
        $analyse = new Analyse();

        // Default status
        $this->assertEquals('en_attente', $analyse->getStatut());
        $this->assertEquals(StatutAnalyse::EN_ATTENTE, $analyse->getStatutEnum());

        // Transition to en_cours
        $analyse->setStatut('en_cours');
        $this->assertEquals('en_cours', $analyse->getStatut());
        $this->assertEquals(StatutAnalyse::EN_COURS, $analyse->getStatutEnum());

        // Transition to terminee
        $analyse->setStatut('terminee');
        $this->assertEquals('terminee', $analyse->getStatut());
        $this->assertEquals(StatutAnalyse::TERMINEE, $analyse->getStatutEnum());

        // Transition to annulee
        $analyse->setStatut('annulee');
        $this->assertEquals('annulee', $analyse->getStatut());
        $this->assertEquals(StatutAnalyse::ANNULEE, $analyse->getStatutEnum());
    }

    /**
     * TEST: AI diagnosis fields
     * Reason: Phase 2 AI integration requires these fields
     */
    public function testAiDiagnosisFields(): void
    {
        $analyse = new Analyse();
        $aiDate = new \DateTime('2026-04-14 12:00:00');

        $this->assertFalse($analyse->hasAiDiagnosis());

        $analyse->setAiDiagnosisResult('Healthy plant detected');
        $analyse->setAiDiagnosisDate($aiDate);
        $analyse->setAiConfidenceScore('95.5');

        $this->assertTrue($analyse->hasAiDiagnosis());
        $this->assertEquals('Healthy plant detected', $analyse->getAiDiagnosisResult());
        $this->assertEquals($aiDate, $analyse->getAiDiagnosisDate());
        $this->assertEquals('95.5', $analyse->getAiConfidenceScore());
    }

    /**
     * TEST: AI diagnosis with null values
     * Reason: Ensure hasAiDiagnosis works correctly with partial data
     */
    public function testAiDiagnosisWithPartialData(): void
    {
        $analyse = new Analyse();

        // Only result set - should return true
        $analyse->setAiDiagnosisResult('Some result');
        $this->assertTrue($analyse->hasAiDiagnosis());
    }

    /**
     * TEST: AI diagnosis cleared
     * Reason: Ensure hasAiDiagnosis returns false when result is cleared
     */
    public function testAiDiagnosisCleared(): void
    {
        $analyse = new Analyse();

        $analyse->setAiDiagnosisResult('Some result');
        $this->assertTrue($analyse->hasAiDiagnosis());

        $analyse->setAiDiagnosisResult(null);
        $this->assertFalse($analyse->hasAiDiagnosis());
    }

    /**
     * TEST: Technicien relationship
     * Reason: Expert assignment is core functionality
     */
    public function testTechnicienRelationship(): void
    {
        $analyse = new Analyse();
        $technicien = new User();

        $this->assertNull($analyse->getTechnicien());

        $analyse->setTechnicien($technicien);
        $this->assertSame($technicien, $analyse->getTechnicien());

        $analyse->setTechnicien(null);
        $this->assertNull($analyse->getTechnicien());
    }

    /**
     * TEST: Demandeur relationship
     * Reason: Analyse has a demandeur who requested it
     */
    public function testDemandeurRelationship(): void
    {
        $analyse = new Analyse();
        $demandeur = new User();

        $this->assertNull($analyse->getDemandeur());

        $analyse->setDemandeur($demandeur);
        $this->assertSame($demandeur, $analyse->getDemandeur());
    }

    /**
     * TEST: Ferme relationship
     * Reason: Analyse belongs to a farm
     */
    public function testFermeRelationship(): void
    {
        $analyse = new Analyse();
        $ferme = new Ferme();

        $this->assertNull($analyse->getFerme());

        $analyse->setFerme($ferme);
        $this->assertSame($ferme, $analyse->getFerme());
    }

    /**
     * TEST: AnimalCible relationship
     * Reason: Analyse can be for an animal
     */
    public function testAnimalCibleRelationship(): void
    {
        $analyse = new Analyse();
        $animal = new Animal();

        $this->assertNull($analyse->getAnimalCible());

        $analyse->setAnimalCible($animal);
        $this->assertSame($animal, $analyse->getAnimalCible());
    }

    /**
     * TEST: PlanteCible relationship
     * Reason: Analyse can be for a plant
     */
    public function testPlanteCibleRelationship(): void
    {
        $analyse = new Analyse();
        $plante = new Plante();

        $this->assertNull($analyse->getPlanteCible());

        $analyse->setPlanteCible($plante);
        $this->assertSame($plante, $analyse->getPlanteCible());
    }

    /**
     * TEST: Conseils collection
     * Reason: Expert provides conseils for analyses
     */
    public function testConseilsCollection(): void
    {
        $analyse = new Analyse();
        $conseil = new Conseil();

        $this->assertCount(0, $analyse->getConseils());
        $this->assertEquals(0, $analyse->getNbConseils());

        $analyse->addConseil($conseil);
        $this->assertCount(1, $analyse->getConseils());
        $this->assertEquals(1, $analyse->getNbConseils());
        $this->assertTrue($analyse->getConseils()->contains($conseil));

        $analyse->removeConseil($conseil);
        $this->assertCount(0, $analyse->getConseils());
        $this->assertEquals(0, $analyse->getNbConseils());
    }

    /**
     * TEST: Bidirectional relationship with Conseil
     * Reason: Ensure conseil knows its analyse
     */
    public function testConseilBidirectionalRelationship(): void
    {
        $analyse = new Analyse();
        $conseil = new Conseil();

        $analyse->addConseil($conseil);

        // The conseil should have the analyse set
        $this->assertSame($analyse, $conseil->getAnalyse());

        $analyse->removeConseil($conseil);

        // The conseil should no longer have the analyse
        $this->assertNull($conseil->getAnalyse());
    }

    /**
     * TEST: DescriptionDemande field
     * Reason: Analyse can have a description from the demandeur
     */
    public function testDescriptionDemande(): void
    {
        $analyse = new Analyse();

        $this->assertNull($analyse->getDescriptionDemande());

        $analyse->setDescriptionDemande('Please analyze this plant');
        $this->assertEquals('Please analyze this plant', $analyse->getDescriptionDemande());
    }

    /**
     * TEST: Fluent interface for setters
     * Reason: Entity should return static for method chaining
     */
    public function testFluentInterface(): void
    {
        $analyse = new Analyse();
        $date = new \DateTime();

        $this->assertSame($analyse, $analyse->setDateAnalyse($date));
        $this->assertSame($analyse, $analyse->setResultatTechnique('test'));
        $this->assertSame($analyse, $analyse->setStatut('en_cours'));
        $this->assertSame($analyse, $analyse->setAiDiagnosisResult('result'));
    }
}
