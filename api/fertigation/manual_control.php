<?php
/*
 * api/fertigation/manual_control.php
 * Writes manual command to DB. The ESP32 picks it up on its next poll.
 * No curl to ESP32. Config version is bumped so firmware detects the change.
 */
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
if (!empty($_SESSION['is_guest'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Action restricted in Guest mode. Please log in with an account.']);
    exit();
}
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
    exit();
}
require_once '../../database.php';

$input  = json_decode(file_get_contents('php://input'), true);
$action = isset($input['action']) ? strtoupper($input['action']) : '';

if ($action !== 'START' && $action !== 'STOP') {
    echo json_encode(['success' => false, 'message' => 'Invalid action. Use START or STOP.']);
    exit;
}

$desired_state = ($action === 'START') ? 'on' : 'off';

try {
    // Switch to manual mode + record intent + bump config_version
    $stmt = $conn->prepare(
        "UPDATE fertigation_control
         SET desired_pump_state = ?,
             mode               = 'manual',
             config_version     = config_version + 1
         WHERE id = 1"
    );
    $stmt->bind_param("s", $desired_state);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => "Command queued: pump $desired_state. Controller will apply within a few seconds."
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
