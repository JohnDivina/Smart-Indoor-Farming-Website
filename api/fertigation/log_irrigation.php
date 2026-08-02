<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
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
$action = isset($input['action']) ? strtoupper($input['action']) : '';

if ($action !== 'START' && $action !== 'STOP') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO irrigation_log (action) VALUES (?)");
    $stmt->bind_param("s", $action);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => "Log inserted"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database insert failed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
