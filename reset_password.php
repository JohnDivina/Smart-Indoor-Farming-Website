<?php
session_start();

// Set timezone
date_default_timezone_set('Asia/Manila');

// Check if user verified OTP
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_Password.php");
    exit();
}

include 'database.php';

$error_message = "";
$success_message = "";
$userId = $_SESSION['reset_user_id'];
$resetEmail = $_SESSION['reset_email'];

// Handle password reset
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];
    
    // Validate passwords
    if (empty($newPassword) || empty($confirmPassword)) {
        $error_message = "Please fill in all fields.";
    } elseif ($newPassword !== $confirmPassword) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($newPassword) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // Update password (using MD5 to match existing system)
        $hashedPassword = md5($newPassword);
        
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        if ($stmt->execute()) {
            // Clear all session variables
            unset($_SESSION['otp_verified']);
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['otp_user_id']);
            unset($_SESSION['otp_username']);
            unset($_SESSION['otp_reason']);
            
            // Set success message in session for login page
            $_SESSION['password_reset_success'] = true;
            
            // Redirect to login page
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Failed to reset password. Please try again.";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CLSU CRRDC | RESET PASSWORD</title>
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

    .reset-box {
      width: 100%;
      max-width: 700px;
      padding: 2rem;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .reset-box img {
      max-width: 160px;
      margin-bottom: 1rem;
    }

    .reset-title {
      font-size: 28px;
      font-weight: 600;
      color: #333;
      margin-bottom: 1rem;
    }

    .reset-subtitle {
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

    .btn-reset {
      background-color:#009639;
      border: none;
      font-weight: 600;
      color: #ffffff;
      padding: 0.75rem;
      border-radius: 8px;
      width: 100%;
      margin-top: 1rem;
    }

    .btn-reset:hover {
      background-color: #007a2f;
      color: #ffffff;
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

      .reset-box {
        box-shadow: 0 12px 25px rgba(33, 106, 30, 0.577);
        padding: 1.5rem;
        height: auto;
      }

      .reset-title {
        margin-top: -.5rem;
        font-size: 24px;
      }

      .right-panel {
        padding: 2rem 1rem;
      }
    }

    .info-box {
      background: #e8f5e9;
      border-left: 4px solid #009639;
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

    .password-requirements {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      padding: 0.75rem;
      margin-top: 0.5rem;
      border-radius: 4px;
      text-align: left;
      font-size: 13px;
    }

    .password-requirements ul {
      margin: 0;
      padding-left: 1.5rem;
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

      <!-- Reset Form Panel -->
      <div class="col-md-6 right-panel">
        <div class="reset-box">
          <!-- Show logo on small screens only -->
          <img src="assets/clsu-official-logo.png" alt="CLSU Logo" class="d-md-none mb-3" style="max-width: 200px;">
        
          <div class="reset-title">Reset Your Password</div>
          <div class="reset-subtitle">
            Enter your new password for <strong><?php echo htmlspecialchars($resetEmail); ?></strong>
          </div>

          <div class="info-box">
            <p><strong>🔒 Create a strong password</strong></p>
            <p>Choose a password that you haven't used before.</p>
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
              <label for="new_password" class="form-label">New Password</label>
              <input 
                type="password" 
                class="form-control" 
                id="new_password" 
                name="new_password" 
                placeholder="Enter new password" 
                required
                minlength="6"
              >
            </div>

            <div class="mb-3 text-start">
              <label for="confirm_password" class="form-label">Confirm Password</label>
              <input 
                type="password" 
                class="form-control" 
                id="confirm_password" 
                name="confirm_password" 
                placeholder="Re-enter new password" 
                required
                minlength="6"
              >
            </div>

            <div class="password-requirements">
              <strong>Password Requirements:</strong>
              <ul>
                <li>At least 6 characters long</li>
                <li>Both passwords must match</li>
              </ul>
            </div>

            <button type="submit" class="btn btn-reset">RESET PASSWORD</button>
          </form>

          <div class="back-link mt-4">
            <a href="login.php">← Back to Login</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Auto-focus new password input
    document.getElementById('new_password').focus();
    
    // Password match validation
    document.getElementById('confirm_password').addEventListener('input', function() {
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = this.value;
      
      if (confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
      } else {
        this.setCustomValidity('');
      }
    });
  </script>

</body>
</html>
