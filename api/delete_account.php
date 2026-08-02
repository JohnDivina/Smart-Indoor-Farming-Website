<?php
session_start();
header('Content-Type: application/json');

// Must be logged in and have a pending delete OTP
if (!isset($_SESSION['id']) || !isset($_SESSION['delete_otp_pending']) || $_SESSION['delete_otp_pending'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized request']);
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

// Extra safety: ensure session user matches the one who requested the OTP
if ($_SESSION['delete_otp_user_id'] !== $_SESSION['id']) {
    echo json_encode(['success' => false, 'message' => 'Session mismatch']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$otp   = trim($input['otp'] ?? '');

if (empty($otp) || !preg_match('/^\d{6}$/', $otp)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid 6-digit OTP']);
    exit();
}

include '../database.php'; // This file is expected to establish the $conn connection

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$userId = $_SESSION['id'];

// Fetch the stored OTP hash (must not be expired)
date_default_timezone_set('Asia/Manila');
$now  = date('Y-m-d H:i:s');
$stmt = $conn->prepare("SELECT otp_hash FROM login_otps WHERE user_id = ? AND expires_at > ?");
$stmt->bind_param("is", $userId, $now);
$stmt->execute();
$result = $stmt->get_result();
$row    = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
    exit();
}

if (!password_verify($otp, $row['otp_hash'])) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
    exit();
}

// OTP is valid — delete OTP record first
$del = $conn->prepare("DELETE FROM login_otps WHERE user_id = ?");
$del->bind_param("i", $userId);
$del->execute();
$del->close();

// Delete the user account
$delUser = $conn->prepare("DELETE FROM users WHERE id = ?");
$delUser->bind_param("i", $userId);

if ($delUser->execute()) {
    $delUser->close();
    $conn->close();

    // Destroy the session
    session_unset();
    session_destroy();

    echo json_encode(['success' => true, 'message' => 'Account deleted successfully.']);
} else {
    $delUser->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Failed to delete account. Please try again.']);
}
?>
