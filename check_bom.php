<?php
$f = file_get_contents('templates/plante/index.html.twig', false, null, 0, 3);
echo 'First 3 bytes (hex): ' . bin2hex($f) . PHP_EOL;
echo 'BOM detected: ' . (bin2hex($f) === 'efbbbf' ? 'YES (UTF-8 BOM)' : 'NO') . PHP_EOL;
