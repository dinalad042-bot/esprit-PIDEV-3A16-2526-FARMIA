<?php
// Test if index.php would be reached
echo "Index test!<br>";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "<br>";
echo "Script name: " . ($_SERVER['SCRIPT_NAME'] ?? 'not set') . "<br>";
echo "Query string: " . ($_SERVER['QUERY_STRING'] ?? 'not set') . "<br>";

// Check if mod_rewrite is working
if (isset($_SERVER['HTTP_MOD_REWRITE'])) {
    echo "Mod_rewrite is ON<br>";
} else {
    echo "Mod_rewrite status unknown (may be on)<br>";
}

// Show all server vars
// echo "<pre>"; print_r($_SERVER); echo "</pre>";
?>