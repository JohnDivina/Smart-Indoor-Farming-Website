<?php
session_start();

// Set timezone to match database and OTP generation
date_default_timezone_set('Asia/Manila');

// Check if user came from account creation
if (!isset($_SESSION['otp_user_id']) || !isset($_SESSION['otp_reason']) || $_SESSION['otp_reason'] !== 'account_creation') {
    header("Location: createaccount.php");
    exit();
}

include 'database.php';

$error_message = "";
$success_message = "";
$userId = $_SESSION['otp_user_id'];
$userName = $_SESSION['otp_username'];

// Handle OTP verification
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_otp'])) {
        $enteredOTP = trim($_POST["otp"]);
        
        // Validate OTP format (6 digits)
        if (!preg_match('/^\d{6}$/', $enteredOTP)) {
            $error_message = "Invalid verification code format.";
        } else {
            // Get OTP record from database
            $sql = "SELECT * FROM login_otps WHERE user_id = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $otpRow = $result->fetch_assoc();
                $otpHash = $otpRow['otp_hash'];
                $attempts = $otpRow['attempts'];
                $otpId = $otpRow['id'];
                
                // Check if too many attempts
                if ($attempts >= 3) {
                    $error_message = "Too many attempts. Please request a new verification code.";
                    
                    // Delete the OTP
                    $deleteStmt = $conn->prepare("DELETE FROM login_otps WHERE id = ?");
                    $deleteStmt->bind_param("i", $otpId);
                    $deleteStmt->execute();
                    $deleteStmt->close();
                } else {
                    // Verify OTP
                    if (password_verify($enteredOTP, $otpHash)) {
                        // OTP is correct - Activate account
                        
                        // Update email_verified to 1
                        $updateSql = "UPDATE users SET email_verified = 1, last_login = NOW() WHERE id = ?";
                        $updateStmt = $conn->prepare($updateSql);
                        $updateStmt->bind_param("i", $userId);
                        $updateStmt->execute();
                        $updateStmt->close();
                        
                        // Delete the OTP from database
                        $deleteStmt = $conn->prepare("DELETE FROM login_otps WHERE id = ?");
                        $deleteStmt->bind_param("i", $otpId);
                        $deleteStmt->execute();
                        $deleteStmt->close();
                        
                        // Clear OTP session variables
                        unset($_SESSION['otp_user_id']);
                        unset($_SESSION['otp_username']);
                        unset($_SESSION['otp_reason']);
                        
                        // Set success message and redirect to login
                        $_SESSION['account_verified'] = true;
                        header("Location: login.php");
                        exit();
                    } else {
                        // Incorrect OTP - Increment attempts
                        $updateStmt = $conn->prepare("UPDATE login_otps SET attempts = attempts + 1 WHERE id = ?");
                        $updateStmt->bind_param("i", $otpId);
                        $updateStmt->execute();
                        $updateStmt->close();
                        
                        $remainingAttempts = 3 - ($attempts + 1);
                        if ($remainingAttempts > 0) {
                            $error_message = "Invalid verification code. $remainingAttempts attempt(s) remaining.";
                        } else {
                            $error_message = "Invalid verification code. No attempts remaining.";
                        }
                    }
                }
            } else {
                $error_message = "Invalid or expired verification code.";
            }
            
            $stmt->close();
        }
    } elseif (isset($_POST['resend_otp'])) {
        // Resend OTP
        require_once 'send_otp_email.php';
        
        // Get user email
        $userSql = "SELECT email FROM users WHERE id = ?";
        $userStmt = $conn->prepare($userSql);
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userRow = $userResult->fetch_assoc();
        $userEmail = $userRow['email'];
        $userStmt->close();
        
        $otpResult = sendOTPEmail($userId, $userEmail, $userName, $conn);
        
        if ($otpResult['success']) {
            $success_message = "A new verification code has been sent to your email.";
        } else {
            $error_message = "Failed to send verification code. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CLSU CRRDC | EMAIL VERIFICATION</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="assets/clsu-official-logo.png">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --green-grad: linear-gradient(120deg, #009639, #87b237);
    }

    body, html {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: 'Libre Franklin', sans-serif;
      background-color: #f0f2f5;
    }

    .main-container {
      height: 100vh;
    }

    .left-panel {
      background: var(--green-grad);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .left-panel img {
      width: 80%;
      max-width: 380px;
      height: auto;
    }

    .right-panel {
      background-color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .verify-box {
      width: 100%;
      max-width: 700px;
      padding: 2rem;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .verify-box img {
      max-width: 160px;
      margin-bottom: 1rem;
    }

    .verify-title {
      font-size: 28px;
      font-weight: 600;
      color: #333;
      margin-bottom: 1rem;
    }

    .verify-subtitle {
      font-size: 16px;
      color: #666;
      margin-bottom: 2rem;
    }

    .form-label {
      font-weight: 500;
      text-align: left;
      display: block;
    }

    .form-control {
      border-radius: 8px;
      padding: 0.75rem;
    }

    .otp-input {
      font-size: 24px;
      letter-spacing: 8px;
      text-align: center;
      font-weight: 600;
    }

    .btn-verify {
      background-color:#009639;
      border: none;
      font-weight: 600;
      color: #ffffff;
      padding: 0.75rem;
      border-radius: 8px;
      width: 100%;
      margin-top: 1rem;
    }

    .btn-verify:hover {
      background-color: #007a2f;
      color: #ffffff;
    }

    .btn-resend {
      background-color: transparent;
      border: 2px solid #009639;
      font-weight: 600;
      color: #009639;
      padding: 0.75rem;
      border-radius: 8px;
      width: 100%;
      margin-top: 0.5rem;
    }

    .btn-resend:hover {
      background-color: #f0f9f4;
      color: #007a2f;
      border-color: #007a2f;
    }

    .back-link {
      text-align: center;
      font-size: 0.875rem;
      margin-top: 1rem;
      color: #6c757d;
    }

    .back-link a {
      text-decoration: none;
      font-weight: 600;
      color: #009639;
    }

    .back-link a:hover {
      text-decoration: underline;
      color: #006622;
    }

    @media (max-width: 768px) {
      .left-panel {
        display: none !important;
      }

      .verify-box {
        box-shadow: 0 12px 25px rgba(33, 106, 30, 0.577);
        padding: 1.5rem;
        height: auto;
      }

      .verify-title {
        margin-top: -.5rem;
        font-size: 24px;
      }

      .right-panel {
        padding: 2rem 1rem;
      }
    }

    .info-box {
      background: #e3f2fd;
      border-left: 4px solid #2196f3;
      padding: 1rem;
      margin-bottom: 1.5rem;
      border-radius: 4px;
      text-align: left;
    }

    .info-box p {
      margin: 0;
      font-size: 14px;
      color: #333;
    }

    .info-box strong {
      color: #1976d2;
    }
  </style>
</head>
<body>

  <div class="container-fluid main-container">
    <div class="row h-100">

      <!-- Left Logo Panel (only on md+ screens) -->
      <div class="col-md-6 left-panel d-none d-md-flex">
        <img src="assets/clsu-official-logo.png" alt="CLSU Logo">
      </div>

      <!-- Verification Form Panel -->
      <div class="col-md-6 right-panel">
        <div class="verify-box">
          <!-- Show logo on small screens only -->
          <img src="assets/clsu-official-logo.png" alt="CLSU Logo" class="d-md-none mb-3" style="max-width: 200px;">
        
          <div class="verify-title">Activate Your Account</div>
          <div class="verify-subtitle">
            Welcome <strong><?php echo htmlspecialchars($userName); ?></strong>!<br>
            Please verify your email address to activate your account.
          </div>

          <div class="info-box">
            <p><strong>📧 Check your email</strong></p>
            <p>We've sent a 6-digit verification code to your registered email.</p>
            <p>The code will expire in 5 minutes.</p>
          </div>

          <!-- Display error message -->
          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
              <?php echo htmlspecialchars($error_message); ?>
            </div>
          <?php endif; ?>

          <!-- Display success message -->
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
              <?php echo htmlspecialchars($success_message); ?>
            </div>
          <?php endif; ?>
        
          <form method="POST" action="">
            <div class="mb-3 text-start">
              <label for="otp" class="form-label">Enter Verification Code</label>
              <input 
                type="text" 
                class="form-control otp-input" 
                id="otp" 
                name="otp" 
                placeholder="000000" 
                maxlength="6"
                pattern="\d{6}"
                required
                autocomplete="off"
              >
            </div>
            <button type="submit" name="verify_otp" class="btn btn-verify">ACTIVATE ACCOUNT</button>
          </form>

          <form method="POST" action="" style="margin-top: 0.5rem;">
            <button type="submit" name="resend_otp" class="btn btn-resend">RESEND CODE</button>
          </form>

          <div class="back-link mt-4">
            <a href="createaccount.php">← Back to Sign Up</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Auto-focus OTP input
    document.getElementById('otp').focus();
    
    // Only allow digits in OTP input
    document.getElementById('otp').addEventListener('input', function(e) {
      this.value = this.value.replace(/\D/g, '');
    });
  </script>

</body>
</html>
