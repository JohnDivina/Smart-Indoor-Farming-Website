<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}
if (!empty($_SESSION["is_guest"])) {
    header("Location: index.php?guest_restricted=1");
    exit();
}

include 'database.php';
$userId = $_SESSION["id"];
$sql = "SELECT username, email, phonenumber FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
$current_page = 'settings.php';
include 'includes/header.php';
?>
<title>Settings – Smart Farm Dashboard</title>
<link rel="stylesheet" href="css/styles.css">
<link rel="stylesheet" href="css/about.css">
<!-- Bootstrap CSS (for modals) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
/* ─────────────────────────────────────────
   Bootstrap sidebar reset
   Bootstrap adds margin-bottom:1rem to <p> which breaks sidebar spacing
──────────────────────────────────────────*/
.sidebar p,
.sidebar ul,
.nav-links p {
  margin: 0 !important;
  padding: 0 !important;
}

/* ─────────────────────────────────────────
   Settings Content Styles
   (Layout comes from css/styles.css)
──────────────────────────────────────────*/
.settings-wrapper {
  max-width: 820px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 28px;
}

/* Hero banner */
.settings-hero {
  padding: 28px 32px;
  border-radius: var(--border-radius-lg);
  background: linear-gradient(120deg, var(--accent-primary) 0%, #009939 60%, var(--accent-secondary) 100%);
  color: #fff;
  display: flex;
  align-items: center;
  gap: 20px;
}
.settings-hero .hero-icon {
  width: 56px;
  height: 56px;
  background: rgba(255,255,255,0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  flex-shrink: 0;
}
.settings-hero h2 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.settings-hero p  { font-size: 14px; opacity: 0.88; margin: 0; }

/* Section card */
.settings-card {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  border: 1px solid var(--glass-border);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--glass-shadow);
  overflow: hidden;
}
.settings-card-header {
  padding: 20px 28px;
  border-bottom: 1px solid var(--glass-border);
}
.settings-card-header h4 {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 2px;
}
.settings-card-header p {
  font-size: 13px;
  color: var(--text-muted);
  margin: 0;
}

/* Info rows */
.info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 28px;
  border-bottom: 1px solid var(--glass-border);
  gap: 16px;
}
.info-row:last-of-type { border-bottom: none; }
.info-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}
.info-value {
  font-size: 15px;
  font-weight: 500;
  color: var(--text-primary);
}
.info-value.password-dots { letter-spacing: 4px; font-size: 18px; color: var(--text-muted); }

/* Inline edit button */
.btn-edit {
  padding: 7px 18px;
  border-radius: 8px;
  border: 1px solid var(--accent-primary);
  background: transparent;
  color: var(--accent-primary);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  flex-shrink: 0;
}
.btn-edit:hover {
  background: var(--accent-primary);
  color: #fff;
}

/* Inline form */
.form-section {
  display: none;
  padding: 16px 28px 20px;
  background: rgba(0,0,0,0.02);
  border-top: 1px solid var(--glass-border);
}
[data-theme="dark"] .form-section { background: rgba(255,255,255,0.03); }
.form-section.active { display: block; }

.form-section .form-group { margin-bottom: 14px; }
.form-section label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.4px;
  display: block;
  margin-bottom: 6px;
}
.form-section input {
  width: 100%;
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px solid var(--glass-border);
  background: var(--glass-bg);
  color: var(--text-primary);
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
  font-family: inherit;
}
.form-section input:focus { border-color: var(--accent-primary); }
.form-actions {
  display: flex; gap: 10px; margin-top: 16px;
}
.btn-save {
  padding: 9px 22px;
  border-radius: 8px;
  border: none;
  background: var(--accent-primary);
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: opacity 0.2s;
}
.btn-save:hover { opacity: 0.85; }
.btn-cancel {
  padding: 9px 22px;
  border-radius: 8px;
  border: 1px solid var(--glass-border);
  background: transparent;
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
}

