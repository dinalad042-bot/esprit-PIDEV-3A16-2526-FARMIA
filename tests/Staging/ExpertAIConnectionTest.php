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
 * Staging tests for Expert AI Module connections
 * Validates AI diagnosis handshake without external dependencies
 * 
 * @covers \App\Controller\Web\ExpertAIController
 */
class ExpertAIConnectionTest extends BaseWebTestCase
{
    private $groqService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsExpert();
        
        // Create a more detailed mock AI service
        $this->groqService = $this->createMock(GroqService::class);
    }

    /**
     * TEST: AI Diagnosis Handshake - Complete Flow
     */
    public function testAIDiagnosisCompleteHandshake(): void
    {
        // Setup mock AI response
        $diagnosisResult = new DiagnosisResult();
        $diagnosisResult->condition = 'Early Blight';
        $diagnosisResult->symptoms = ['Brown spots on leaves', 'Yellowing around spots'];
        $diagnosisResult->treatment = 'Apply copper-based fungicide';
        $diagnosisResult->prevention = 'Improve air circulation';
        $diagnosisResult->urgency = 'medium';
        $diagnosisResult->needsExpert = true;
        $diagnosisResult->confidence = 0.87;
        $diagnosisResult->rawResponse = '{"disease": "early_blight"}';
        
        $this->groqService->method('generateVisionDiagnostic')
            ->willReturn($diagnosisResult);
            
        self::getContainer()->set(GroqService::class, $this->groqService);
        
        // Create analysis with image
        $analyse = $this->createAnalysisWithImage();
        
        // Fire the diagnosis
        self::$client->request('POST', '/expert/analyse/' . $analyse->getId() . '/diagnose');
        
        // Verify redirect and flash message
        $this->assertResponseRedirects('/expert/analyse/' . $analyse->getId());
        self::$client->followRedirect();
        
        $this->assertSelectorExists('.alert-success:contains("Diagnostic IA effectué")');
        
        // Verify AI results stored
        self::$em->refresh($analyse);
        $this->assertNotNull($analyse->getAiDiagnosisResult());
        $this->assertEquals(0.87, $analyse->getAiConfidenceScore());
        $this->assertNotNull($analyse->getAiDiagnosisDate());
        
        // Verify AI result structure
        $aiData = json_decode($analyse->getAiDiagnosisResult(), true);
        $this->assertIsArray($aiData);
        $this->assertEquals('Early Blight', $aiData['condition']);
        $this->assertTrue($aiData['needsExpert']);
    }
    /**
     * Helper: Create analysis with image for AI testing
     */
    private function createAnalysisWithImage(): Analyse
    {
        $expert = $this->getUser();
        
        $ferme = new Ferme();
        $ferme->setNomFerme('AI Test Farm');
        $ferme->setLieu('Test Location');
        $ferme->setSurface(100);
        $ferme->setProprietaire($expert);
        
        $analyse = new Analyse();
        $analyse->setDateAnalyse(new \DateTime());
        $analyse->setResultatTechnique('AI handshake test analysis');
        $analyse->setStatut(StatutAnalyse::EN_COURS);
        $analyse->setFerme($ferme);
        $analyse->setTechnicien($expert);
        $analyse->setImageUrl('https://example.com/test-plant-image.jpg');
        
        self::$em->persist($ferme);
        self::$em->persist($analyse);
        self::$em->flush();
        
        return $analyse;
    }

    /**
     * Helper: Create analysis without image
     */
    private function createAnalysisWithoutImage(): Analyse
    {
        $expert = $this->getUser();
        
        $ferme = new Ferme();
        $ferme->setNomFerme('AI Test Farm');
        $ferme->setLieu('Test Location');
        $ferme->setSurface(100);
        $ferme->setProprietaire($expert);
        
        $analyse = new Analyse();
        $analyse->setDateAnalyse(new \DateTime());
        $analyse->setResultatTechnique('AI handshake test analysis');
        $analyse->setStatut(StatutAnalyse::EN_COURS);
        $analyse->setFerme($ferme);
        $analyse->setTechnicien($expert);
        
        self::$em->persist($ferme);
        self::$em->persist($analyse);
        self::$em->flush();
        
        return $analyse;
    }

    /**
     * Helper: Create analysis with existing AI diagnosis
     */
    private function createAnalysisWithAIDiagnosis(): Analyse
    {
        $analyse = $this->createAnalysisWithImage();
        
        // Set AI diagnosis data
        $aiData = [
            'condition' => 'Early Blight',
            'symptoms' => ['Brown spots on leaves', 'Yellowing around spots'],
            'treatment' => 'Apply copper-based fungicide',
            'prevention' => 'Improve air circulation',
            'urgency' => 'medium',
            'needsExpert' => true,
            'rawResponse' => '{"disease": "early_blight"}'
        ];
        
        $analyse->setAiDiagnosisResult(json_encode($aiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $analyse->setAiConfidenceScore(0.87);
        $analyse->setAiDiagnosisDate(new \DateTime('-1 day'));
        
        self::$em->persist($analyse);
        self::$em->flush();
        
        return $analyse;
    }
}