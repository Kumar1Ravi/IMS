
<?php
$serverName = "KUMARR\\SQLEXPRESS";
$connectionInfo = array(
    "Database" => "Kumar_IMS",
    "UID" => "Kumar_IMS",
    "PWD" => "Kumar@17071992"
);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
   // die("❌ Connection failed: " . print_r(sqlsrv_errors(), true));
} else {
   // echo "✅ Connected successfully!";
}