/* Danger zone */
.danger-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 28px;
  gap: 16px;
}
.danger-label {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 2px;
}
.danger-desc {
  font-size: 13px;
  color: #ea4c4c;
  margin: 0;
}
.btn-danger {
  padding: 8px 20px;
  border-radius: 8px;
  border: none;
  background: #ea4c4c;
  color: #fff;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  white-space: nowrap;
  flex-shrink: 0;
  transition: opacity 0.2s;
}
.btn-danger:hover { opacity: 0.85; }

/* Delete modal */
.modal { z-index: 1060 !important; }
.modal-backdrop { z-index: 1059 !important; }
body.modal-open { overflow: hidden !important; }

#deleteAccountModal .modal-content {
  border-radius: 16px;
  overflow: hidden;
  border: 2px solid #dc3545;
}
#deleteAccountModal .modal-header {
  background: #dc3545;
  color: #fff;
  border-bottom: none;
}
#deleteAccountModal .modal-title { color: #fff; font-weight: 700; }
#deleteAccountModal .modal-body  { padding: 28px; }
#deleteAccountModal .btn-close   { filter: invert(1); }

.delete-step { display: none; text-align: center; }
.delete-step.active { display: block; }
.delete-warning-icon {
  font-size: 52px;
  color: #dc3545;
  display: block;
  margin-bottom: 16px;
}
.delete-warning-title { font-size: 20px; font-weight: 700; margin-bottom: 12px; }
.delete-warning-text  { color: #555; font-size: 14px; margin-bottom: 20px; }
.btn-send-otp {
  width: 100%;
  padding: 12px;
  background: #dc3545;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  margin-bottom: 10px;
}
.btn-send-otp:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-danger-solid {
  width: 100%;
  padding: 12px;
  background: #dc3545;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
}
.btn-danger-solid:disabled { opacity: 0.6; cursor: not-allowed; }
.otp-input-delete {
  text-align: center;
  font-size: 24px;
  font-weight: 700;
  letter-spacing: 8px;
  border-radius: 10px;
}
.step-back-btn {
  background: none; border: none;
  color: #6c757d; cursor: pointer;
  display: flex; align-items: center; gap: 6px;
  font-size: 14px; margin-bottom: 12px;
}
.resend-link { color: #dc3545; cursor: pointer; text-decoration: underline; font-size: 14px; }
</style>

</head>
<body class="sticky-header-page">
<div class="app-container">

  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content">

    <!-- Header -->
    <div class="top-header">
      <div class="page-title">
        <p><a href="index.php" style="color:var(--accent-primary); text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a></p>
        <h1 style="margin-top: 12px;">Settings</h1>
        <p>Manage your account preferences and security.</p>
      </div>
      <div class="header-actions">
        <button class="icon-button" id="themeToggle" title="Toggle theme">
          <i class="fa-solid fa-moon"></i>
        </button>
      </div>
    </div>

    <!-- Settings Content -->
    <div class="settings-wrapper">

      <!-- Hero -->
      <div class="settings-hero">
        <div class="hero-icon"><i class="fa-solid fa-gear"></i></div>
        <div>
          <h2>Account Settings</h2>
          <p>Manage your account information and preferences.</p>
        </div>
      </div>

      <!-- Profile Information -->
      <div class="settings-card">
        <div class="settings-card-header">
          <h4><i class="fa-solid fa-user" style="color:var(--accent-primary); margin-right:8px;"></i>Profile Information</h4>
          <p>Update your personal details.</p>
        </div>

        <!-- Username -->
        <div class="info-row">
          <div>
            <div class="info-label">Username</div>
            <div class="info-value" id="displayUsername"><?= htmlspecialchars($userData['username'] ?? 'N/A') ?></div>
          </div>
          <button class="btn-edit" onclick="toggleEditForm('usernameForm')">Edit</button>
        </div>
        <div class="form-section" id="usernameForm">
          <form id="updateUsernameForm">
            <div class="form-group">
              <label>New Username</label>
              <input type="text" name="newUsername" required minlength="3" placeholder="Enter new username">
            </div>
            <div class="form-group">
              <label>Current Password</label>
              <input type="password" name="password" required placeholder="Confirm with current password">
            </div>
            <div class="form-actions">
              <button type="submit" class="btn-save">Save</button>
              <button type="button" class="btn-cancel" onclick="toggleEditForm('usernameForm')">Cancel</button>
            </div>
          </form>
        </div>

        <!-- Email -->
        <div class="info-row">
          <div>
            <div class="info-label">Email Address</div>
            <div class="info-value" id="displayEmail"><?= htmlspecialchars($userData['email'] ?? 'N/A') ?></div>
          </div>
          <button class="btn-edit" onclick="toggleEditForm('emailForm')">Edit</button>
        </div>
        <div class="form-section" id="emailForm">
          <form id="updateEmailForm">
            <div class="form-group">
              <label>New Email</label>
              <input type="email" name="newEmail" required placeholder="Enter new email address">
            </div>
            <div class="form-group">
              <label>Current Password</label>
              <input type="password" name="password" required placeholder="Confirm with current password">
            </div>
            <div class="form-actions">
              <button type="submit" class="btn-save">Save</button>
              <button type="button" class="btn-cancel" onclick="toggleEditForm('emailForm')">Cancel</button>
            </div>
          </form>
        </div>

        <!-- Phone -->
        <div class="info-row">
          <div>
            <div class="info-label">Phone Number</div>
            <div class="info-value" id="displayPhone"><?= htmlspecialchars($userData['phonenumber'] ?? 'N/A') ?></div>
          </div>
          <button class="btn-edit" onclick="toggleEditForm('phoneForm')">Edit</button>
        </div>
        <div class="form-section" id="phoneForm">
          <form id="updatePhoneForm">
            <div class="form-group">
              <label>New Phone Number</label>
              <input type="tel" name="newPhone" required placeholder="e.g. 09xxxxxxxxx">
            </div>
            <div class="form-group">
              <label>Current Password</label>
              <input type="password" name="password" required placeholder="Confirm with current password">
            </div>
            <div class="form-actions">
              <button type="submit" class="btn-save">Save</button>
              <button type="button" class="btn-cancel" onclick="toggleEditForm('phoneForm')">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Security -->
      <div class="settings-card">
        <div class="settings-card-header">
          <h4><i class="fa-solid fa-lock" style="color:var(--accent-primary); margin-right:8px;"></i>Security</h4>
          <p>Change your password to keep your account secure.</p>
        </div>

        <div class="info-row">
          <div>
            <div class="info-label">Password</div>
            <div class="info-value password-dots">••••••••</div>
          </div>
          <button class="btn-edit" onclick="toggleEditForm('passwordForm')">Change Password</button>
        </div>
        <div class="form-section" id="passwordForm">
          <form id="updatePasswordForm">
            <div class="form-group">
              <label>Current Password</label>
              <input type="password" name="currentPassword" required placeholder="Enter current password">
            </div>
            <div class="form-group">
              <label>New Password</label>
              <input type="password" name="newPassword" required placeholder="Min. 6 characters, 1 number or symbol">
            </div>
            <div class="form-group">
              <label>Confirm New Password</label>
              <input type="password" name="confirmPassword" required placeholder="Re-enter new password">
            </div>
            <div class="form-actions">
              <button type="submit" class="btn-save">Update Password</button>
              <button type="button" class="btn-cancel" onclick="toggleEditForm('passwordForm')">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="settings-card" style="border-color: rgba(234,76,76,0.3);">
        <div class="settings-card-header" style="border-bottom-color: rgba(234,76,76,0.2);">
          <h4><i class="fa-solid fa-triangle-exclamation" style="color:#ea4c4c; margin-right:8px;"></i>Danger Zone</h4>
          <p>Irreversible actions. Proceed with caution.</p>
        </div>
        <div class="danger-row">
          <div>
            <div class="danger-label">Delete Account</div>
            <p class="danger-desc">Permanently delete your account and all associated data.</p>
          </div>
          <button class="btn-danger" onclick="openDeleteModal()">Delete Account</button>
        </div>
      </div>

      <!-- Terms of Use -->
      <div class="settings-card">
        <div class="settings-card-header">
          <h4><i class="fa-solid fa-file-lines" style="color:var(--accent-primary); margin-right:8px;"></i>Terms of Use</h4>
          <p>Guidelines for using the Smart Farm Dashboard.</p>
        </div>
        <div style="padding: 20px 28px; font-size:14px; color:var(--text-secondary); line-height:1.7;">
          <p style="margin:0 0 12px;">By using the <strong>CLSU Smart Indoor Farming Dashboard</strong>, you agree to the following terms:</p>
          <ul style="margin:0; padding-left:20px; display:flex; flex-direction:column; gap:8px;">
            <li>This system is intended exclusively for authorized personnel of the Crops and Resources Research and Development Center (CRRDC), Central Luzon State University.</li>
            <li>Sensor data, control commands, and account information are strictly confidential and must not be shared outside the authorized team.</li>
            <li>Do not attempt to reverse-engineer, modify, or interfere with the system's hardware connections or ESP32 microcontroller firmware.</li>
            <li>Any unauthorized access, data manipulation, or misuse of the control systems (fan, fertigation, solar) may result in immediate account termination.</li>
            <li>The system is provided "as-is" for research and operational monitoring purposes. The development team is not liable for crop damage resulting from misuse of automated controls.</li>
          </ul>
        </div>
      </div>

      <!-- Privacy Policy -->
      <div class="settings-card">
        <div class="settings-card-header">
          <h4><i class="fa-solid fa-shield-halved" style="color:var(--accent-primary); margin-right:8px;"></i>Privacy Policy</h4>
          <p>How we collect and protect your information.</p>
        </div>
        <div style="padding: 20px 28px; font-size:14px; color:var(--text-secondary); line-height:1.7;">
          <p style="margin:0 0 12px;"><strong>Information We Collect</strong></p>
          <ul style="margin:0 0 14px; padding-left:20px; display:flex; flex-direction:column; gap:8px;">
            <li><strong>Account Data:</strong> Username, email address, and phone number provided at registration.</li>
            <li><strong>Sensor Data:</strong> All readings from NPK, temperature/humidity, and light intensity sensors are logged with timestamps for research purposes.</li>
            <li><strong>Activity Logs:</strong> Control actions (fan on/off, fertigation cycles, solar panel status) are recorded for audit and analysis.</li>
          </ul>
          <p style="margin:0 0 12px;"><strong>How We Use Your Information</strong></p>
          <ul style="margin:0 0 14px; padding-left:20px; display:flex; flex-direction:column; gap:8px;">
            <li>Account data is used solely for authentication and profile management within this system.</li>
            <li>Sensor and activity data is used exclusively for research, monitoring, and improving the Smart Indoor Farming system.</li>
            <li>Data is never sold or shared with third parties outside of CLSU CRRDC.</li>
          </ul>
          <p style="margin:0; color:var(--text-muted); font-size:13px;">For questions about data privacy, contact the project team at the Crops and Resources Research and Development Center, CLSU.</p>
        </div>
      </div>

      <!-- Troubleshooting -->
      <div class="settings-card">
        <div class="settings-card-header">
          <h4><i class="fa-solid fa-screwdriver-wrench" style="color:var(--accent-primary); margin-right:8px;"></i>Troubleshooting</h4>
          <p>Common issues and how to fix them.</p>
        </div>
        <div style="padding: 20px 28px; display:flex; flex-direction:column; gap:20px;">

          <!-- Issue 1 -->
          <div style="background:rgba(0,0,0,0.02); border-radius:10px; padding:16px 20px; border:1px solid var(--glass-border);">
            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-bottom:8px;">
              <i class="fa-solid fa-circle-xmark" style="color:#ea4c4c; margin-right:8px;"></i>Sensor shows "OFFLINE"
            </div>
            <p style="font-size:13px; color:var(--text-secondary); margin:0;">Ensure the ESP32 microcontroller is powered and connected to the local network. Check that the sensor's Wi-Fi credentials match the current network. If the issue persists, restart the ESP32 by pressing its reset button or power-cycling it.</p>
          </div>

          <!-- Issue 2 -->
          <div style="background:rgba(0,0,0,0.02); border-radius:10px; padding:16px 20px; border:1px solid var(--glass-border);">
            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-bottom:8px;">
              <i class="fa-solid fa-circle-xmark" style="color:#ea4c4c; margin-right:8px;"></i>Fan or Fertigation controls not responding
            </div>
            <p style="font-size:13px; color:var(--text-secondary); margin:0;">Check the ESP32 connection status at the top of the control page. If the device is "OFFLINE", the command will be queued but not immediately executed. Verify wiring connections to the relay modules and confirm the relay's DB table row exists (id = 1).</p>
          </div>

          <!-- Issue 3 -->
          <div style="background:rgba(0,0,0,0.02); border-radius:10px; padding:16px 20px; border:1px solid var(--glass-border);">
            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-bottom:8px;">
              <i class="fa-solid fa-circle-xmark" style="color:#ea4c4c; margin-right:8px;"></i>Cannot log in or create an account
            </div>
            <p style="font-size:13px; color:var(--text-secondary); margin:0;">Ensure the MySQL server (XAMPP) is running and the database credentials in <code>database.php</code> match your local configuration. Verify the <code>users</code> table exists. If you forgot your password, contact the system administrator.</p>
          </div>

          <!-- Issue 4 -->
          <div style="background:rgba(0,0,0,0.02); border-radius:10px; padding:16px 20px; border:1px solid var(--glass-border);">
            <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-bottom:8px;">
              <i class="fa-solid fa-circle-xmark" style="color:#ea4c4c; margin-right:8px;"></i>Charts show no data
            </div>
            <p style="font-size:13px; color:var(--text-secondary); margin:0;">Select a date where sensor readings were recorded. Data is stored per day — if the ESP32 was not running on that specific date, no records will exist. Check the database directly via phpMyAdmin if needed.</p>
          </div>

          <p style="font-size:13px; color:var(--text-muted); margin:0; text-align:center;">
            Still having issues? Contact the development team at <strong>CLSU CRRDC</strong>.
          </p>
        </div>
      </div>

    </div><!-- end settings-wrapper -->
  </main>
</div><!-- end app-container -->

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Delete Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetDeleteModal()"></button>
      </div>
      <div class="modal-body">
        <!-- Step 1: Warning -->
        <div class="delete-step active" id="deleteStep1">
          <i class="bi bi-trash3-fill delete-warning-icon"></i>
          <div class="delete-warning-title">Are you sure?</div>
          <p class="delete-warning-text">
            This action is <strong>permanent and irreversible</strong>. Your account, profile, and all associated data will be deleted forever.<br><br>
            To confirm, we will send a one-time verification code to your registered email address.
          </p>
          <button class="btn-send-otp" id="sendOtpBtn" onclick="requestDeleteOTP()">
            <i class="bi bi-envelope-fill me-2"></i>Send OTP to my Email
          </button>
          <button type="button" class="btn btn-secondary w-100 mt-2" data-bs-dismiss="modal" onclick="resetDeleteModal()">Cancel</button>
        </div>

        <!-- Step 2: OTP -->
        <div class="delete-step" id="deleteStep2">
          <button class="step-back-btn" onclick="goToStep(1)"><i class="bi bi-arrow-left"></i> Back</button>
          <i class="bi bi-shield-lock-fill delete-warning-icon" style="color:#dc3545;"></i>
          <div class="delete-warning-title">Enter Verification Code</div>
          <p class="delete-warning-text">A 6-digit code was sent to your email. Enter it below to permanently delete your account.</p>
          <div class="mb-3">
            <input type="text" class="form-control otp-input-delete" id="deleteOtpInput" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="000000" autocomplete="one-time-code">
          </div>
          <div class="text-center mb-3">
            <span id="resendCountdownText" style="color:#999; font-size:0.9rem;">Resend code in <strong id="resendTimer">60</strong>s</span>
            <span id="resendLinkWrap" style="display:none;"><span class="resend-link" onclick="requestDeleteOTP(true)">Resend OTP</span></span>
          </div>
          <button class="btn-danger-solid" id="confirmDeleteBtn" onclick="confirmDeleteAccount()">
            <i class="bi bi-trash3 me-2"></i>Confirm Deletion
          </button>
          <div id="deleteOtpError" class="text-danger small mt-2 text-center" style="display:none;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ─────────────────────────────────────────
// Toggle inline edit forms
// ─────────────────────────────────────────
function toggleEditForm(formId) {
  const form = document.getElementById(formId);
  document.querySelectorAll('.form-section').forEach(f => {
    if (f.id !== formId) f.classList.remove('active');
  });
  form.classList.toggle('active');
}

// ─────────────────────────────────────────
// Toast
// ─────────────────────────────────────────
function showToast(message, type = 'success') {
  const el = document.createElement('div');
  el.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
  el.style.zIndex = '9999';
  el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 5000);
}

// ─────────────────────────────────────────
// Update Username
// ─────────────────────────────────────────
document.getElementById('updateUsernameForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    const r = await fetch('api/update_profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
      body: JSON.stringify({ action: 'update_username', username: fd.get('newUsername'), password: fd.get('password') })
    });
    const d = await r.json();
    if (d.success) {
      document.getElementById('displayUsername').textContent = fd.get('newUsername');
      toggleEditForm('usernameForm');
      showToast('Username updated!', 'success');
      e.target.reset();
    } else { showToast(d.message || 'Failed', 'error'); }
  } catch { showToast('Network error.', 'error'); }
});

