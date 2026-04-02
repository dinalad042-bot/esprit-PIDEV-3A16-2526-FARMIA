<?php
require_once 'vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$url = $_ENV['DATABASE_URL'];
$dbpts = parse_url($url);
$dbName = ltrim($dbpts['path'], '/');
$conn = new mysqli($dbpts['host'], $dbpts['user'], $dbpts['pass'], $dbName);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$schema = [];

foreach (['user', 'user_log'] as $table) {
    $res = $conn->query("DESCRIBE $table");
    if ($res) {
        while($row = $res->fetch_assoc()) {
            $schema[$table][] = $row;
        }
    } else {
        $schema[$table] = "Error: " . $conn->error;
    }
}

echo json_encode($schema, JSON_PRETTY_PRINT);
