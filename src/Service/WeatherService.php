<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $baseUrl = 'https://api.openweathermap.org/data/2.5'
    ) {}

    public function getWeatherForLocation(string $location): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->baseUrl . '/weather', [
                'query' => [
                    'q'     => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang'  => 'fr',
                ],
                'timeout' => 10,
            ]);

            $data = $response->toArray();

            return [
                'success'     => true,
                'location'    => $data['name'] ?? $location,
                'country'     => $data['sys']['country'] ?? '',
                'temperature' => round($data['main']['temp'] ?? 0, 1),
                'feels_like'  => round($data['main']['feels_like'] ?? 0, 1),
                'humidity'    => $data['main']['humidity'] ?? 0,
                'description' => ucfirst($data['weather'][0]['description'] ?? ''),
                'icon'        => $data['weather'][0]['icon'] ?? '01d',
                'wind_speed'  => round(($data['wind']['speed'] ?? 0) * 3.6, 1),
                'icon_url'    => 'https://openweathermap.org/img/wn/'
                                 . ($data['weather'][0]['icon'] ?? '01d')
                                 . '@2x.png',
                'raw'         => $data,
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'location' => $location,
            ];
        }
    }

    public function getAgriAdvice(array $weather): string
    {
        if (!$weather['success']) {
            return 'Données météo non disponibles.';
        }

        $temp     = $weather['temperature'];
        $humidity = $weather['humidity'];
        $desc     = strtolower($weather['description']);
        $advice   = [];

        // Temperature advice
        if ($temp > 35) {
            $advice[] = '🌡️ Forte chaleur: arrosage tôt le matin ou en soirée recommandé.';
        } elseif ($temp < 5) {
            $advice[] = '❄️ Risque de gel: protéger les cultures sensibles.';
        } elseif ($temp >= 20 && $temp <= 28) {
            $advice[] = '✅ Température optimale pour la croissance végétale.';
        }

        // Humidity advice
        if ($humidity > 80) {
            $advice[] = '💧 Humidité élevée: risque de maladies fongiques, surveiller.';
        } elseif ($humidity < 30) {
            $advice[] = '🏜️ Air très sec: augmenter la fréquence d\'arrosage.';
        }

        // Weather condition advice
        if (str_contains($desc, 'pluie') || str_contains($desc, 'rain')) {
            $advice[] = '🌧️ Pluie prévue: suspendre l\'irrigation, reporter les traitements.';
        } elseif (str_contains($desc, 'vent') || str_contains($desc, 'wind')) {
            $advice[] = '💨 Vent fort: éviter les pulvérisations de produits.';
        }

        return empty($advice)
            ? '🌤️ Conditions météo normales pour les activités agricoles.'
            : implode("\n", $advice);
    }
}