// ─────────────────────────────────────────
// Update Email
// ─────────────────────────────────────────
document.getElementById('updateEmailForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    const r = await fetch('api/update_profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
      body: JSON.stringify({ action: 'update_email', email: fd.get('newEmail'), password: fd.get('password') })
    });
    const d = await r.json();
    if (d.success) {
      document.getElementById('displayEmail').textContent = fd.get('newEmail');
      toggleEditForm('emailForm');
      showToast('Email updated!', 'success');
      e.target.reset();
    } else { showToast(d.message || 'Failed', 'error'); }
  } catch { showToast('Network error.', 'error'); }
});

// ─────────────────────────────────────────
// Update Phone
// ─────────────────────────────────────────
document.getElementById('updatePhoneForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    const r = await fetch('api/update_profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
      body: JSON.stringify({ action: 'update_phone', phone: fd.get('newPhone'), password: fd.get('password') })
    });
    const d = await r.json();
    if (d.success) {
      document.getElementById('displayPhone').textContent = fd.get('newPhone');
      toggleEditForm('phoneForm');
      showToast('Phone number updated!', 'success');
      e.target.reset();
    } else { showToast(d.message || 'Failed', 'error'); }
  } catch { showToast('Network error.', 'error'); }
});

// ─────────────────────────────────────────
// Update Password
// ─────────────────────────────────────────
document.getElementById('updatePasswordForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  if (fd.get('newPassword') !== fd.get('confirmPassword')) {
    showToast('Passwords do not match!', 'error'); return;
  }
  const pw = fd.get('newPassword');
  if (pw.length < 6 || !/[0-9!@#$%^&*(),.?":{}|<>]/.test(pw)) {
    showToast('Password must be at least 6 characters with a number or symbol.', 'error'); return;
  }
  try {
    const r = await fetch('api/update_profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
      body: JSON.stringify({ action: 'update_password', currentPassword: fd.get('currentPassword'), newPassword: fd.get('newPassword') })
    });
    const d = await r.json();
    if (d.success) {
      toggleEditForm('passwordForm');
      showToast('Password updated!', 'success');
      e.target.reset();
    } else { showToast(d.message || 'Failed', 'error'); }
  } catch { showToast('Network error.', 'error'); }
});

