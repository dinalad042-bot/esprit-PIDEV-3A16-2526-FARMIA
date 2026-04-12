<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private HttpClientInterface $client;
    private string $apiKey;

    /**
     * Le paramètre $weatherApiKey est automatiquement injecté par Symfony 
     * grâce au 'bind' dans services.yaml
     */
    public function __construct(HttpClientInterface $client, string $weatherApiKey)
    {
        $this->client = $client;
        $this->apiKey = $weatherApiKey;
    }

    /**
     * Récupère la météo pour une ville donnée
     */
    public function getWeather(string $city): array
    {
        try {
            $response = $this->client->request('GET', "https://api.openweathermap.org/data/2.5/weather", [
                'query' => [
                    'q'     => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang'  => 'fr'
                ]
            ]);

            // Vérification du code de statut HTTP
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Erreur API OpenWeather : ' . $response->getStatusCode());
            }

            return $response->toArray();

        } catch (\Exception $e) {
            // En cas d'erreur ou de clé non active, on renvoie un tableau de secours (fallback)
            // Cela permet à la vue Twig de ne pas planter.
            return [
                'main' => [
                    'temp'       => 0, 
                    'feels_like' => 0, 
                    'humidity'   => 0
                ],
                'weather' => [
                    [
                        'description' => 'Météo indisponible (clé API en cours d\'activation)', 
                        'icon'        => '01n'
                    ]
                ],
                'wind'   => ['speed' => 0],
                'clouds' => ['all' => 0]
            ];
        }
    }
}