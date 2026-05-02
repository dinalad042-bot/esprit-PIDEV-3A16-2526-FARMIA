<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=farmai', 'root', '');
    
    // Get ferme table structure
    $columns = $pdo->query('DESCRIBE ferme')->fetchAll(PDO::FETCH_ASSOC);
    echo 'Ferme table columns:' . PHP_EOL;
    foreach ($columns as $col) {
        echo '  - ' . $col['Field'] . PHP_EOL;
    }
    
    // Try to find TEST_INTEGRATION
    $stmt = $pdo->query("SELECT * FROM ferme WHERE nom_ferme LIKE '%TEST%' OR nom_ferme = 'TEST_INTEGRATION'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($results) > 0) {
        echo PHP_EOL . 'YES - TEST_INTEGRATION found in database' . PHP_EOL;
        print_r($results);
    } else {
        echo PHP_EOL . 'NO - TEST_INTEGRATION not found in database' . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
?>
