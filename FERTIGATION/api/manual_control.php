<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "smartfarm");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$action = strtolower($data['action'] ?? '');

if (!in_array($action, ['start', 'stop'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    $conn->close();
    exit();
}

/* Map action → desired pump state */
$desired_state = ($action === 'start') ? 'on' : 'off';

/* Update controller table */
$stmt = $conn->prepare("
    UPDATE fertigation_control
    SET desired_pump_state = ?,
        last_updated = NOW()
    WHERE id = 1
");

$stmt->bind_param("s", $desired_state);

if ($stmt->execute()) {

    /* Log action */
    $log_stmt = $conn->prepare("
        INSERT INTO irrigation_log (action, timestamp)
        VALUES (?, NOW())
    ");
    $log_action = strtoupper($action);
    $log_stmt->bind_param("s", $log_action);
    $log_stmt->execute();
    $log_stmt->close();

    echo json_encode([
        'success' => true,
        'message' => "Pump $action command saved",
        'desired_pump_state' => $desired_state
    ]);

} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update desired state'
    ]);
}

$stmt->close();
$conn->close();
?>
