<?php
session_start();

include 'database.php';

$error_message = "";
$success_message = "";

// Check if account was just verified
if (isset($_SESSION['account_verified']) && $_SESSION['account_verified'] === true) {
    $success_message = "✅ Email verified successfully! You can now log in to your account.";
    unset($_SESSION['account_verified']);
}

// Check if password was just reset
if (isset($_SESSION['password_reset_success']) && $_SESSION['password_reset_success'] === true) {
    $success_message = "✅ Password reset successfully! You can now log in with your new password.";
    unset($_SESSION['password_reset_success']);
}

// Check for Guest Login request
if (isset($_GET['guest']) || (isset($_POST['guest_login']) && $_POST['guest_login'] == '1')) {
    session_regenerate_id(true);
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    $_SESSION["id"] = "guest";
    $_SESSION["is_guest"] = true;
    $_SESSION["username"] = "Guest User";
    $_SESSION["email"] = "guest@clsu.edu.ph";
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST["identifier"]);
    $password = $_POST["password"];
    
    // Detect identifier type
    $identifierType = '';
    $column = '';
    
    if (strpos($identifier, '@') !== false) {
        // Email
        $identifierType = 'email';
        $column = 'email';
    } elseif (preg_match('/^\d+$/', $identifier)) {
        // Phone number (all digits)
        $identifierType = 'phonenumber';
        $column = 'phonenumber';
    } else {
        // Username
        $identifierType = 'username';
        $column = 'username';
    }
    
    // Query user by detected identifier type
    $sql = "SELECT * FROM users WHERE $column = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password = $row["password"];
        
        // Verify password: Check BCRYPT first, fallback to MD5 for legacy users
        $password_correct = false;
        $needs_upgrade = false;
        
        if (password_verify($password, $stored_password)) {
            $password_correct = true;
        } elseif (md5($password) === $stored_password) {
            // Legacy MD5 support
            $password_correct = true;
            $needs_upgrade = true;
        }
        
        if ($password_correct) {
            $userId = $row["id"];
            
            // Upgrade legacy MD5 passwords to BCRYPT
            if ($needs_upgrade) {
                $new_hash = password_hash($password, PASSWORD_BCRYPT);
                $update_pw_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_pw_stmt->bind_param("si", $new_hash, $userId);
                $update_pw_stmt->execute();
                $update_pw_stmt->close();
            }

            // Password correct - Check if OTP is needed
            $userEmail = $row["email"];
            $userName = $row["username"];
            $emailVerified = $row["email_verified"];
            $lastLogin = $row["last_login"];
            
            $requireOTP = false;
            $otpReason = "";
            
            // Check if email is not verified
            if ($emailVerified == 0 || $emailVerified === null) {
                $requireOTP = true;
                $otpReason = "email_verification";
            }
            // Check if user has been inactive for 2+ days
            else if ($lastLogin !== null) {
                $lastLoginDate = new DateTime($lastLogin);
                $now = new DateTime();
                $daysSinceLogin = $now->diff($lastLoginDate)->days;
                
                if ($daysSinceLogin >= 2) {
                    $requireOTP = true;
                    $otpReason = "inactivity";
                }
            } else {
                // last_login is NULL (shouldn't happen after migration, but handle it)
                $requireOTP = true;
                $otpReason = "first_login";
            }
            
            if ($requireOTP) {
                // Send OTP for verification
                require_once 'send_otp_email.php';
                
                $otpResult = sendOTPEmail($userId, $userEmail, $userName, $conn);
                
                if ($otpResult['success']) {
                    // Store user ID and reason in session for OTP verification
                    $_SESSION['otp_user_id'] = $userId;
                    $_SESSION['otp_username'] = $userName;
                    $_SESSION['otp_reason'] = $otpReason;
                    
                    // Redirect to OTP verification page
                    header("Location: verify_otp.php");
                    exit();
                } else {
                    $error_message = "Failed to send verification code. Please try again.";
                }
            } else {
                // No OTP needed - Login directly
                session_regenerate_id(true);
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
                $_SESSION["id"] = $userId;
                $_SESSION["phonenumber"] = $row["phonenumber"];
                $_SESSION["username"] = $userName;
                $_SESSION["email"] = $userEmail;
                $_SESSION["email_verified"] = $emailVerified;
                
                // Update last_login timestamp
                $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $userId);
                $updateStmt->execute();
                $updateStmt->close();
                
                // Redirect to dashboard
                header("Location: index.php");
                exit();
            }
        } else {
            // Invalid password
            $error_message = "Invalid credentials.";
        }
    } else {
        // User not found
        $error_message = "Invalid credentials.";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CLSU CRRDC | SMARTFARM LOGIN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="assets/clsu-official-logo.png">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

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

    .login-box {
      width: 100%;
      max-width: 700px;
      padding: 2rem;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .login-box img {
      max-width: 160px;
      margin-bottom: 1rem;
    }

    .login-title {
      font-size: 28px;
      font-weight: 600;
      color: #333;
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

    .btn-login {
      background-color:#009639;
      border: none;
      font-weight: 600;
      color: #ffffff;
      padding: 0.75rem;
      border-radius: 8px;
      width: 100%;
      margin-top: 1rem;
    }

    .btn-login:hover {
      background-color: #009639;
      color: #ffffff;
    }

    .btn-guest {
      background-color: transparent;
      border: 2px solid #009639;
      font-weight: 600;
      color: #009639;
      padding: 0.75rem;
      border-radius: 8px;
      width: 100%;
      margin-top: 0.75rem;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
    }

    .btn-guest:hover {
      background-color: rgba(0, 150, 57, 0.08);
      border-color: #007a2e;
      color: #007a2e;
      transform: translateY(-1px);
    }

    .forgot, .have-account {
      text-align: right;
      font-size: 0.875rem;
      margin-top: 0.5rem;
      color: #6c757d;
    }
    .have-account a {
      text-decoration: none;
      font-weight: 600;
      color: #009639;
    }

    .have-account a:hover {
      text-decoration: underline;
      color: #006622;
    }

    @media (max-width: 768px) {
      .left-panel {
        display: none !important;
      }

      .login-box {
        box-shadow: 0 12px 25px rgba(33, 106, 30, 0.577);
        padding: 1.5rem;
        height: auto;
      }

      .login-title {
        margin-top: -.5rem;
        font-size: 24px;
      }

      .right-panel {
        padding: 2rem 1rem;
      }
    }

    /* Modern Loading Animation */
    #loadingOverlay {
      animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
    }

    .loading-content {
      animation: scaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s backwards;
    }

    /* Circular Spinner */
    .spinner-container {
      position: relative;
      width: 120px;
      height: 120px;
      margin: 0 auto 2rem;
    }

    .spinner-ring {
      position: absolute;
      width: 100%;
      height: 100%;
      border: 4px solid transparent;
      border-top-color: #009639;
      border-right-color: #009639;
      border-radius: 50%;
      animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    }

    .spinner-ring:nth-child(2) {
      border-top-color: #87b237;
      border-right-color: #87b237;
      animation-delay: -0.3s;
      width: 90%;
      height: 90%;
      top: 5%;
      left: 5%;
    }

    .spinner-ring:nth-child(3) {
      border-top-color: #4caf50;
      border-right-color: #4caf50;
      animation-delay: -0.6s;
      width: 80%;
      height: 80%;
      top: 10%;
      left: 10%;
    }

    /* Agricultural Icon in Center */
    .spinner-icon {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 48px;
      height: 48px;
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .spinner-icon svg {
      width: 100%;
      height: 100%;
      fill: #009639;
      filter: drop-shadow(0 2px 8px rgba(0, 150, 57, 0.3));
    }

    /* Loading Text */
    .loading-text {
      font-size: 1.25rem;
      font-weight: 600;
      color: #009639;
      letter-spacing: 0.5px;
    }

    .loading-dots {
      display: inline-block;
      width: 24px;
      text-align: left;
    }

    .loading-dots::after {
      content: '';
      animation: dots 1.5s steps(4, end) infinite;
    }

    /* Animations */
    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }

    @keyframes scaleIn {
      from {
        opacity: 0;
        transform: scale(0.9);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }

    @keyframes pulse {
      0%, 100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
      }
      50% {
        transform: translate(-50%, -50%) scale(1.1);
        opacity: 0.8;
      }
    }

    @keyframes dots {
      0%, 20% {
        content: '';
      }
      40% {
        content: '.';
      }
      60% {
        content: '..';
      }
      80%, 100% {
        content: '...';
      }
    }

    /* Accessibility: Reduced Motion */
    @media (prefers-reduced-motion: reduce) {
      #loadingOverlay,
      .loading-content,
      .spinner-ring,
      .spinner-icon {
        animation: none !important;
      }
      
      .spinner-ring {
        opacity: 0.6;
      }
      
      .loading-dots::after {
        content: '...';
        animation: none;
      }
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
      .spinner-container {
        width: 100px;
        height: 100px;
      }

      .spinner-icon {
        width: 40px;
        height: 40px;
      }

      .loading-text {
        font-size: 1.1rem;
      }
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

      <!-- Login Form Panel -->
      <div class="col-md-6 right-panel">
        <div class="login-box">
          <!-- Show logo on small screens only -->
          <img src="assets/clsu-official-logo.png" alt="CLSU Logo" class="d-md-none mb-3" style="max-width: 200px;">
        
          <div class="login-title">SMART FARM LOG IN</div>

          <!-- Display success message -->
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
              <?php echo $success_message; ?>
            </div>
          <?php endif; ?>

          <!-- Display error message -->
          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
              <?php echo $error_message; ?>
            </div>
          <?php endif; ?>
        
          <form method="POST" action="" id="loginForm">
            <div class="mb-3 text-start">
              <label for="identifier" class="form-label">Username / Email / Number</label>
              <input type="text" class="form-control" id="identifier" name="identifier" placeholder="Enter username, email, or number" required>
            </div>
            <div class="mb-2 text-start">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <div class="forgot">
              <a href="forgot_Password.php">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-login">LOGIN</button>
          </form>

          <form method="POST" action="" id="guestForm">
            <input type="hidden" name="guest_login" value="1">
            <button type="submit" class="btn btn-guest" id="btnGuestLogin">
              <i class="fa-solid fa-eye"></i> VIEW AS GUEST
            </button>
          </form>

          <!-- Loading Overlay -->
          <div id="loadingOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;background:rgba(255,255,255,0.92);align-items:center;justify-content:center;">
            <div class="loading-content" style="display:flex;flex-direction:column;align-items:center;">
              <!-- Circular Spinner with Agricultural Icon -->
              <div class="spinner-container">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-icon">
                  <!-- Leaf/Plant Icon -->
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z"/>
                  </svg>
                </div>
              </div>
              <!-- Animated Loading Text -->
              <div class="loading-text" role="status" aria-live="polite">
                Logging in<span class="loading-dots"></span>
              </div>
            </div>
          </div>

            <div class="have-account mt-4 d-flex justify-content-center">
                <span>Don't have an account? <a href="createaccount.php">Sign Up</a></span>
            </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      e.preventDefault();
      document.getElementById('loadingOverlay').style.display = 'flex';
      setTimeout(() => {
        e.target.submit();
      }, 3000);
    });
  </script>

</body>
</html>