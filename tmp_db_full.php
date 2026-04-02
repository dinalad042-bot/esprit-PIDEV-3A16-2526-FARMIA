<?php
require_once 'vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$url = $_ENV['DATABASE_URL'];
$dbpts = parse_url($url);
$dbName = ltrim($dbpts['path'], '/');
$conn = new mysqli($dbpts['host'], $dbpts['user'], $dbpts['pass'], $dbName);

if ($conn->connect_error) { die("Conn failed"); }

$fp = fopen('tmp_db_full_diagnostics.csv', 'w');

fputcsv($fp, ['--- TABLE user ---']);
$res = $conn->query("DESCRIBE user");
while($row = $res->fetch_assoc()) { fputcsv($fp, $row); }

fputcsv($fp, ['--- SAMPLE user ---']);
$res = $conn->query("SELECT * FROM user LIMIT 1");
if ($res) {
    $row = $res->fetch_assoc();
    fputcsv($fp, array_keys($row));
    fputcsv($fp, array_values($row));
}

fputcsv($fp, ['--- TABLE user_log ---']);
$res = $conn->query("DESCRIBE user_log");
while($row = $res->fetch_assoc()) { fputcsv($fp, $row); }

fclose($fp);
echo "Done";
