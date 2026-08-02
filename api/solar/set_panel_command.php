<?php
session_start();
header('Content-Type: application/json');

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

require_once __DIR__ . '/../../database.php';

$input = json_decode(file_get_contents('php://input'), true);

$response = [
    'success' => false,
    'message' => 'Invalid input'
];

if ($input && isset($input['desired_state'])) {
    $desired_state = (int)$input['desired_state'];
    
    // Only 1 (open) or 0 (fold) is allowed
    if ($desired_state !== 1 && $desired_state !== 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid state']);
        exit();
    }

    try {
        // Force manual mode, queue the requested state, and bump the version
        // so the ESP32 can acknowledge this command just like fertigation.
        $stmt = $conn->prepare("UPDATE solar_panel_control SET 
            mode = 'manual',
            desired_state = ?,
            config_version = config_version + 1
            WHERE id = 1");
            
        $stmt->bind_param("i", $desired_state);

        if ($stmt->execute()) {
            $versionResult = $conn->query("SELECT config_version FROM solar_panel_control WHERE id = 1");
            $versionRow = $versionResult ? $versionResult->fetch_assoc() : null;
            $response = [
                'success' => true,
                'message' => 'Command queued. Controller will apply it within a few seconds.',
                'config_version' => $versionRow ? (int)$versionRow['config_version'] : null
            ];
        } else {
            $response['message'] = 'Database update failed';
        }

    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
$conn->close();
?>
