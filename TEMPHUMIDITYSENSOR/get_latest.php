<?php
include '../database.php';

$sql = "SELECT temperature, humidity, timestamp FROM temphumiditysensor ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($sql);

$data = ['temperature' => null, 'humidity' => null, 'timestamp' => null];
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $data = [
        'temperature' => $row['temperature'],
        'humidity' => $row['humidity'],
        'timestamp' => $row['timestamp']
    ];
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($data);
?> 