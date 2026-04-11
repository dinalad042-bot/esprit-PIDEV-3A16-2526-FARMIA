<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Kernel;

$dotenv = new Dotenv();
$dotenv->bootEnv(__DIR__.'/.env');

echo "========== DIAGNOSTIC OPENAI ===========\n";
echo "1. Variable .env chargée: " . (isset($_ENV['OPENAI_API_KEY']) ? "OUI" : "NON") . "\n";
echo "2. Longueur de la clé: " . strlen($_ENV['OPENAI_API_KEY'] ?? '') . " caractères\n";

$_SERVER['APP_ENV'] = 'test'; // Use test env to access private services
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer()->get('test.service_container');

try {
    $service = $container->get('App\Service\OpenAIChatService');
    echo "3. Service instancié: OUI\n";
    
    // Check injected key via reflection
    $ref = new ReflectionClass($service);
    $prop = $ref->getProperty('openAiKey');
    $prop->setAccessible(true);
    $injectedKey = $prop->getValue($service);
    
    echo "4. Clé injectée dans le service: " . ($injectedKey ? "OUI" : "NON (Vide)") . "\n";
    echo "   Longueur de la clé injectée: " . strlen($injectedKey ?? '') . " caractères\n";
    
    echo "5. TENTATIVE D'APPEL A OPENAI...\n";
    $result = $service->generateResponse("Dis 'Bonjour, système opérationnel' en 4 mots max.");
    
    echo "---------------- RESULTAT ----------------\n";
    echo $result . "\n";
    echo "------------------------------------------\n";

} catch (\Exception $e) {
    echo "ERREUR LORS DU TEST: " . $e->getMessage() . "\n";
}
