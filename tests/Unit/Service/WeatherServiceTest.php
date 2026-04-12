<?php

namespace App\Tests\Unit\Service;

use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Unit tests for WeatherService.
 *
 * @covers \App\Service\WeatherService
 */
class WeatherServiceTest extends TestCase
{
    public function testGetWeatherForLocationReturnsArray(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('toArray')->willReturn([
            'name' => 'Tunis',
            'sys' => ['country' => 'TN'],
            'main' => ['temp' => 25.5, 'feels_like' => 27.0, 'humidity' => 60],
            'weather' => [['description' => 'clear sky', 'icon' => '01d']],
            'wind' => ['speed' => 5.5],
        ]);

        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient->method('request')->willReturn($mockResponse);

        $service = new WeatherService($mockClient, 'fake-api-key');
        $result = $service->getWeatherForLocation('Tunis');

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Tunis', $result['location']);
        $this->assertEquals(25.5, $result['temperature']);
        $this->assertEquals(60, $result['humidity']);
    }

    public function testGetWeatherForLocationHandlesError(): void
    {
        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient->method('request')->willThrowException(new \Exception('API Error'));

        $service = new WeatherService($mockClient, 'fake-api-key');
        $result = $service->getWeatherForLocation('InvalidLocation');

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function testGetAgriAdviceReturnsString(): void
    {
        $mockClient = $this->createMock(HttpClientInterface::class);
        $service = new WeatherService($mockClient, 'fake-api-key');

        $weather = [
            'success' => true,
            'temperature' => 35,
            'humidity' => 85,
            'description' => 'light rain',
        ];

        $advice = $service->getAgriAdvice($weather);

        $this->assertIsString($advice);
        $this->assertNotEmpty($advice);
    }

    public function testGetAgriAdviceHandlesFailure(): void
    {
        $mockClient = $this->createMock(HttpClientInterface::class);
        $service = new WeatherService($mockClient, 'fake-api-key');

        $weather = ['success' => false];
        $advice = $service->getAgriAdvice($weather);

        $this->assertEquals('Données météo non disponibles.', $advice);
    }
}
