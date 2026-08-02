<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartfarm";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

// Get the month parameter from the request
$month = isset($_GET['month']) ? $_GET['month'] : null;

$query = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i:%s') AS timestamp, temp, moist, ph, ec, n, p, k 
          FROM npksensoraverage";

if ($month) {
    $query .= " WHERE DATE_FORMAT(timestamp, '%Y-%m') = ?";
}

$query .= " ORDER BY timestamp ASC";

$stmt = $conn->prepare($query);

if ($month) {
    $stmt->bind_param("s", $month);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>