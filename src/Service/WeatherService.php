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
            // Validate API key is configured
            if (empty($this->apiKey)) {
                return $this->getErrorResponse('Clé API OpenWeather non configurée');
            }

            $response = $this->client->request('GET', "https://api.openweathermap.org/data/2.5/weather", [
                'query' => [
                    'q'     => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang'  => 'fr'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            
            // Handle different HTTP status codes
            if ($statusCode === 404) {
                return $this->getErrorResponse("Ville non trouvée : \"$city\". Vérifiez l'orthographe ou utilisez le code postal.");
            } elseif ($statusCode === 401) {
                return $this->getErrorResponse('Clé API OpenWeather invalide ou expirée');
            } elseif ($statusCode !== 200) {
                return $this->getErrorResponse("Erreur API OpenWeather : HTTP $statusCode");
            }

            return $response->toArray();

        } catch (\Exception $e) {
            // Log the error for debugging
            $errorMsg = $e->getMessage();
            
            // Provide user-friendly error message
            if (strpos($errorMsg, 'Connection refused') !== false) {
                return $this->getErrorResponse('Impossible de se connecter au service météo');
            } elseif (strpos($errorMsg, 'Timeout') !== false) {
                return $this->getErrorResponse('Le service météo met trop de temps à répondre');
            }
            
            return $this->getErrorResponse('Erreur lors de la récupération de la météo');
        }
    }

    /**
     * Retourne une réponse d'erreur structurée
     */
    private function getErrorResponse(string $errorMessage): array
    {
        return [
            'main' => [
                'temp'       => 0, 
                'feels_like' => 0, 
                'humidity'   => 0
            ],
            'weather' => [
                [
                    'description' => $errorMessage, 
                    'icon'        => '01n'
                ]
            ],
            'wind'   => ['speed' => 0],
            'clouds' => ['all' => 0],
            'error' => $errorMessage
        ];
    }

    /**
     * Récupère la météo pour une localisation donnée avec format structuré
     */
    public function getWeatherForLocation(string $location): array
    {
        try {
            // Validate API key is configured
            if (empty($this->apiKey)) {
                return [
                    'success' => false,
                    'error' => 'Clé API OpenWeather non configurée'
                ];
            }

            $response = $this->client->request('GET', "https://api.openweathermap.org/data/2.5/weather", [
                'query' => [
                    'q'     => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang'  => 'fr'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            
            // Handle different HTTP status codes
            if ($statusCode === 404) {
                return [
                    'success' => false,
                    'error' => "Localisation non trouvée : \"$location\". Vérifiez l'orthographe ou utilisez le code postal."
                ];
            } elseif ($statusCode === 401) {
                return [
                    'success' => false,
                    'error' => 'Clé API OpenWeather invalide ou expirée'
                ];
            } elseif ($statusCode !== 200) {
                return [
                    'success' => false,
                    'error' => "Erreur API OpenWeather : HTTP $statusCode"
                ];
            }

            $data = $response->toArray();

            return [
                'success' => true,
                'location' => $data['name'] ?? $location,
                'country' => $data['sys']['country'] ?? '',
                'temperature' => $data['main']['temp'] ?? 0,
                'feels_like' => $data['main']['feels_like'] ?? 0,
                'humidity' => $data['main']['humidity'] ?? 0,
                'description' => $data['weather'][0]['description'] ?? '',
                'icon' => $data['weather'][0]['icon'] ?? '',
                'wind_speed' => $data['wind']['speed'] ?? 0,
                'clouds' => $data['clouds']['all'] ?? 0,
                'raw_data' => $data
            ];

        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            
            // Provide user-friendly error messages
            if (strpos($errorMsg, 'Connection refused') !== false) {
                $error = 'Impossible de se connecter au service météo';
            } elseif (strpos($errorMsg, 'Timeout') !== false) {
                $error = 'Le service météo met trop de temps à répondre';
            } else {
                $error = 'Erreur lors de la récupération de la météo : ' . $errorMsg;
            }
            
            return [
                'success' => false,
                'error' => $error
            ];
        }
    }

    /**
     * Génère un conseil agricole basé sur les données météo
     */
    public function getAgriAdvice(array $weather): string
    {
        if (!$weather['success'] ?? false) {
            return 'Données météo non disponibles.';
        }

        $temp = $weather['temperature'] ?? 0;
        $humidity = $weather['humidity'] ?? 0;
        $description = $weather['description'] ?? '';

        $advice = [];

        if ($temp > 35) {
            $advice[] = '⚠️ Chaleur extrême : Augmentez l\'irrigation et protégez les cultures sensibles.';
        } elseif ($temp < 5) {
            $advice[] = '❄️ Froid : Risque de gel. Protégez les cultures sensibles.';
        }

        if ($humidity > 80) {
            $advice[] = '💧 Humidité élevée : Risque de maladies fongiques. Améliorez la ventilation.';
        } elseif ($humidity < 30) {
            $advice[] = '🌵 Sécheresse : Augmentez l\'irrigation et paillez les cultures.';
        }

        if (strpos($description, 'pluie') !== false || strpos($description, 'rain') !== false) {
            $advice[] = '🌧️ Pluie prévue : Bonne opportunité pour l\'irrigation naturelle.';
        }

        if (strpos($description, 'orage') !== false || strpos($description, 'storm') !== false) {
            $advice[] = '⛈️ Orage : Protégez les cultures et vérifiez les installations.';
        }

        return !empty($advice) ? implode(' ', $advice) : '✅ Conditions météo favorables pour l\'agriculture.';
    }
}