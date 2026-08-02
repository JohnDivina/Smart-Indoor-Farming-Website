<?php
header('Content-Type: application/json');

include '../database.php';

// Get the most recent irrigation START event
$sql = "SELECT timestamp FROM irrigation_log 
        WHERE action = 'START' 
        ORDER BY timestamp DESC 
        LIMIT 1";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $timestamp = strtotime($row['timestamp']);
    $now = time();
    $diff = $now - $timestamp;
    
    // Ensure diff is not negative
    if ($diff < 0) {
        $diff = 0;
    }
    
    // Calculate time ago - MINIMUM unit is minutes
    if ($diff < 60) {
        $timeAgo = '< 1 min ago';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        $timeAgo = $minutes . ' min ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        $timeAgo = $hours . ' hr ago';
    } else {
        $days = floor($diff / 86400);
        $timeAgo = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
    
    echo json_encode([
        'success' => true,
        'timestamp' => $row['timestamp'],
        'timeAgo' => $timeAgo,
        'secondsAgo' => $diff
    ]);
} else {
    echo json_encode([
        'success' => false,
        'timeAgo' => 'Never',
        'secondsAgo' => 0
    ]);
}

$conn->close();
?>
