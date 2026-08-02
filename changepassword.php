<?php
session_start();

// Only allow access if OTP was verified
if (!isset($_SESSION["reset_email"]) || !isset($_SESSION["otp_verified"])) {
    header("Location: forgot_Password.php");
    exit();
}

include 'database.php';

$error_message = "";
$success_message = "";

$email = $_SESSION["reset_email"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Password validation (min 6 chars, at least one number or special symbol)
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif (!preg_match('/^(?=.*[0-9!@#$%^&*(),.?":{}|<>]).{6,}$/', $new_password)) {
        $error_message = "Password must be at least 6 characters and contain at least one number or special symbol.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Hash the password using MD5
        $hashed_password = md5($new_password);

        // Update password in DB using email
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            $success_message = "Password changed successfully. <a href='login.php'>Login here</a>.";
            // Clear session variables related to password reset
            unset($_SESSION["reset_email"]);
            unset($_SESSION["otp_verified"]);
        } else {
            $error_message = "Failed to update password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>CLSU | Change Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="assets/clsu-official-logo.png">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;600;700&display=swap" rel="stylesheet" />
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
    .have-account {
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
      }
      .right-panel {
        padding: 2rem 1rem;
      }
    }
  </style>
</head>
<body>

  <div class="container-fluid main-container">
    <div class="row h-100">
      <div class="col-md-6 left-panel d-none d-md-flex">
        <img src="assets/clsu-official-logo.png" alt="CLSU Logo" />
      </div>
      <div class="col-md-6 right-panel">
        <div class="login-box">
          <img src="assets/clsu-official-logo.png" alt="CLSU Logo" class="d-md-none mb-3" style="max-width: 200px;" />
          <div class="login-title">CHANGE PASSWORD</div>

          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
              <?php echo htmlspecialchars($error_message); ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
              <?php echo $success_message; ?>
            </div>
          <?php endif; ?>

          <?php if (empty($success_message)): ?>
          <form method="POST" action="" id="changepwForm" novalidate>
            <div class="mb-3 text-start">
              <label for="new_password" class="form-label">New Password</label>
              <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password" required minlength="6" autocomplete="new-password" />
              <div id="passwordHelp" class="form-text text-danger"></div>
            </div>
            <div class="mb-3 text-start">
              <label for="confirm_password" class="form-label">Confirm New Password</label>
              <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required autocomplete="new-password" />
              <div id="confirmHelp" class="form-text text-danger"></div>
            </div>
            <button type="submit" class="btn btn-login">Change Password</button>
          </form>
          <?php endif; ?>

          <div class="have-account mt-4 d-flex justify-content-center">
            <span>Back to <a href="login.php">Login</a></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password validation (same as createaccount)
    const passwordInput = document.getElementById('new_password');
    const confirmInput = document.getElementById('confirm_password');
    const passwordHelp = document.getElementById('passwordHelp');
    const confirmHelp = document.getElementById('confirmHelp');
    const form = document.getElementById('changepwForm');

    function validatePassword() {
      const password = passwordInput.value;
      let valid = true;
      let message = "";

      // Minimum 6 characters, at least one number or special character
      if (password.length < 6) {
        message = "Password must be at least 6 characters.";
        valid = false;
      } else if (!(/[0-9!@#$%^&*(),.?":{}|<>]/.test(password))) {
        message = "Password must contain at least one number or special symbol.";
        valid = false;
      }

      passwordHelp.textContent = message;
      return valid;
    }

    function validateConfirm() {
      const password = passwordInput.value;
      const confirm = confirmInput.value;
      let valid = true;
      let message = "";

      if (confirm && password !== confirm) {
        message = "Passwords do not match.";
        valid = false;
      }

      confirmHelp.textContent = message;
      return valid;
    }

    passwordInput.addEventListener('input', function() {
      validatePassword();
      validateConfirm();
    });

    confirmInput.addEventListener('input', validateConfirm);

    form.addEventListener('submit', function(e) {
      let validPassword = validatePassword();
      let validConfirm = validateConfirm();
      if (!validPassword || !validConfirm) {
        e.preventDefault();
      }
    });

    // Auto-logout after 5 minutes (300,000 ms) of inactivity
    (function() {
      let logoutTimer;
      const logoutAfter = 5 * 60 * 1000; // 5 minutes in milliseconds

      function resetLogoutTimer() {
        clearTimeout(logoutTimer);
        logoutTimer = setTimeout(() => {
          window.location.href = 'user_logout.php?inactive=1';
        }, logoutAfter);
      }

      // Reset timer on user activity
      ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, resetLogoutTimer, true);
      });

      resetLogoutTimer(); // Start timer on page load
    })();
  </script>
</body>
</html>