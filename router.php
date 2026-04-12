<?php
/**
 * PHP Built-in Server Router for Symfony
 * Usage: php -S localhost:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . '/public' . $uri;

// DEBUG: Log what we're checking
error_log("Router: Checking URI=$uri, FilePath=$filePath, is_file=" . (is_file($filePath) ? 'YES' : 'NO'));

// Serve static files directly (only actual files, not directories)
if ($uri !== '/' && is_file($filePath)) {
    error_log("Router: Serving static file directly");
    return false;
}

error_log("Router: Routing to Symfony");

// Bootstrap Symfony manually (bypassing Runtime)
require_once __DIR__ . '/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;

// Load environment
if (file_exists(__DIR__ . '/.env')) {
    (new Dotenv())->load(__DIR__ . '/.env');
}
if (file_exists(__DIR__ . '/.env.local')) {
    (new Dotenv())->load(__DIR__ . '/.env.local');
}

// Set required server variables
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] ?? 'dev';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? '1';

error_log("Router: Creating kernel with APP_ENV=" . $_SERVER['APP_ENV']);

// Create and handle request
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();

error_log("Router: Handling request...");

$response = $kernel->handle($request);

error_log("Router: Sending response...");

$response->send();
$kernel->terminate($request, $response);

error_log("Router: Done");
