<?php
require 'vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;
$dotenv = new Dotenv();
$dotenv->load('.env');
$dbUrl = $_ENV['DATABASE_URL'];
$parsed = parse_url($dbUrl);
$host = $parsed['host'];
$user = $parsed['user'] ?? 'root';
$pass = $parsed['pass'] ?? '';
$db = trim($parsed['path'], '/');
$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
$stmt = $pdo->query("SHOW CREATE TABLE user_log");
if ($stmt) {
    $row = $stmt->fetch();
    echo $row['Create Table'];
} else {
    echo "NO TABLE";
}
