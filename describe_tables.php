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
function describe($pdo, $table) {
    echo "--- DESCRIBE $table ---\n";
    $stmt = $pdo->query("DESCRIBE $table");
    if ($stmt) {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']}\n";
        }
    } else {
        echo "NO TABLE\n";
    }
}
describe($pdo, 'analyse');
describe($pdo, 'conseil');
