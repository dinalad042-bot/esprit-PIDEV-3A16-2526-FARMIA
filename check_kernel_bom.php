<?php
$file = 'src/Kernel.php';
$content = file_get_contents($file);
$bytes = substr($content, 0, 3);
echo "First 3 bytes (hex): " . bin2hex($bytes) . "\n";
if ($bytes === "\xEF\xBB\xBF") {
    echo "BOM detected: YES (UTF-8 BOM)\n";
} else {
    echo "BOM detected: NO\n";
}
echo "File size: " . strlen($content) . " bytes\n";
