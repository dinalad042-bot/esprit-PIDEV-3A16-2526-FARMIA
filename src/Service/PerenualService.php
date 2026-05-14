<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PerenualService
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client, string $perenualApiKey)
    {
        $this->client = $client;
        $this->apiKey = $perenualApiKey;
    }

    public function getMaintenanceData(string $nom): array
    {
        try {
            // 1. Recherche de la plante pour obtenir son ID
            $response = $this->client->request('GET', 'https://perenual.com/api/species-list', [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $nom
                ]
            ]);

            $data = $response->toArray();

            if (empty($data['data'])) {
                return [];
            }

            // 2. Récupération des détails via l'ID du premier résultat
            $plantId = $data['data'][0]['id'];
            $detailResponse = $this->client->request('GET', "https://perenual.com/api/species/details/{$plantId}", [
                'query' => ['key' => $this->apiKey]
            ]);

            return $detailResponse->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}