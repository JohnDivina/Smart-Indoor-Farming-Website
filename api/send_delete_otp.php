<?php
session_start();
header('Content-Type: application/json');

// Must be logged in
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
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

include '../database.php';

$userId = $_SESSION['id'];

// Fetch user email and username
$stmt = $conn->prepare("SELECT email, username FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    $conn->close();
    exit();
}

// Reuse existing OTP email function
require_once __DIR__ . '/../send_otp_email.php';

$res = sendOTPEmail($userId, $user['email'], $user['username'], $conn);

if ($res['success']) {
    // Mark the purpose of this OTP in the session so it can't be reused for login
    $_SESSION['delete_otp_pending'] = true;
    $_SESSION['delete_otp_user_id'] = $userId;
}

$conn->close();
echo json_encode($res);
?>
