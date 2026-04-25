<?php

namespace App\Tests\Staging;

use App\Entity\Analyse;
use App\Entity\Ferme;
use App\Entity\User;
use App\Enum\StatutAnalyse;
use App\Tests\BaseWebTestCase;
use App\Service\GroqService;
use App\DTO\DiagnosisResult;

/**
 * Staging/Pre-production handshake testing for Expert Module
 * Validates button→action→response chains without browser automation
 */
class ExpertModuleHandshakeTest extends BaseWebTestCase
{
    private $groqService;
    private $expertUser;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->expertUser = $this->loginAsExpert();
        
        // Mock AI service for consistent testing
        $this->groqService = $this->createMock(GroqService::class);
        $diagnosisResult = new DiagnosisResult();
        $diagnosisResult->condition = 'Healthy Plant';
        $diagnosisResult->confidence = 0.95;
        $diagnosisResult->rawResponse = '{"status": "healthy"}';
        
        $this->groqService->method('generateVisionDiagnostic')
            ->willReturn($diagnosisResult);
    }

    /**
     * TEST: Expert Analyses List Handshake
     */
    public function testExpertAnalysesListHandshake(): void
    {
        // Create test data
        $analyse = $this->createAnalyse(StatutAnalyse::EN_COURS);
        
        // Test page renders
        self::$client->request('GET', '/expert/analyses');
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('expert_analyses_list');
        
        // Test correct data display
        $crawler = self::$client->getCrawler();
        $this->assertSelectorExists('h2:contains("Mes Analyses")');
        $this->assertSelectorExists('a[href*="/expert/analyse/new"]');
        $this->assertSelectorExists('a[href*="/expert/analyse/' . $analyse->getId() . '"]');
    }

    /**
     * TEST: AI Diagnosis Handshake
     */
    public function testAIDiagnosisHandshake(): void
    {
        $analyse = $this->createAnalyse(StatutAnalyse::EN_COURS);
        $analyse->setImageUrl('https://example.com/plant-image.jpg');
        self::$em->persist($analyse);
        self::$em->flush();
        
        // Setup mock with string confidence
        $diagnosisResult = new DiagnosisResult();
        $diagnosisResult->condition = 'Healthy Plant';
        $diagnosisResult->confidence = 'HIGH';
        $diagnosisResult->rawResponse = '{"status": "healthy"}';
        
        $this->groqService->method('generateVisionDiagnostic')
            ->willReturn($diagnosisResult);
        
        // Replace the service in container
        self::getContainer()->set(GroqService::class, $this->groqService);
        
        // Trigger AI diagnosis
        self::$client->request('POST', '/expert/analyse/' . $analyse->getId() . '/diagnose');
        
        // Verify response
        $this->assertResponseRedirects('/expert/analyse/' . $analyse->getId());
        self::$client->followRedirect();
        $this->assertSelectorExists('.alert-success:contains("Diagnostic IA effectué")');
        
        // Verify AI results stored
        self::$em->refresh($analyse);
        $this->assertNotNull($analyse->getAiDiagnosisResult());
    }

    /**
     * TEST: Security Access Control Handshake
     */
    public function testSecurityAccessControlHandshake(): void
    {
        // Test non-expert access
        $this->logout();
        $this->loginAsUser();
        
        self::$client->request('GET', '/expert/analyses');
        // May redirect instead of 403
        $this->assertTrue(in_array(self::$client->getResponse()->getStatusCode(), [403, 302]));
        
        // Test unauthenticated access
        $this->logout();
        self::$client->request('GET', '/expert/analyses');
        $this->assertResponseRedirects('/login');
    }

    /**
     * Helper: Create a test analysis
     */
    private function createAnalyse(StatutAnalyse $status = StatutAnalyse::EN_ATTENTE): Analyse
    {
        $expert = $this->expertUser;
        
        $ferme = new Ferme();
        $ferme->setNomFerme('Test Farm - Handshake');
        $ferme->setLieu('Test Location');
        $ferme->setSurface(100);
        $ferme->setProprietaire($expert);
        
        $analyse = new Analyse();
        $analyse->setDateAnalyse(new \DateTime());
        $analyse->setResultatTechnique('Test analysis for handshake testing');
        $analyse->setStatut($status->value);
        $analyse->setFerme($ferme);
        $analyse->setDemandeur($expert);
        
        if ($status !== StatutAnalyse::EN_ATTENTE) {
            $analyse->setTechnicien($expert);
        }
        
        self::$em->persist($ferme);
        self::$em->persist($analyse);
        self::$em->flush();
        
        return $analyse;
    }
}