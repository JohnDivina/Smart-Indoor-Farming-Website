<?php
/*
 * api/fertigation/set_mode.php
 * Switches fertigation mode in DB. Config version bumped so ESP32 picks up the change.
 * Modes: manual | scheduled | sensor_auto
 *
 * No curl to ESP32. The ESP32 polls get_fert_control.php and acts on the new mode.
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

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['mode'])) {
    echo json_encode(['success' => false, 'message' => 'Missing mode']);
    exit;
}

$allowed = ['manual', 'scheduled', 'sensor_auto'];
$mode    = in_array($input['mode'], $allowed, true) ? $input['mode'] : 'manual';

try {
    // (Schedule validation removed to allow frontend UX flow; ESP32 firmware handles safety)

    $stmt = $conn->prepare(
        "UPDATE fertigation_control
         SET mode = ?,
             config_version = config_version + 1
         WHERE id = 1"
    );
    $stmt->bind_param("s", $mode);

    if ($stmt->execute()) {
        $labels = [
            'manual'      => 'Manual',
            'scheduled'   => 'Scheduled',
            'sensor_auto' => 'Sensor Auto'
        ];
        echo json_encode([
            'success' => true,
            'message' => 'Mode switched to ' . $labels[$mode] . '. Controller will apply within a few seconds.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
