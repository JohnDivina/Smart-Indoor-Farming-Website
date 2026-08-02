<?php
/**
 * Get Active Users API Endpoint
 * 
 * Returns list of users active within last 30 seconds
 * Generates initials server-side
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

$currentUserId = $_SESSION['id'];

// Get users active within last 30 seconds (excluding current user)
$sql = "SELECT u.id, u.username, au.current_page 
        FROM active_users au 
        JOIN users u ON au.user_id = u.id 
        WHERE au.last_activity >= DATE_SUB(NOW(), INTERVAL 30 SECOND)
        AND au.user_id != ?
        ORDER BY u.username ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

$activeUsers = [];

while ($row = $result->fetch_assoc()) {
    // Generate initials from username
    $username = $row['username'];
    $initials = generateInitials($username);
    
    $activeUsers[] = [
        'name' => $username,
        'initials' => $initials,
        'page' => $row['current_page'] ?? 'Unknown'
    ];
}

echo json_encode($activeUsers);

$stmt->close();
$conn->close();

/**
 * Generate initials from username
 * Takes first letter of first word and first letter of last word
 */
function generateInitials($name) {
    $name = trim($name);
    $words = preg_split('/\s+/', $name);
    
    if (count($words) == 0) {
        return '??';
    } elseif (count($words) == 1) {
        // Single word: take first two letters
        return strtoupper(substr($name, 0, 2));
    } else {
        // Multiple words: first letter of first and last word
        $first = substr($words[0], 0, 1);
        $last = substr($words[count($words) - 1], 0, 1);
        return strtoupper($first . $last);
    }
}
