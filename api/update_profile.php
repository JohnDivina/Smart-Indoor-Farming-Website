<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION["id"])) {
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

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$userId = $_SESSION["id"];

// Verify password for all actions except password change (which has its own verification)
if ($action !== 'update_password') {
    $password = $input['password'] ?? '';
    
    // Get current password hash
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user || (!password_verify($password, $user['password']) && md5($password) !== $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect password']);
        exit();
    }
}

switch ($action) {
    case 'update_username':
        $newUsername = trim($input['username'] ?? '');
        
        if (empty($newUsername)) {
            echo json_encode(['success' => false, 'message' => 'Username cannot be empty']);
            exit();
        }
        
        // Check if username already exists
        $checkSql = "SELECT id FROM users WHERE username = ? AND id != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $newUsername, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();
        
        // Update username
        $updateSql = "UPDATE users SET username = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $newUsername, $userId);
        
        if ($updateStmt->execute()) {
            $_SESSION['username'] = $newUsername;
            echo json_encode(['success' => true, 'message' => 'Username updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update username']);
        }
        $updateStmt->close();
        break;
        
    case 'update_email':
        $newEmail = trim($input['email'] ?? '');
        
        if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            exit();
        }
        
        // Check if email already exists
        $checkSql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $newEmail, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();
        
        // Update email and set email_verified to 0 (requires re-verification)
        $updateSql = "UPDATE users SET email = ?, email_verified = 0 WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $newEmail, $userId);
        
        if ($updateStmt->execute()) {
            $_SESSION['email'] = $newEmail;
            $_SESSION['email_verified'] = 0;
            echo json_encode(['success' => true, 'message' => 'Email updated successfully. Please verify your new email.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update email']);
        }
        $updateStmt->close();
        break;
        
    case 'update_phone':
        $newPhone = trim($input['phone'] ?? '');
        
        if (empty($newPhone) || !preg_match('/^\d+$/', $newPhone)) {
            echo json_encode(['success' => false, 'message' => 'Invalid phone number (digits only)']);
            exit();
        }
        
        // Check if phone already exists
        $checkSql = "SELECT id FROM users WHERE phonenumber = ? AND id != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $newPhone, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Phone number already exists']);
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();
        
        // Update phone number
        $updateSql = "UPDATE users SET phonenumber = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $newPhone, $userId);
        
        if ($updateStmt->execute()) {
            $_SESSION['phonenumber'] = $newPhone;
            echo json_encode(['success' => true, 'message' => 'Phone number updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update phone number']);
        }
        $updateStmt->close();
        break;
        
    case 'update_password':
        $currentPassword = $input['currentPassword'] ?? '';
        $newPassword = $input['newPassword'] ?? '';
        
        // Verify current password
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if (!$user || (!password_verify($currentPassword, $user['password']) && md5($currentPassword) !== $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit();
        }
        
        // Validate new password
        if (strlen($newPassword) < 6 || !preg_match('/[0-9!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters and contain at least one number or special symbol']);
            exit();
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateSql = "UPDATE users SET password = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $hashedPassword, $userId);
        
        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password']);
        }
        $updateStmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

$conn->close();
?>
