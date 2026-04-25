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
        return $this->getWeatherForLocation($city);
    }

    /**
     * Récupère la météo pour un lieu donné (ville ou localisation)
     */
    public function getWeatherForLocation(string $location): array
    {
        try {
            $response = $this->client->request('GET', "https://api.openweathermap.org/data/2.5/weather", [
                'query' => [
                    'q'     => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang'  => 'fr'
                ]
            ]);

            // Vérification du code de statut HTTP
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Erreur API OpenWeather : ' . $response->getStatusCode());
            }

            $data = $response->toArray();
            $data['success'] = true;
            return $data;

        } catch (\Exception $e) {
            // En cas d'erreur ou de clé non active, on renvoie un tableau de secours (fallback)
            // Cela permet à la vue Twig de ne pas planter.
            return [
                'success' => false,
                'error'   => 'Clé API inactive',
                'main'    => [
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

    /**
     * Retourne un conseil agricole basé sur la météo.
     * Accepte soit un nom de ville (string), soit des données météo déjà récupérées (array).
     */
    public function getAgriAdvice(string|array|null $cityOrWeather = null): string
    {
        if (!$cityOrWeather) {
            return 'Conseil météo indisponible (ville non précisée)';
        }

        if (is_array($cityOrWeather)) {
            $weather = $cityOrWeather;
            $city = $weather['name'] ?? 'Localisation inconnue';
            if (empty($weather['success'])) {
                return 'Données météo non disponibles.';
            }
        } else {
            $city = $cityOrWeather;
            $weather = $this->getWeatherForLocation($city);
            if (empty($weather['success'])) {
                return 'Conseil météo indisponible (clé API inactive)';
            }
        }

        $temp = $weather['main']['temp'] ?? 0;
        $humidity = $weather['main']['humidity'] ?? 0;
        $wind = $weather['wind']['speed'] ?? 0;

        $advice = "À $city : ";
        if ($temp > 30) {
            $advice .= "Température élevée, pensez à l'irrigation. ";
        } elseif ($temp < 5) {
            $advice .= "Risque de gel, protégez les cultures. ";
        }
        if ($humidity > 80) {
            $advice .= "Forte humidité, surveillez les maladies fongiques. ";
        } elseif ($humidity < 30) {
            $advice .= "Air sec, augmentez l'arrosage. ";
        }
        $advice .= "Vent à $wind m/s.";
        return $advice;
    }
}