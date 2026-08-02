<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "smartfarm");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

// Get ESP32 reported state (GET or POST acceptable)
$esp_mode = $_REQUEST['esp_mode'] ?? 'manual';
$esp_pump_state = $_REQUEST['esp_pump_state'] ?? 'off';

// Validate inputs
if (!in_array($esp_mode, ['manual', 'scheduled'])) {
    $esp_mode = 'manual';
}
if (!in_array($esp_pump_state, ['on', 'off'])) {
    $esp_pump_state = 'off';
}

// Update ESP32 heartbeat and reported state
$stmt = $conn->prepare("
    UPDATE fertigation_control 
    SET last_heartbeat = NOW(),
        esp_pump_state = ?,
        esp_mode = ?
    WHERE id = 1
");

$stmt->bind_param("ss", $esp_pump_state, $esp_mode);
$stmt->execute();
$stmt->close();

// Fetch desired state from server (server is authority)
$result = $conn->query("SELECT * FROM fertigation_control WHERE id = 1");
$state = $result->fetch_assoc();

// Respond with what ESP32 should do
$response = [
    'success' => true,
    'desired_pump_state' => $state['desired_pump_state'],
    'mode' => $state['mode'],
    'schedule_time' => $state['schedule_time'],
    'schedule_duration_minutes' => (int)$state['schedule_duration_minutes'],
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response);

$conn->close();
?>
