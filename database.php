<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartfarm";

date_default_timezone_set("Asia/Manila"); // Set the correct timezone

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
