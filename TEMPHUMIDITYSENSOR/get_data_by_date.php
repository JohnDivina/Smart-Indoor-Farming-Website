<?php
include '../database.php';

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$date = $_GET['date'] ?? null;

if ($date) {
    $sql = "SELECT temperature, humidity, timestamp FROM temphumiditysensor WHERE DATE(timestamp) = ? ORDER BY timestamp ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo json_encode(["error" => "No date provided"]);
}

$conn->close();
?> 