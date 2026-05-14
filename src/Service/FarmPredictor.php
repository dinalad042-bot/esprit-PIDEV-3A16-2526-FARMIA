<?php

namespace App\Service;

use App\Entity\Ferme;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FarmPredictor
{
    private $httpClient;
    private $weatherApiKey;
    private $groqApiKey;

    public function __construct(HttpClientInterface $httpClient, string $weatherApiKey, string $groqApiKey)
    {
        $this->httpClient = $httpClient;
        $this->weatherApiKey = $weatherApiKey;
        $this->groqApiKey = $groqApiKey;
    }

    /**
     * Génère un plan prédictif complet en croisant Météo, Surface et IA
     */
    public function generateFullPlan(Ferme $ferme): string
    {
        // 1. Récupération des données météo réelles
        $weatherInfo = $this->getWeatherData($ferme);
        
        // 2. Préparation du contexte pour l'IA
        $prompt = "Tu es un expert en agriculture de précision (Smart Farming). 
        Analyse les données suivantes pour la ferme '{$ferme->getNomFerme()}' :
        - Surface disponible : {$ferme->getSurface()} m²
        - Localisation : {$ferme->getLieu()} (Tunisie)
        - Météo actuelle : {$weatherInfo}

        Génère un plan prédictif structuré :
        1. **Capacité Animale** : Combien d'animaux (vaches, moutons ou volailles) peut-on élever sans surpeuplement ?
        2. **Cultures Optimales** : Quelles plantes cultiver selon la surface et la météo actuelle ?
        3. **Plan d'Action** : Recommandations sur l'irrigation ou la protection des sols pour les 7 prochains jours.
        4. **Analyse de Risques** : Prédire les maladies potentielles (plantes ou animaux) liées à l'humidité/température.
        
        Réponds de manière concise, professionnelle et en français.";

        return $this->callGroq($prompt);
    }

    /**
     * Appelle l'API OpenWeather pour obtenir le climat local
     */
    private function getWeatherData(Ferme $ferme): string
    {
        try {
            // On utilise le champ 'lieu' de l'entité Ferme pour la ville
            $city = $ferme->getLieu();
            $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid={$this->weatherApiKey}&units=metric&lang=fr";
            
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();

            $temp = $data['main']['temp'];
            $desc = $data['weather'][0]['description'];
            $hum = $data['main']['humidity'];

            return "Température: {$temp}°C, Ciel: {$desc}, Humidité: {$hum}%";
        } catch (\Exception $e) {
            return "Données météo locales indisponibles (Vérifiez la clé API ou le nom de la ville).";
        }
    }

    /**
     * Envoie le prompt à l'API Groq (Llama 3)
     */
    private function callGroq(string $prompt): string
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Tu es un assistant agricole intelligent.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.7,
                ],
            ]);

            $result = $response->toArray();
            return $result['choices'][0]['message']['content'];
        } catch (\Exception $e) {
            return "Désolé, l'analyse prédictive par IA est temporairement indisponible. Erreur : " . $e->getMessage();
        }
    }
}