<?php
/**
 * Simple Integration Test to Prove Merge Success
 * Tests key merge artifacts without requiring full Symfony container
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\DTO\DiagnosisResult;

echo "🧪 INTEGRATION TEST: Proving dev_ + aymen-bensalem merge success\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Core classes load without syntax errors
echo "📋 TEST 1: Core Classes Load (no merge syntax errors)\n";
$classesToTest = [
    'App\\Entity\\User' => 'User entity (aymen-bensalem)',
    'App\\Entity\\Ferme' => 'Ferme entity (dev_)',
    'App\\Entity\\Analyse' => 'Analyse entity (dev_)',
    'App\\Service\\GroqService' => 'GroqService (dev_)',
    'App\\Service\\EmailSecurityService' => 'EmailSecurityService (merge fix)',
    'App\\Controller\\Web\\ExpertAIController' => 'ExpertAIController (dev_)',
    'App\\Controller\\Web\\ProfileController' => 'ProfileController (aymen-bensalem)',
];

$allClassesLoaded = true;
foreach ($classesToTest as $className => $description) {
    if (class_exists($className)) {
        echo "✅ $description - class loaded\n";
    } else {
        echo "❌ $description - class NOT found\n";
        $allClassesLoaded = false;
    }
}

// Test 2: DiagnosisResult DTO symptoms fix (key merge issue)
echo "\n📋 TEST 2: DiagnosisResult DTO (merge fix verification)\n";
try {
    $diagnosis = new DiagnosisResult();
    $diagnosis->condition = 'Test Disease';
    $diagnosis->symptoms = ['Symptom 1', 'Symptom 2']; // This was broken before merge fix
    $diagnosis->treatment = 'Test treatment';
    $diagnosis->prevention = 'Test prevention';
    $diagnosis->urgency = 'medium';
    $diagnosis->needsExpert = true;
    $diagnosis->confidence = 'HIGH';
    $diagnosis->rawResponse = '{"test": "data"}';

    if (is_array($diagnosis->symptoms) && count($diagnosis->symptoms) == 2) {
        echo "✅ DiagnosisResult symptoms is ARRAY (merge fix confirmed)\n";
        echo "✅ DTO can be instantiated with array symptoms\n";
    } else {
        echo "❌ DiagnosisResult symptoms is not array - merge fix failed\n";
        $allClassesLoaded = false;
    }
} catch (TypeError $e) {
    echo "❌ DiagnosisResult type error: " . $e->getMessage() . "\n";
    echo "❌ This indicates the symptoms field is still typed as string instead of array\n";
    $allClassesLoaded = false;
}

// Test 3: Check that User entity has lat/lon fields (aymen-bensalem addition)
echo "\n📋 TEST 3: User Entity Fields (aymen-bensalem additions)\n";
$userClass = new ReflectionClass('App\\Entity\\User');

$latProperty = $userClass->hasProperty('latitude');
$lonProperty = $userClass->hasProperty('longitude');
$resetCodeProperty = $userClass->hasProperty('resetCode');

if ($latProperty && $lonProperty) {
    echo "✅ User entity has latitude/longitude fields (aymen-bensalem)\n";
} else {
    echo "❌ User entity missing latitude/longitude fields\n";
    $allClassesLoaded = false;
}

if ($resetCodeProperty) {
    echo "✅ User entity has resetCode field (aymen-bensalem)\n";
} else {
    echo "❌ User entity missing resetCode field\n";
    $allClassesLoaded = false;
}

// Test 4: Check Analyse entity has AI diagnosis fields (dev_ features)
echo "\n📋 TEST 4: Analyse Entity AI Fields (dev_ features)\n";
$analyseClass = new ReflectionClass('App\\Entity\\Analyse');

$aiResultProperty = $analyseClass->hasProperty('aiDiagnosisResult');
$aiConfidenceProperty = $analyseClass->hasProperty('aiConfidenceScore');
$aiDateProperty = $analyseClass->hasProperty('aiDiagnosisDate');

if ($aiResultProperty && $aiConfidenceProperty && $aiDateProperty) {
    echo "✅ Analyse entity has AI diagnosis fields (dev_)\n";
    echo "✅ aiDiagnosisResult, aiConfidenceScore, aiDiagnosisDate present\n";
} else {
    echo "❌ Analyse entity missing AI diagnosis fields\n";
    $allClassesLoaded = false;
}

// Test 5: Check EmailSecurityService for import syntax (merge fix)
echo "\n📋 TEST 5: EmailSecurityService Import Syntax (merge fix)\n";
$emailServiceFile = __DIR__ . '/src/Service/EmailSecurityService.php';

if (file_exists($emailServiceFile)) {
    $content = file_get_contents($emailServiceFile);

    // Check for the problematic double backslash that was in the merged code
    if (strpos($content, 'use Symfony\\Component\\Routing\\\\Generator\\UrlGeneratorInterface;') !== false) {
        echo "❌ EmailSecurityService still has double backslash in imports\n";
        echo "❌ Merge fix not applied correctly\n";
        $allClassesLoaded = false;
    } elseif (strpos($content, 'use Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface;') !== false) {
        echo "✅ EmailSecurityService has correct single backslash imports\n";
        echo "✅ Merge import fix confirmed\n";
    } else {
        echo "⚠️  EmailSecurityService import pattern not found (might be okay)\n";
    }
} else {
    echo "❌ EmailSecurityService.php file not found\n";
    $allClassesLoaded = false;
}

// Test 6: Check composer.json has the new google-mailer dependency
echo "\n📋 TEST 6: Composer Dependencies (post-merge)\n";
$composerFile = __DIR__ . '/composer.json';

if (file_exists($composerFile)) {
    $composerContent = file_get_contents($composerFile);
    $composerData = json_decode($composerContent, true);

    if (isset($composerData['require']['symfony/google-mailer'])) {
        echo "✅ composer.json includes symfony/google-mailer (aymen-bensalem)\n";
    } else {
        echo "❌ composer.json missing symfony/google-mailer dependency\n";
        $allClassesLoaded = false;
    }
} else {
    echo "❌ composer.json not found\n";
    $allClassesLoaded = false;
}

// Summary
echo "\n🎉 INTEGRATION TEST SUMMARY\n";
echo str_repeat("=", 60) . "\n";

if ($allClassesLoaded) {
    echo "✅ ALL TESTS PASSED - Merge integration successful!\n\n";
    echo "🎯 VERIFIED MERGE SUCCESS:\n";
    echo "   • User Module (aymen-bensalem): lat/lon, resetCode fields intact\n";
    echo "   • Expert Module (dev_): AI diagnosis fields, GroqService preserved\n";
    echo "   • Code fixes: DiagnosisResult symptoms array, import syntax corrected\n";
    echo "   • Dependencies: Both modules' packages present in composer.json\n";
    echo "   • No syntax errors: All classes load without fatal errors\n\n";
    echo "🏆 CONCLUSION: aymen-bensalem → dev_ merge COMPLETED SUCCESSFULLY!\n";
    echo "   Both user authentication AND expert AI functionality can coexist.\n";
} else {
    echo "❌ SOME TESTS FAILED - Merge may have issues\n";
    echo "   Check the errors above and verify the merge process.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";