// ─────────────────────────────────────────
// Delete Account Modal Flow
// ─────────────────────────────────────────
let deleteCountdownInterval = null;
let deleteModalInstance = null;

function openDeleteModal() {
  resetDeleteModal();
  deleteModalInstance = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
  deleteModalInstance.show();
}

function resetDeleteModal() {
  goToStep(1);
  document.getElementById('deleteOtpInput').value = '';
  document.getElementById('deleteOtpError').style.display = 'none';
  document.getElementById('sendOtpBtn').disabled = false;
  document.getElementById('sendOtpBtn').innerHTML = '<i class="bi bi-envelope-fill me-2"></i>Send OTP to my Email';
  clearInterval(deleteCountdownInterval);
  document.getElementById('resendCountdownText').style.display = 'inline';
  document.getElementById('resendLinkWrap').style.display = 'none';
  document.getElementById('resendTimer').textContent = '60';
}

function goToStep(step) {
  document.querySelectorAll('.delete-step').forEach(el => el.classList.remove('active'));
  document.getElementById('deleteStep' + step).classList.add('active');
}

function startResendCountdown() {
  let seconds = 60;
  document.getElementById('resendCountdownText').style.display = 'inline';
  document.getElementById('resendLinkWrap').style.display = 'none';
  document.getElementById('resendTimer').textContent = seconds;
  clearInterval(deleteCountdownInterval);
  deleteCountdownInterval = setInterval(() => {
    seconds--;
    document.getElementById('resendTimer').textContent = seconds;
    if (seconds <= 0) {
      clearInterval(deleteCountdownInterval);
      document.getElementById('resendCountdownText').style.display = 'none';
      document.getElementById('resendLinkWrap').style.display = 'inline';
    }
  }, 1000);
}

