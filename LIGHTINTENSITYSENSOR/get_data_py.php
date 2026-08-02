<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smartfarm";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Query to get the latest row
$sql = "SELECT hourlyAverage, timestamp FROM lightintensitysensor ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($sql);

if (!$result) {
    die(json_encode(['error' => 'Query failed: ' . $conn->error]));
}

// Check sensor status
$statusUrl = "http://localhost/smartfarm/LIGHTINTENSITYSENSOR/LightIntensitySensor.php";
$statusData = @file_get_contents($statusUrl);
if ($statusData === false) {
    $status = "disconnected";
    $lastHeartbeat = "N/A";
} else {
    $decodedData = json_decode($statusData, true);
    $status = $decodedData['status'] ?? "disconnected";
    $lastHeartbeat = $decodedData['lastHeartbeat'] ?? "N/A";
}

// Prepare data
$data = [
    'status' => $status,
    'hourlyAverage' => null,
    'timestamp' => null
];

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $data['hourlyAverage'] = $row["hourlyAverage"];
    $data['timestamp'] = date("F j, Y h:i A", strtotime($row["timestamp"]));
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data);
?>