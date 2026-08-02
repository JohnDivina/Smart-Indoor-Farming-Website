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

if ($input && isset($input['open_time']) && isset($input['fold_time'])) {
    
    // Basic validation of HH:MM format
    if (!preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $input['open_time']) || 
        !preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $input['fold_time'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid time format']);
        exit();
    }

    try {
        $open_time = $input['open_time'] . ':00';
        $fold_time = $input['fold_time'] . ':00';
        
        $stmt = $conn->prepare("UPDATE solar_panel_control SET 
            mode = 'scheduled',
            open_time = ?, 
            fold_time = ?, 
            config_version = config_version + 1 
            WHERE id = 1");
            
        $stmt->bind_param("ss", $open_time, $fold_time);

        if ($stmt->execute()) {
            // Get the new config version to return it
            $res = $conn->query("SELECT config_version FROM solar_panel_control WHERE id = 1");
            $row = $res->fetch_assoc();
            
            $response = [
                'success' => true,
                'message' => 'Schedule saved',
                'config_version' => (int)$row['config_version']
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
