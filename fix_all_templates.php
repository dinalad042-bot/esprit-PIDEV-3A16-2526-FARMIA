<?php
// Fix all Twig templates with UTF-16 BOM
$templatesDir = 'templates';
$fixed = 0;
$checked = 0;

function fixTemplate($filepath) {
    global $fixed, $checked;
    $checked++;
    
    $content = file_get_contents($filepath);
    $first3 = substr($content, 0, 3);
    $first2 = substr($content, 0, 2);
    
    // Check for UTF-16 LE BOM (ff fe)
    if ($first2 === "\xff\xfe") {
        echo "UTF-16 LE BOM found: $filepath\n";
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
        file_put_contents($filepath, $content);
        $fixed++;
        return true;
    }
    // Check for UTF-16 BE BOM (fe ff)
    if ($first2 === "\xfe\xff") {
        echo "UTF-16 BE BOM found: $filepath\n";
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16BE');
        file_put_contents($filepath, $content);
        $fixed++;
        return true;
    }
    // Check for UTF-8 BOM (ef bb bf)
    if ($first3 === "\xef\xbb\xbf") {
        echo "UTF-8 BOM found: $filepath\n";
        $content = substr($content, 3);
        file_put_contents($filepath, $content);
        $fixed++;
        return true;
    }
    
    return false;
}

function scanDirectory($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            scanDirectory($path);
        } elseif (str_ends_with($file, '.twig')) {
            fixTemplate($path);
        }
    }
}

scanDirectory($templatesDir);
echo "\nScanned: $checked templates\n";
echo "Fixed: $fixed templates\n";
