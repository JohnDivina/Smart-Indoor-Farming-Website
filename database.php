<?php
date_default_timezone_set("Asia/Manila");

// Supports both local (XAMPP) and production (Railway/any host)
// On Railway, these environment variables are set automatically
// On localhost, falls back to your XAMPP defaults
$servername = getenv('MYSQLHOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
$username   = getenv('MYSQLUSER') ?: (getenv('MYSQL_USER') ?: 'root');
$password   = getenv('MYSQLPASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: '');
$dbname     = getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: 'smartfarm');
$port       = (int)(getenv('MYSQLPORT') ?: (getenv('MYSQL_PORT') ?: 3306));

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
