<?php
header("Content-Type: application/json");

// Database connection
include '../database.php';

// Get notifications from database
$sql = "SELECT type, title, message, timestamp FROM system_notifications ORDER BY timestamp DESC LIMIT 50";
$result = $conn->query($sql);

$notifications = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            "type" => $row['type'],
            "title" => $row['title'],
            "message" => $row['message'],
            "timestamp" => $row['timestamp'],
            "formatted_time" => date("F j, Y, g:i A", strtotime($row['timestamp']))
        ];
    }
}

echo json_encode([
    "success" => true,
    "notifications" => $notifications,
    "count" => count($notifications)
]);

$conn->close();
?>
