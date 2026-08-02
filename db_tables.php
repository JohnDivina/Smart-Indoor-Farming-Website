<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartfarm";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "Tables in $dbname:\n";
    while ($row = $result->fetch_array()) {
        $table = $row[0];
        $countResult = $conn->query("SELECT COUNT(*) FROM `$table` ");
        $count = $countResult->fetch_array()[0];
        echo "- $table ($count records)\n";
    }
} else {
    echo "Zero tables found.\n";
}
$conn->close();
?>
