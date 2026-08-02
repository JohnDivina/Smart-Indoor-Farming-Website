<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/esp32_auth.php';
verify_esp32_auth();
require_once __DIR__ . '/../database.php';

// Accept JSON payload from ESP32, fallback to $_POST for x-www-form-urlencoded
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

if (!$input && !empty($_POST)) {
    $input = $_POST;
}

$response = [
    'success' => false,
    'message' => 'Invalid input'
];

if (is_array($input)) {
    // 1. Presence checks avoiding truthy/falsy evaluation (resolving 0 issues)
    $mode = $input['mode'] ?? null;
    $motor_running = $input['motor_running'] ?? null;
    $desired_state = $input['desired_state'] ?? null;
    $actual_state = $input['actual_state'] ?? null;
    $pushed = $input['pushed'] ?? null;
    $pulled = $input['pulled'] ?? null;
    $direction = $input['direction'] ?? null;
    $last_message = $input['last_message'] ?? '';
    $wifi_status = $input['wifi_status'] ?? null;
    $ack_config_version = $input['ack_config_version'] ?? null;
    $config_version = $input['config_version'] ?? null;
    $open_time = $input['open_time'] ?? '';
    $fold_time = $input['fold_time'] ?? '';
    $reason = $input['reason'] ?? '';

    $valid = true;
    $errors = [];

    // 2. Strict Validation avoiding empty() on zero values
    if ($mode !== "manual" && $mode !== "scheduled") { $valid = false; $errors[] = "mode"; }
    
    if ($motor_running === null || !is_numeric($motor_running) || !in_array((int)$motor_running, [0, 1], true)) { $valid = false; $errors[] = "motor_running"; }
    if ($desired_state === null || !is_numeric($desired_state) || !in_array((int)$desired_state, [-1, 0, 1], true)) { $valid = false; $errors[] = "desired_state"; }
    if ($actual_state === null || !is_numeric($actual_state) || !in_array((int)$actual_state, [-1, 0, 1], true)) { $valid = false; $errors[] = "actual_state"; }
    
    if ($pushed === null || !is_numeric($pushed) || !in_array((int)$pushed, [0, 1], true)) { $valid = false; $errors[] = "pushed"; }
    if ($pulled === null || !is_numeric($pulled) || !in_array((int)$pulled, [0, 1], true)) { $valid = false; $errors[] = "pulled"; }
    
    if ($ack_config_version === null || !is_numeric($ack_config_version) || (int)$ack_config_version < 0) { $valid = false; $errors[] = "ack_config_version"; }
    if ($config_version === null || !is_numeric($config_version) || (int)$config_version < 0) { $valid = false; $errors[] = "config_version"; }

    if ($direction === null || trim((string)$direction) === "") { $valid = false; $errors[] = "direction"; }
    if ($wifi_status === null || trim((string)$wifi_status) === "") { $valid = false; $errors[] = "wifi_status"; }

    // 3. Execution (Only if all validations passed)
    if ($valid) {
        try {
            if ($reason !== '' && $last_message === '') {
                $last_message = $reason;
            }

            $safe_direction = $conn->real_escape_string((string)$direction);
            $safe_last_message = $conn->real_escape_string((string)$last_message);
            $safe_wifi_status = $conn->real_escape_string((string)$wifi_status);
            
            $safe_motor = (int)$motor_running;
            $safe_actual = (int)$actual_state;
            $safe_pushed = (int)$pushed;
            $safe_pulled = (int)$pulled;
            $safe_ack = (int)$ack_config_version;

            $stmt = $conn->prepare("UPDATE solar_panel_status SET 
                motor_running = ?, 
                actual_state = ?, 
                pushed = ?, 
                pulled = ?, 
                direction = ?, 
                last_message = ?, 
                wifi_status = ?, 
                ack_config_version = ?, 
                last_seen_at = NOW() 
                WHERE id = 1");
                
            $stmt->bind_param("iiiisssi", 
                $safe_motor, 
                $safe_actual, 
                $safe_pushed, 
                $safe_pulled, 
                $safe_direction, 
                $safe_last_message, 
                $safe_wifi_status, 
                $safe_ack
            );

            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'Status updated successfully'
                ];
            } else {
                $response['message'] = 'Database update failed';
            }

        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Invalid input on fields: ' . implode(', ', $errors);
    }
} else {
    $response['message'] = 'Invalid JSON: Could not parse input payload.';
}

echo json_encode($response);
$conn->close();
?>
