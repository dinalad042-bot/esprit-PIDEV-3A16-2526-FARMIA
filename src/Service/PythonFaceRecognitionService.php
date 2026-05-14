<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PythonFaceRecognitionService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private string $pythonApiUrl;
    private string $pythonScriptPath;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        string $pythonApiUrl = 'http://127.0.0.1:5000'
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        // Permet d'écraser l'URL via le constructeur si injecté, sinon défaut
        $this->pythonApiUrl = $_ENV['PYTHON_API_URL'] ?? $pythonApiUrl;
        // On récupère le chemin absolu du fichier app.py (il est dans le dossier python_api de la racine du projet)
        $this->pythonScriptPath = dirname(__DIR__, 2) . '/python_api/app.py';
    }

    /**
     * Vérifie si l'API Python est en ligne.
     * @phpstan-impure
     */
    public function isHealthy(): bool
    {
        try {
            $response = $this->httpClient->request('GET', $this->pythonApiUrl . '/health', [
                'timeout' => 2,
            ]);
            
            if ($response->getStatusCode() === 200) {
                return true;
            }
        } catch (TransportExceptionInterface $e) {
            // L'API ne répond pas du tout (Connection refused, etc.)
            return false;
        }

        return false;
    }

    /**
     * Démarre l'API Python de manière asynchrone (en arrière-plan).
     * @return bool True si la commande de lancement a été exécutée.
     */
    public function startServer(): bool
    {
        if ($this->isHealthy()) {
            $this->logger->info('API Python Face Recognition déjà en cours dexécution.');
            return true;
        }

        $this->logger->warning('API Python non joignable. Tentative de redémarrage automatique...');

        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $pythonVenvPath = dirname(__DIR__, 2) . '\\venv\\Scripts\\python.exe';
                $command = sprintf('start /B "" %s %s > NUL 2>&1', escapeshellarg($pythonVenvPath), escapeshellarg($this->pythonScriptPath));
                pclose(popen($command, 'r'));
            } else {
                $pythonVenvPath = dirname(__DIR__, 2) . '/venv/bin/python';
                $process = new Process([$pythonVenvPath, $this->pythonScriptPath]);
                $process->setTimeout(null);
                $process->start();
            }

            // Petite boucle d'attente car Flask et OpenCV peuvent prendre quelques secondes pour démarrer
            $maxWaitSeconds = 10;
            for ($i = 0; $i < $maxWaitSeconds; $i++) {
                sleep(1);
                if ($this->isHealthy()) {
                    $this->logger->info('API Python redémarrée avec succès en arrière-plan.');
                    return true;
                }
            }

            $this->logger->error('Impossible de confirmer le lancement de l\'API Python sur le port 5000 après ' . $maxWaitSeconds . ' secondes.');
            return false;

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du lancement de l\'API Python : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Assure que le serveur est démarré. A utiliser avant chaque endpoint majeur de Face Recognition.
     */
    public function ensureServerIsRunning(): bool
    {
        if (!$this->isHealthy()) {
            return $this->startServer();
        }
        
        return true;
    }
}