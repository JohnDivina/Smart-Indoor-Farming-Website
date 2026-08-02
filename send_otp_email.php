<?php
/**
 * Send OTP Email Function
 * Generates a 6-digit OTP, stores it hashed in the database, and sends it via email
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

function sendOTPEmail($userId, $userEmail, $username, $conn) {
    // Set timezone to match database
    date_default_timezone_set('Asia/Manila');
    
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(0, 999999));
    
    // Hash the OTP for secure storage
    $otpHash = password_hash($otp, PASSWORD_BCRYPT);
    
    // Set expiration time (5 minutes from now)
    $currentTime = new DateTime();
    $expiresAt = $currentTime->add(new DateInterval('PT5M'))->format('Y-m-d H:i:s');
    
    // Debug logging
    error_log("OTP Generation - Current Time: " . date('Y-m-d H:i:s'));
    error_log("OTP Generation - Expires At: " . $expiresAt);
    error_log("OTP Generation - Generated OTP: " . $otp);
    
    // Delete any existing OTPs for this user
    $deleteStmt = $conn->prepare("DELETE FROM login_otps WHERE user_id = ?");
    if ($deleteStmt === false) {
        error_log("Failed to prepare DELETE statement: " . $conn->error);
        return ['success' => false, 'message' => 'Database error. Please ensure the login_otps table exists.'];
    }
    $deleteStmt->bind_param("i", $userId);
    if (!$deleteStmt->execute()) {
        $error = $deleteStmt->error;
        $deleteStmt->close();
        return ['success' => false, 'message' => 'Database error (DELETE): ' . $error];
    }
    $deleteStmt->close();
    
    // Insert new OTP
    $insertStmt = $conn->prepare("INSERT INTO login_otps (user_id, otp_hash, expires_at) VALUES (?, ?, ?)");
    if ($insertStmt === false) {
        error_log("Failed to prepare INSERT statement: " . $conn->error);
        return ['success' => false, 'message' => 'Database error. Please run setup_otp_database.php first.'];
    }
    $insertStmt->bind_param("iss", $userId, $otpHash, $expiresAt);
    
    if (!$insertStmt->execute()) {
        $error = $insertStmt->error;
        $insertStmt->close();
        return ['success' => false, 'message' => 'Failed to generate verification code. DB Error: ' . $error];
    }
    $insertStmt->close();
    
    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration for Google Workspace (CLSU email)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Google Workspace uses same SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'johnrey_divina@clsu.edu.ph';
        $mail->Password   = 'opwv icgp jzyo wred'; // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Debug mode for localhost (REMOVE IN PRODUCTION)
        $mail->SMTPDebug = 0; // Set to 0 for production
        
        // Localhost SSL fix (ONLY for development - REMOVE IN PRODUCTION)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom('noreply@smartfarm.com', 'CLSU Smart Farm');
        $mail->addAddress($userEmail, $username);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Smart Farm Login Verification Code';
        
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(120deg, #009639, #87b237); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .otp-code { font-size: 32px; font-weight: bold; color: #009639; text-align: center; letter-spacing: 8px; padding: 20px; background: white; border-radius: 8px; margin: 20px 0; }
                .warning { color: #d32f2f; font-size: 14px; margin-top: 20px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Smart Farm Login Verification</h2>
                </div>
                <div class='content'>
                    <p>Hello <strong>" . htmlspecialchars($username) . "</strong>,</p>
                    <p>Your verification code is:</p>
                    <div class='otp-code'>" . $otp . "</div>
                    <p><strong>This code will expire in 5 minutes.</strong></p>
                    <p>If you did not request this code, please ignore this email and ensure your account is secure.</p>
                    <div class='warning'>
                        ⚠️ Never share this code with anyone. CLSU Smart Farm staff will never ask for your verification code.
                    </div>
                </div>
                <div class='footer'>
                    <p>© 2026 CLSU Smart Farm. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Hello $username,\n\nYour verification code is: $otp\n\nThis code will expire in 5 minutes.\n\nIf you did not request this code, please ignore this email.\n\nBest regards,\nCLSU Smart Farm Team";
        
        $mail->send();
        return ['success' => true, 'message' => 'Verification code sent to your email'];
        
    } catch (Exception $e) {
        $errorMsg = "Email sending failed: " . $mail->ErrorInfo . " | Exception: " . $e->getMessage();
        error_log($errorMsg);
        
        // Return more detailed error for debugging (remove in production)
        return [
            'success' => false, 
            'message' => 'Failed to send verification code. Error: ' . $mail->ErrorInfo,
            'debug' => isset($debugLog) ? $debugLog : "No debug log available"
        ];
    }
}
?>
