<?php
header('Content-Type: application/json');
require_once '../../database.php';

$response = [
    'success' => false,
    'temperature' => 0.0,
    'humidity' => 0.0,
    'timestamp' => '--',
    'status' => 'disconnected'
];

try {
    $stmt = $conn->prepare("SELECT temperature, humidity, timestamp FROM temphumiditysensor ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $response['success'] = true;
        $response['temperature'] = floatval($row['temperature']);
        $response['humidity'] = floatval($row['humidity']);
        $response['timestamp'] = date("F j, Y h:i A", strtotime($row['timestamp']));
        
        // Status logic
        $latestTs = strtotime($row['timestamp']);
        $age = time() - $latestTs;
        if ($age <= 60) { // 60 seconds
            $response['status'] = 'connected';
        } else {
            $response['status'] = 'disconnected';
        }
    }
} catch (Exception $e) { }

echo json_encode($response);
$conn->close();
?>
