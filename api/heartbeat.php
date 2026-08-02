<?php
/**
 * Heartbeat API Endpoint
 * 
 * Updates user's last activity and current page
 * Called every 15 seconds from frontend
 */

session_start();
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

include '../database.php';

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get user ID from session
$userId = $_SESSION['id'];

// Get current page from POST data
$input = json_decode(file_get_contents('php://input'), true);
$currentPage = isset($input['page']) ? $input['page'] : 'Unknown';

// Sanitize page name (remove special characters, limit length)
$currentPage = substr(preg_replace('/[^a-zA-Z0-9\s\-]/', '', $currentPage), 0, 100);

// Insert or update user activity
$sql = "INSERT INTO active_users (user_id, last_activity, current_page) 
        VALUES (?, NOW(), ?) 
        ON DUPLICATE KEY UPDATE 
        last_activity = NOW(), 
        current_page = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $userId, $currentPage, $currentPage);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to update activity']);
}

$stmt->close();
$conn->close();
