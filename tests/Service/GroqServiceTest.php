<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\GroqService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Unit tests for GroqService AI diagnosis functionality.
 *
 * @covers \App\Service\GroqService
 */
final class GroqServiceTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private GroqService $groqService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->groqService = new GroqService(
            $this->httpClient,
            'test-api-key',
            'meta-llama/llama-4-scout-17b-16e-instruct'
        );
    }

    protected function tearDown(): void
    {
        unset($this->httpClient, $this->groqService);
        parent::tearDown();
    }

    /**
     * TEST: Successful AI diagnosis with valid image URL
     * Reason: Verify happy path for AI diagnosis
     */
    public function testDiagnosePlantDiseaseReturnsSuccessResult(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('toArray')->willReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'plant_name' => 'Tomato',
                            'disease_name' => 'Late Blight',
                            'confidence' => 85.5,
                            'description' => 'A serious fungal disease',
                            'symptoms' => ['Dark spots on leaves', 'White mold on underside'],
                            'treatment' => ['Apply fungicide', 'Remove infected plants'],
                            'prevention' => ['Rotate crops', 'Improve air circulation'],
                            'is_healthy' => false
                        ])
                    ]
                ]
            ]
        ]);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.groq.com/openai/v1/chat/completions',
                $this->callback(function ($options) {
                    return isset($options['headers']['Authorization']) &&
                           str_contains($options['json']['model'], 'llama');
                })
            )
            ->willReturn($mockResponse);

        $result = $this->groqService->diagnosePlantDisease('https://example.com/image.jpg');

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Tomato', $result->getPlantName());
        $this->assertEquals('Late Blight', $result->getDiseaseName());
        $this->assertEquals(85.5, $result->getConfidence());
        $this->assertFalse($result->isHealthy());
    }

    /**
     * TEST: AI diagnosis with healthy plant
     * Reason: Verify healthy plant detection
     */
    public function testDiagnosePlantDiseaseReturnsHealthyResult(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('toArray')->willReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'plant_name' => 'Rose',
                            'disease_name' => null,
                            'confidence' => 92.0,
                            'description' => 'Plant appears healthy',
                            'symptoms' => [],
                            'treatment' => [],
                            'prevention' => ['Regular watering', 'Proper sunlight'],
                            'is_healthy' => true
                        ])
                    ]
                ]
            ]
        ]);

        $this->httpClient
            ->method('request')
            ->willReturn($mockResponse);

        $result = $this->groqService->diagnosePlantDisease('https://example.com/healthy.jpg');

        $this->assertTrue($result->isSuccess());
        $this->assertTrue($result->isHealthy());
        $this->assertNull($result->getDiseaseName());
    }

    /**
     * TEST: API error handling
     * Reason: Verify graceful failure handling
     */
    public function testDiagnosePlantDiseaseHandlesApiError(): void
    {
        $this->httpClient
            ->method('request')
            ->willThrowException(new \Exception('API Error'));

        $result = $this->groqService->diagnosePlantDisease('https://example.com/image.jpg');

        $this->assertFalse($result->isSuccess());
        $this->assertEquals('Failed to get AI diagnosis', $result->getErrorMessage());
    }

    /**
     * TEST: Invalid JSON response handling
     * Reason: Verify handling of malformed API responses
     */
    public function testDiagnosePlantDiseaseHandlesInvalidJsonResponse(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('toArray')->willReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => 'invalid json'
                    ]
                ]
            ]
        ]);

        $this->httpClient
            ->method('request')
            ->willReturn($mockResponse);

        $result = $this->groqService->diagnosePlantDisease('https://example.com/image.jpg');

        $this->assertFalse($result->isSuccess());
        $this->assertNotNull($result->getErrorMessage());
    }

    /**
     * TEST: Empty choices array handling
     * Reason: Verify handling of unexpected API response structure
     */
    public function testDiagnosePlantDiseaseHandlesEmptyChoices(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('toArray')->willReturn([
            'choices' => []
        ]);

        $this->httpClient
            ->method('request')
            ->willReturn($mockResponse);

        $result = $this->groqService->diagnosePlantDisease('https://example.com/image.jpg');

        $this->assertFalse($result->isSuccess());
    }
}
