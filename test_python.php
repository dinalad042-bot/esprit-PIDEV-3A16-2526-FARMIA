<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Kernel;

$dotenv = new Dotenv();
$dotenv->bootEnv(__DIR__.'/.env');

$_SERVER['APP_ENV'] = 'test'; 
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer()->get('test.service_container');

try {
    /** @var \App\Service\PythonFaceRecognitionService $service */
    $service = $container->get('App\Service\PythonFaceRecognitionService');
    
    echo "1. Etat initial de l'API: " . ($service->isHealthy() ? "ON" : "OFF") . "\n";
    
    echo "2. Lancement ensureServerIsRunning()...\n";
    $result = $service->ensureServerIsRunning();
    
    echo "3. Résultat du démarrage: " . ($result ? "SUCCES" : "ECHEC") . "\n";
    
    echo "4. Etat final API: " . ($service->isHealthy() ? "ON" : "OFF") . "\n";

} catch (\Exception $e) {
    echo "ERREUR LORS DU TEST: " . $e->getMessage() . "\n";
}
