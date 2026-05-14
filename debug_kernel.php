<?php
$content = file_get_contents('src/Kernel.php');
echo "First 200 chars:\n";
echo substr($content, 0, 200);
echo "\n---\n";
echo "File length: " . strlen($content) . "\n";
echo "First 10 bytes hex: " . bin2hex(substr($content, 0, 10)) . "\n";
echo "Class defined: " . (class_exists('App\Kernel') ? 'YES' : 'NO') . "\n";
require_once 'src/Kernel.php';
echo "After require - Class defined: " . (class_exists('App\Kernel') ? 'YES' : 'NO') . "\n";
