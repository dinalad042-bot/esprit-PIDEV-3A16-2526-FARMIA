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
    die("Connection failed: " . $conn->connect_error);
}

echo "--- TABLE user ---\n";
$res = $conn->query("DESCRIBE user");
while($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n--- TABLE user_log ---\n";
$res = $conn->query("DESCRIBE user_log");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
