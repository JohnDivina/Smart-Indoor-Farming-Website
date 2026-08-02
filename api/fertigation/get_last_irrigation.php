<?php
header('Content-Type: application/json');
require_once '../../database.php';

try {
    $stmt = $conn->prepare("SELECT timestamp FROM irrigation_log WHERE action = 'START' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $last_run = new DateTime($row['timestamp']);
        $now = new DateTime();
        $interval = $now->diff($last_run);
        
        $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
        
        echo json_encode([
            'success' => true, 
            'minutes_ago' => $minutes,
            'timestamp' => $row['timestamp']
        ]);
    } else {
        echo json_encode(['success' => true, 'minutes_ago' => null]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
