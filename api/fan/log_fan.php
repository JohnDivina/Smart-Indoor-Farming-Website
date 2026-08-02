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

$response = ['success' => false, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['action'])) {
        throw new Exception('Action not provided');
    }
    
    $action = strtoupper($data['action']); // START or STOP
    
    $stmt = $conn->prepare("INSERT INTO fan_log (action) VALUES (?)");
    $stmt->bind_param("s", $action);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Event logged";
    } else {
        throw new Exception("Database insert failed");
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
