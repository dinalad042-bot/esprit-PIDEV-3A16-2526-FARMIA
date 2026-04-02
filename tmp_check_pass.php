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

$res = $conn->query("SELECT email, password FROM user LIMIT 3");
while($row = $res->fetch_assoc()) {
    echo "USER: " . $row['email'] . "\n";
    echo "PASS: " . $row['password'] . "\n\n";
}
