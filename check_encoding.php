<?php
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('templates', RecursiveDirectoryIterator::SKIP_DOTS)
);

$utf16Count = 0;
$totalCount = 0;

foreach ($files as $file) {
    if ($file->getExtension() !== 'twig') continue;
    $totalCount++;
    
    $content = file_get_contents($file->getPathname());
    $first3 = substr($content, 0, 3);
    
    // UTF-16 LE BOM: FF FE
    // UTF-16 BE BOM: FE FF
    if ($first3 === "\xff\xfe" || $first3 === "\xfe\xff" || 
        substr($content, 0, 2) === "\xff\xfe" || substr($content, 0, 2) === "\xfe\xff") {
        echo "UTF-16: " . $file->getPathname() . "\n";
        $utf16Count++;
    }
}

echo "\nTotal: $totalCount files\n";
echo "UTF-16 BOM: $utf16Count files\n";
