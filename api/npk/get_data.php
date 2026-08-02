<?php
header('Content-Type: application/json');
require_once '../../database.php';

$response = [
    'success' => false,
    'sensorData' => null,
    'status' => 'disconnected',
    'lastHeartbeat' => 'N/A',
    'message' => ''
];

try {
    // Fetch the latest sensor reading
    $stmt = $conn->prepare("SELECT temp, moist, ph, ec, n, p, k, timestamp FROM npksensor ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $response['success'] = true;
        
        $response['sensorData'] = [
            'temp' => floatval($row['temp']),
            'moist' => floatval($row['moist']),
            'ph' => floatval($row['ph']),
            'ec' => floatval($row['ec']),
            'n' => floatval($row['n']),
            'p' => floatval($row['p']),
            'k' => floatval($row['k']),
            'timestamp' => date("F j, Y h:i A", strtotime($row['timestamp']))
        ];
        
        // Check if data is fresh enough to be considered "ONLINE"
        $latestTs = strtotime($row['timestamp']);
        $age = time() - $latestTs;
        
        if ($age <= 60) { // 60 seconds threshold
            $response['status'] = 'connected';
            $response['lastHeartbeat'] = $response['sensorData']['timestamp'];
        } else {
            $response['status'] = 'disconnected';
            $response['lastHeartbeat'] = $response['sensorData']['timestamp'];
        }
    } else {
        $response['message'] = 'No sensor data found';
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