async function requestDeleteOTP(isResend = false) {
  const btn = document.getElementById('sendOtpBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Sending...';
  try {
    const r = await fetch('api/send_delete_otp.php', { method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } });
    const d = await r.json();
    if (d.success) {
      goToStep(2);
      startResendCountdown();
      showToast(isResend ? 'New code sent!' : 'Code sent to your email!', 'success');
    } else {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-envelope-fill me-2"></i>Send OTP to my Email';
      showToast(d.message || 'Failed to send OTP.', 'error');
    }
  } catch {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-envelope-fill me-2"></i>Send OTP to my Email';
    showToast('Network error.', 'error');
  }
}

async function confirmDeleteAccount() {
  const otp = document.getElementById('deleteOtpInput').value.trim();
  const errorEl = document.getElementById('deleteOtpError');
  const btn = document.getElementById('confirmDeleteBtn');
  errorEl.style.display = 'none';

  if (!/^\d{6}$/.test(otp)) {
    errorEl.textContent = 'Please enter a valid 6-digit code.';
    errorEl.style.display = 'block';
    return;
  }
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Deleting...';
  try {
    const r = await fetch('api/delete_account.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
      body: JSON.stringify({ otp })
    });
    const d = await r.json();
    if (d.success) {
      if (deleteModalInstance) deleteModalInstance.hide();
      showToast('Account deleted. Redirecting...', 'success');
      setTimeout(() => { window.location.href = 'login.php'; }, 1500);
    } else {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-trash3 me-2"></i>Confirm Deletion';
      errorEl.textContent = d.message || 'Invalid OTP.';
      errorEl.style.display = 'block';
    }
  } catch {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-trash3 me-2"></i>Confirm Deletion';
    errorEl.textContent = 'Network error. Please try again.';
    errorEl.style.display = 'block';
  }
}

document.getElementById('deleteOtpInput').addEventListener('keydown', (e) => {
  if (e.key === 'Enter') confirmDeleteAccount();
});
document.getElementById('deleteOtpInput').addEventListener('input', (e) => {
  e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
});
</script>
</body>
</html>
