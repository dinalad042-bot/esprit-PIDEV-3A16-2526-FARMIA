<?php
// FARMIA Integration Verification Script
require __DIR__ . '/vendor/autoload.php';

$results = [];
$errors = [];

echo "=== FARMIA INTEGRATION VERIFICATION ===\n\n";

// Test 1: Check all entities exist
$entities = [
    'App\Entity\User',
    'App\Entity\UserFace',
    'App\Entity\UserLog',
    'App\Entity\Ferme',
    'App\Entity\Animal',
    'App\Entity\Plante',
    'App\Entity\Analyse',
    'App\Entity\Conseil',
    'App\Enum\Priorite',
];

echo "1. ENTITY EXISTENCE CHECK:\n";
foreach ($entities as $entity) {
    if (class_exists($entity)) {
        echo "   [OK] $entity\n";
        $results[] = "Entity $entity: OK";
    } else {
        echo "   [MISSING] $entity\n";
        $errors[] = "Entity $entity: MISSING";
    }
}

// Test 2: Check repositories
$repositories = [
    'App\Repository\UserRepository',
    'App\Repository\FermeRepository',
    'App\Repository\AnimalRepository',
    'App\Repository\PlanteRepository',
    'App\Repository\AnalyseRepository',
    'App\Repository\ConseilRepository',
];

echo "\n2. REPOSITORY EXISTENCE CHECK:\n";
foreach ($repositories as $repo) {
    if (class_exists($repo)) {
        echo "   [OK] $repo\n";
        $results[] = "Repository $repo: OK";
    } else {
        echo "   [MISSING] $repo\n";
        $errors[] = "Repository $repo: MISSING";
    }
}

// Test 3: Check User has required methods
$userMethods = [
    'getUserFaces',
    'hasFaceAuth',
    'getActiveFace',
    'getAnalyses',
    'getFermes',
    'addFerme',
    'removeFerme',
];

echo "\n3. USER ENTITY METHODS CHECK:\n";
$userReflection = new ReflectionClass('App\Entity\User');
foreach ($userMethods as $method) {
    if ($userReflection->hasMethod($method)) {
        echo "   [OK] $method\n";
        $results[] = "User::$method: OK";
    } else {
        echo "   [MISSING] $method\n";
        $errors[] = "User::$method: MISSING";
    }
}

// Test 4: Check Ferme has required relations
$fermeMethods = [
    'getAnimals',
    'getPlantes',
    'getAnalyses',
    'addAnimal',
    'addPlante',
];

echo "\n4. FERME ENTITY RELATIONS CHECK:\n";
$fermeReflection = new ReflectionClass('App\Entity\Ferme');
foreach ($fermeMethods as $method) {
    if ($fermeReflection->hasMethod($method)) {
        echo "   [OK] $method\n";
        $results[] = "Ferme::$method: OK";
    } else {
        echo "   [MISSING] $method\n";
        $errors[] = "Ferme::$method: MISSING";
    }
}

// Test 5: Check for remove methods in Ferme (might be missing)
echo "\n5. FERME REMOVE METHODS CHECK:\n";
$removeMethods = ['removeAnimal', 'removePlante'];
foreach ($removeMethods as $method) {
    if ($fermeReflection->hasMethod($method)) {
        echo "   [OK] $method\n";
        $results[] = "Ferme::$method: OK";
    } else {
        echo "   [MISSING - OPTIONAL] $method\n";
        $results[] = "Ferme::$method: OPTIONAL (not found)";
    }
}

// Summary
echo "\n=== VERIFICATION SUMMARY ===\n";
echo "Passed: " . count($results) . "\n";
echo "Failed: " . count($errors) . "\n";

if (count($errors) > 0) {
    echo "\nERRORS:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    exit(1);
} else {
    echo "\nALL CHECKS PASSED\n";
    exit(0);
}
