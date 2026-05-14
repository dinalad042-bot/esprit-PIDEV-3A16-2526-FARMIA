<?php
$file = 'src/Kernel.php';
$content = file_get_contents($file);

// Remove BOM if present
if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
    $content = substr($content, 3);
    file_put_contents($file, $content);
    echo "BOM removed from Kernel.php\n";
} else {
    echo "No BOM found in Kernel.php\n";
}
