<?php
header('Content-Type: application/json');

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing action parameter']);
    exit();
}

$action = strtoupper($data['action']);

if (!in_array($action, ['START', 'STOP'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Connect to database
include '../database.php';

// Insert irrigation log
$sql = "INSERT INTO irrigation_log (action, timestamp) VALUES (?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $action);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Irrigation event logged',
        'action' => $action,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to log event',
        'error' => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
