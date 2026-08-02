<?php
/*
 * Manual Control + Mode Switch — Auxiliary Blower
 * Called by browser frontend for manual ON/OFF and mode changes.
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
include '../../database.php';

$data   = json_decode(file_get_contents('php://input'), true);
$action = strtolower(trim($data['action'] ?? ''));  // start / stop / set_mode
$mode   = strtolower(trim($data['mode']   ?? ''));  // manual / scheduled / auto (for set_mode)

$response = ['success' => false, 'message' => ''];

try {
    if ($action === 'set_mode') {
        // Switch mode without touching desired_fan_state
        if (!in_array($mode, ['manual', 'scheduled', 'auto'])) {
            throw new Exception("Invalid mode. Use manual, scheduled, or auto.");
        }
        $stmt = $conn->prepare("UPDATE fan_state SET mode = ?, last_updated = NOW() WHERE id = 1");
        $stmt->bind_param('s', $mode);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => "Mode set to $mode", 'mode' => $mode];
        } else {
            throw new Exception("Database update failed");
        }

    } elseif ($action === 'start' || $action === 'on') {
        // Manual start: set desired_fan_state = 'on', revert to manual mode
        $desired = 'on';
        $new_mode = 'manual';
        $stmt = $conn->prepare(
            "UPDATE fan_state SET desired_fan_state = ?, mode = ?, last_updated = NOW() WHERE id = 1"
        );
        $stmt->bind_param('ss', $desired, $new_mode);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Fan command sent: ON'];
        } else {
            throw new Exception("Database update failed");
        }

    } elseif ($action === 'stop' || $action === 'off') {
        // Manual stop: set desired_fan_state = 'off', revert to manual mode
        $desired = 'off';
        $new_mode = 'manual';
        $stmt = $conn->prepare(
            "UPDATE fan_state SET desired_fan_state = ?, mode = ?, last_updated = NOW() WHERE id = 1"
        );
        $stmt->bind_param('ss', $desired, $new_mode);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Fan command sent: OFF'];
        } else {
            throw new Exception("Database update failed");
        }

    } else {
        throw new Exception("Invalid action. Use start, stop, or set_mode.");
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

if (isset($stmt)) $stmt->close();
echo json_encode($response);
$conn->close();
?>
