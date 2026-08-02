<?php
header('Content-Type: application/json');
require_once '../../database.php';

$response = [
    'success' => false,
    'lux' => 0.0,
    'timestamp' => '--',
    'status' => 'disconnected'
];

try {
    $stmt = $conn->prepare("SELECT hourlyAverage, timestamp FROM lightintensitysensor ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check live readings for true timestamp status
    $stmtLive = $conn->prepare("SELECT timestamp FROM live_light_readings ORDER BY timestamp DESC LIMIT 1");
    $stmtLive->execute();
    $resLive = $stmtLive->get_result();
    $liveTs = null;
    if ($rowLive = $resLive->fetch_assoc()) {
        $liveTs = strtotime($rowLive['timestamp']);
    }

    if ($row = $result->fetch_assoc()) {
        $response['success'] = true;
        $response['lux'] = floatval($row['hourlyAverage']);
        $response['timestamp'] = date("F j, Y h:i A", strtotime($row['timestamp']));
        
        $age = time() - ($liveTs ? $liveTs : strtotime($row['timestamp']));
        if ($age <= 60) { 
            $response['status'] = 'connected';
        } else {
            $response['status'] = 'disconnected';
        }
    }
} catch (Exception $e) { }

echo json_encode($response);
$conn->close();
?>
