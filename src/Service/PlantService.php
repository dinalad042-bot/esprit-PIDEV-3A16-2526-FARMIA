<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PlantService
{
    private $client;
    private $token;

    public function __construct(HttpClientInterface $client, string $trefleApiToken)
    {
        $this->client = $client;
        $this->token = $trefleApiToken;
    }

    public function getPlantDetails(string $name): ?array
    {
        try {
            // 1. Chercher l'ID de la plante par son nom
            $searchResponse = $this->client->request('GET', 'https://trefle.io/api/v1/plants/search', [
                'query' => ['token' => $this->token, 'q' => $name]
            ]);
            
            $searchData = $searchResponse->toArray();
            if (empty($searchData['data'])) return null;

            // 2. Récupérer les détails complets (pH, lumière, humidité, etc.)
            $plantId = $searchData['data'][0]['id'];
            $detailsResponse = $this->client->request('GET', "https://trefle.io/api/v1/plants/$plantId", [
                'query' => ['token' => $this->token]
            ]);

            return $detailsResponse->toArray()['data'];
        } catch (\Exception $e) {
            return null;
        }
    }
}