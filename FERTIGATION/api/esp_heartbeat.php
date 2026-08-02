
// Update ESP32 heartbeat and reported state
$stmt = $conn->prepare("
    UPDATE fertigation_control 
    SET last_heartbeat = NOW(),
        esp_pump_state = ?,
        esp_mode = ?,
        ack_config_version = GREATEST(ack_config_version, ?)
    WHERE id = 1
");

$stmt->bind_param("ssi", $esp_pump_state, $esp_mode, $ack_config_version);
$stmt->execute();
$stmt->close();

// Run scheduler to update desired_pump_state based on schedule
require_once __DIR__ . '/check_schedule.php';

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
    'config_version' => (int)$state['config_version'],
    'ack_config_version' => (int)$state['ack_config_version'],
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response);
// Connection handled by shared database.php
?>
