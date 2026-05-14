<?php

namespace App\Service\ERP;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRateService
{
    private const API_BASE = 'https://open.er-api.com/v6/latest/';

    public function __construct(private HttpClientInterface $httpClient) {}

    public function getRate(string $base, string $target): ?float
    {
        try {
            $response = $this->httpClient->request('GET', self::API_BASE . strtoupper($base), ['timeout' => 5]);
            if ($response->getStatusCode() !== 200) return null;
            $data = $response->toArray(false);
            if (!isset($data['rates'][strtoupper($target)])) return null;
            return (float) $data['rates'][strtoupper($target)];
        } catch (\Throwable) {
            return null;
        }
    }

    public function convert(float $amount, string $base, string $target): ?float
    {
        $rate = $this->getRate($base, $target);
        if ($rate === null) return null;
        return round($amount * $rate, 2, PHP_ROUND_HALF_UP);
    }

    public function getCurrencyCodes(string $base = 'EUR'): array
    {
        try {
            $response = $this->httpClient->request('GET', self::API_BASE . strtoupper($base), ['timeout' => 5]);
            if ($response->getStatusCode() !== 200) return [];
            $data = $response->toArray(false);
            if (!isset($data['rates'])) return [];
            $codes = array_keys($data['rates']);
            sort($codes);
            return $codes;
        } catch (\Throwable) {
            return [];
        }
    }
}
