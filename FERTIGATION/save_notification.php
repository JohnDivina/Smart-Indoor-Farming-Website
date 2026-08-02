<?php
header("Content-Type: application/json");

// Database connection
include '../database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['type']) && isset($input['title']) && isset($input['message'])) {
    $type = $conn->real_escape_string($input['type']);
    $title = $conn->real_escape_string($input['title']);
    $message = $conn->real_escape_string($input['message']);
    $timestamp = date('Y-m-d H:i:s');
    
    // Insert notification into database
    $sql = "INSERT INTO system_notifications (type, title, message, timestamp) VALUES ('$type', '$title', '$message', '$timestamp')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true, "message" => "Notification saved successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error saving notification: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing type, title or message"]);
}

$conn->close();
?>
