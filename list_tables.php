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
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $tables);
