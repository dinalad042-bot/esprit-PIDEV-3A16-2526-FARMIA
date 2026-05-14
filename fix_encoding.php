<?php
// Read the file
$content = file_get_contents('templates/plante/index.html.twig');

// Remove UTF-16 BOM if present
if (substr($content, 0, 2) === "\xff\xfe" || substr($content, 0, 2) === "\xfe\xff") {
    echo "UTF-16 BOM detected - converting to UTF-8\n";
    // Try to convert from UTF-16 to UTF-8
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
} elseif (substr($content, 0, 3) === "\xef\xbb\xbf") {
    echo "UTF-8 BOM detected - removing\n";
    $content = substr($content, 3);
}

// Ensure content starts with {%
$content = ltrim($content);

// Save as UTF-8 without BOM
file_put_contents('templates/plante/index.html.twig', $content);
echo "Template encoding fixed!\n";
