<?php
/**
 * PHP Built-in Server Router for Symfony 6.4
 * Place this file IN the public/ directory
 * 
 * Usage: cd public && php -S localhost:8000 router.php
 * OR from project root: php -S localhost:8000 -t public public/router.php
 */

// Get the requested URI path
$uri = $_SERVER['REQUEST_URI'];
$uriPath = parse_url($uri, PHP_URL_PATH) ?: $uri;

// Check if it's a file that should be served directly (assets, images, css, js)
if ($uriPath !== '/' && !str_starts_with($uriPath, '/_') && !str_starts_with($uriPath, '/index.php')) {
    $filePath = __DIR__ . $uriPath;
    // Normalize path for Windows
    $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);
    
    if (file_exists($filePath) && is_file($filePath)) {
        // Determine content type
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
        ];
        
        if (isset($contentTypes[$ext])) {
            header('Content-Type: ' . $contentTypes[$ext]);
        }
        
        readfile($filePath);
        return;
    }
}

// For all other requests, route through Symfony's index.php
require __DIR__ . '/index.php';
