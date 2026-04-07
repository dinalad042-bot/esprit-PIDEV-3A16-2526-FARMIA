<?php
function check($url) {
    echo "--- CHECK $url ---\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    echo "HTTP Status Code: " . $info['http_code'] . "\n";
    if ($info['http_code'] != 200) {
        // Output first 200 chars of response for error
        echo "Error body snippet: " . substr(strip_tags($response), 0, 200) . "...\n";
    } else {
        echo "PAGE LOADED SUCCESSFULLY\n";
    }
}
check("http://localhost:8000/admin/analyse");
check("http://localhost:8000/admin/conseil");
