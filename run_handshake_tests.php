#!/usr/bin/env php
<?php

/**
 * FarmAI Expert Module Handshake Test Runner
 * 
 * This script runs the staging/pre-production tests for the Expert Module
 * to validate all button→action→response handshakes without browser automation.
 */

echo "🧪 FarmAI Expert Module Handshake Test Runner\n";
echo "============================================\n\n";

// Check if we're in the right directory
if (!file_exists('bin/console')) {
    echo "❌ Error: Please run this script from the project root directory\n";
    echo "   Current directory: " . getcwd() . "\n";
    exit(1);
}

// Parse command line arguments
$options = getopt('h', ['help', 'coverage', 'verbose', 'filter:', 'list']);

if (isset($options['h']) || isset($options['help'])) {
    echo "Usage: php run_handshake_tests.php [options]\n\n";
    echo "Options:\n";
    echo "  --coverage    Generate code coverage report\n";
    echo "  --verbose     Show detailed test output\n";
    echo "  --filter=STR  Run only tests matching the filter string\n";
    echo "  --list        List all available handshake tests\n";
    echo "  -h, --help    Show this help message\n\n";
    echo "Examples:\n";
    echo "  php run_handshake_tests.php                    # Run all handshake tests\n";
    echo "  php run_handshake_tests.php --coverage         # Run with coverage report\n";
    echo "  php run_handshake_tests.php --filter=AI        # Run only AI-related tests\n";
    exit(0);
}

// List available tests
if (isset($options['list'])) {
    echo "📋 Available Handshake Tests:\n";
    echo "=============================\n\n";
    
    $tests = [
        'ExpertModuleHandshakeTest' => [
            'testExpertAnalysesListHandshake' => 'Expert analyses list rendering and data display',
            'testCreateAnalysisHandshake' => 'Create new analysis form and submission',
            'testAnalysisStatusUpdateHandshake' => 'Update analysis status via button click',
            'testTakeRequestHandshake' => 'Expert taking pending request',
            'testAIDiagnosisHandshake' => 'AI diagnosis trigger and result storage',
            'testConseilCreationHandshake' => 'Add conseil to analysis',
            'testExpertConseilsListHandshake' => 'Expert conseils list with filtering',
            'testSecurityAccessControlHandshake' => 'Security access control validation',
            'testPdfExportHandshake' => 'PDF export functionality'
        ],
        'ExpertAIConnectionTest' => [
            'testAIDiagnosisCompleteHandshake' => 'Complete AI diagnosis flow',
            'testAIResultDisplayHandshake' => 'Display stored AI diagnosis results',
            'testAIDiagnosisWithoutImageHandshake' => 'AI diagnosis without image (error handling)',
            'testAIAPIEndpointHandshake' => 'Direct API call to AI diagnosis',
            'testUnauthorizedAIAccessHandshake' => 'Unauthorized access to AI features'
        ]
    ];
    
    foreach ($tests as $class => $methods) {
        echo "📁 $class:\n";
        foreach ($methods as $method => $description) {
            echo "   • $method - $description\n";
        }
        echo "\n";
    }
    exit(0);
}

// Build PHPUnit command
$phpunitCmd = 'php bin/phpunit tests/Staging/';
$args = [];

if (isset($options['coverage'])) {
    echo "📊 Generating code coverage report...\n";
    $args[] = '--coverage-html coverage/';
}

if (isset($options['verbose'])) {
    echo "🔍 Verbose mode enabled\n";
    $args[] = '-v';
}

if (isset($options['filter'])) {
    $filter = $options['filter'];
    echo "🔍 Filtering tests: $filter\n";
    $args[] = '--filter=' . escapeshellarg($filter);
}

// Add default configuration
$args[] = '--configuration=phpunit.xml.dist';

// Build final command
if (!empty($args)) {
    $phpunitCmd .= ' ' . implode(' ', $args);
}

echo "🚀 Running Expert Module Handshake Tests...\n";
echo "Command: $phpunitCmd\n\n";

// Execute tests
passthru($phpunitCmd, $returnCode);

// Handle results
if ($returnCode === 0) {
    echo "\n✅ All handshake tests passed!\n";
    echo "📋 Expert module connections validated successfully.\n";
    
    if (isset($options['coverage'])) {
        echo "📊 Coverage report generated in 'coverage/' directory\n";
        echo "   Open coverage/index.html in your browser to view\n";
    }
    
    echo "\n🎯 Summary of validated handshakes:\n";
    echo "   • Expert analyses list and management\n";
    echo "   • Analysis creation and editing\n";
    echo "   • Status updates and request assignment\n";
    echo "   • AI diagnosis integration\n";
    echo "   • Conseil management\n";
    echo "   • Security access control\n";
    echo "   • PDF export functionality\n";
    
} else {
    echo "\n❌ Some handshake tests failed!\n";
    echo "🔧 Please check the test output above for details.\n";
    echo "💡 Common issues:\n";
    echo "   • Database connection problems\n";
    echo "   • Missing test data or fixtures\n";
    echo "   • AI service configuration issues\n";
    echo "   • Route or controller changes\n";
}

exit($returnCode);