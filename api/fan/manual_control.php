<?php
header('Content-Type: application/json');
require_once '../../database.php';

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['action'])) {
        throw new Exception('Action not provided');
    }
    
    $action = strtoupper($data['action']); // START or STOP
    $desired_state = ($action === 'START') ? 'ON' : 'OFF';

    // Disable schedule mode when manually controlling
    $stmt = $conn->prepare("UPDATE fan_state SET desired_fan_state = ?, scheduled_mode_enabled = 0 WHERE id = 1");
    $stmt->bind_param("s", $desired_state);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Fan set to $desired_state";
    } else {
        throw new Exception("Database update failed");
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
