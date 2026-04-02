<?php
require_once 'vendor/autoload.php';
use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$authService = $container->get('App\Service\AuthService');

try {
    $user = $authService->signup([
        'nom' => 'Test',
        'prenom' => 'Test',
        'email' => 'test_'.rand(100,999).'@test.com',
        'password' => '123456',
        'telephone' => '12345678',
        'adresse' => 'Test address',
        'role' => 'ROLE_USER',
        'cin' => rand(10000000, 99999999)
    ]);
    echo "Signup success! ID: " . $user->getId();
} catch (\Exception $e) {
    echo "Signup failed: " . get_class($e) . "\nMessage: " . $e->getMessage();
}
