<?php
$c = new mysqli('127.0.0.1', 'root', '', 'farmai');
if ($c->connect_error) { die("Conn failed"); }

$out = "USER TABLE:\n";
$r = $c->query("DESCRIBE user");
while($row = $r->fetch_assoc()) {
    $out .= $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . "\n";
}

$out .= "\nUSER_LOG TABLE:\n";
$r = $c->query("DESCRIBE user_log");
while($row = $r->fetch_assoc()) {
    $out .= $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Key'] . " | " . $row['Default'] . "\n";
}

$out .= "\nSAMPLE USER:\n";
$r = $c->query("SELECT * FROM user LIMIT 1");
if ($r) {
    $row = $r->fetch_assoc();
    foreach ($row as $k => $v) {
        $out .= "$k => $v\n";
    }
}

file_put_contents('tmp_db_schema_details.txt', $out);
echo "Done";
