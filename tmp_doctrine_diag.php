<?php
require_once 'vendor/autoload.php';
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use App\Kernel;

$kernel = new Kernel('dev', true);
$kernel->boot();

$em = $kernel->getContainer()->get('doctrine')->getManager();
$schemaManager = $em->getConnection()->createSchemaManager();

$dbColumns = $schemaManager->listTableColumns('user');
$dbColNames = array_keys($dbColumns);

$metadata = $em->getClassMetadata('App\Entity\User');
$entityColNames = $metadata->getColumnNames();

echo "Database Columns in 'user' table:\n";
print_r($dbColNames);

echo "\nEntity Mapped Columns in App\\Entity\\User:\n";
print_r($entityColNames);

echo "\nMissing in Entity:\n";
print_r(array_diff($dbColNames, $entityColNames));

echo "\nMissing in Database (Entity expects these but DB doesn't have them):\n";
print_r(array_diff($entityColNames, $dbColNames));

