<?php
// Script to fix all Twig templates with UTF-16 BOM encoding

$templatesDir = 'templates';
$fixed = [];
$checked = 0;

// Recursive function to find all .twig files
function findTwigFiles($dir, &$files) {
    $items = glob($dir . '/*');
    foreach ($items as $item) {
        if (is_dir($item)) {
            findTwigFiles($item, $files);
        } elseif (is_file($item) && str_ends_with($item, '.twig')) {
            $files[] = $item;
        }
    }
}

$files = [];
findTwigFiles($templatesDir, $files);

echo "Found " . count($files) . " Twig template files\n";
echo str_repeat("=", 60) . "\n";

foreach ($files as $file) {
    $checked++;
    $content = file_get_contents($file);
    $first3 = substr($content, 0, 3);
    
    // Check for UTF-16 BOM (FF FE or FE FF)
    if ($first3 === "\xff\xfe" || $first3 === "\xfe\xff" || substr($content, 0, 2) === "\xff\xfe" || substr($content, 0, 2) === "\xfe\xff") {
        echo "Fixing: $file\n";
        echo "  - Detected UTF-16 BOM\n";
        
        // Convert from UTF-16 to UTF-8
        $newContent = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
        $newContent = ltrim($newContent);
        
        // Backup original
        rename($file, $file . '.backup');
        
        // Save fixed version
        file_put_contents($file, $newContent);
        
        $fixed[] = $file;
        echo "  - Converted to UTF-8 and saved\n\n";
    }
}

echo str_repeat("=", 60) . "\n";
echo "Checked: $checked files\n";
echo "Fixed: " . count($fixed) . " files\n";

if (count($fixed) > 0) {
    echo "\nFixed files:\n";
    foreach ($fixed as $f) {
        echo "  - $f\n";
    }
} else {
    echo "\nNo UTF-16 encoded files found (all templates are UTF-8)\n";
}
