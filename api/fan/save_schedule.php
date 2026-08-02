<?php
header('Content-Type: application/json');
require_once '../../database.php';

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $enabled = isset($data['enabled']) && $data['enabled'] ? 1 : 0;
    $time = isset($data['time']) ? $data['time'] : null;
    
    if ($enabled && $time) {
        $stmt = $conn->prepare("UPDATE fan_state SET scheduled_mode_enabled = 1, scheduled_time = ? WHERE id = 1");
        $stmt->bind_param("s", $time);
    } else {
        $stmt = $conn->prepare("UPDATE fan_state SET scheduled_mode_enabled = ?, scheduled_time = ? WHERE id = 1");
        $stmt->bind_param("is", $enabled, $time);
    }
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Schedule updated";
    } else {
        throw new Exception("Database update failed");
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
