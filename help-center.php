<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'includes/header.php'; ?>
<title>Help Center - Smart Indoor Farming Dashboard</title>
<link rel="stylesheet" href="css/drts-layout.css">
  
  
  <style>
  /* ========================================
     DRTS-STYLE LAYOUT: SIDEBAR + HEADER + CONTENT
     ======================================== */
  
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }
  
  body {
    font-family: 'Outfit', 'Inter', sans-serif;
    color: var(--text-primary);
    background: var(--bg-base);
    overflow-x: hidden;
  }
  
  /* Layout Container */
  .dashboard-layout {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
  }
  
  /* ========== SIDEBAR ========== */
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    width: 250px;
    background: linear-gradient(180deg, #1e6031 0%, #009639 100%);
    color: #fff;
    transition: width 0.3s ease, transform 0.3s ease;
    z-index: 1000;
    overflow-x: hidden;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
  }
  
  .sidebar.collapsed {
    width: 70px;
  }
  
  body.sidebar-collapsed .sidebar {
    width: 70px;
  }
  
  .sidebar-header {
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    position: relative;
  }
  
  .sidebar-logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
  }
  
  .sidebar-title {
    font-size: 0.9rem;
    font-weight: 600;
    white-space: nowrap;
    opacity: 1;
    transition: opacity 0.3s ease, visibility 0.3s ease;
  }
  
  .sidebar.collapsed .sidebar-title,
  body.sidebar-collapsed .sidebar-title {
    opacity: 0;
    width: 0;
    visibility: hidden;
    overflow: hidden;
  }
  
  .sidebar-nav {
    padding: 1rem 0;
  }
  
  .sidebar-nav-item {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    color: #fff;
    text-decoration: none;
    transition: background 0.2s ease;
    cursor: pointer;
    gap: 1rem;
  }
  
  .sidebar-nav-item:hover,
  .sidebar-nav-item.active {
    background: rgba(255, 255, 255, 0.1);
  }
  
  .sidebar-nav-item i {
    font-size: 1.3rem;
    flex-shrink: 0;
    width: 24px;
    text-align: center;
  }
  
  .sidebar-nav-item span {
    white-space: nowrap;
    opacity: 1;
    transition: opacity 0.3s ease, visibility 0.3s ease;
  }
  
  .sidebar.collapsed .sidebar-nav-item span,
  body.sidebar-collapsed .sidebar-nav-item span {
    opacity: 0;
    width: 0;
    visibility: hidden;
    overflow: hidden;
  }
  
  /* ========== HEADER ========== */
  .top-header {
    position: fixed;
    top: 0;
    left: 250px;
    right: 0;
    height: 60px;
    background: linear-gradient(90deg, #009639 60%, #87b237 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    z-index: 999;
    transition: left 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  
  body.sidebar-collapsed .top-header {
    left: 70px;
  }
  
  .header-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
  }
  
  .hamburger-btn {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.2s ease;
  }
  
  .hamburger-btn:hover {
    color: #FFD700;
  }
  
  .header-title {
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.02em;
  }
  
  .header-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
  }
  
  .header-right .notification-icon,
  .header-right button {
    background: transparent;
    border: none;
    color: #fff;
    cursor: pointer;
    transition: color 0.2s ease;
    position: relative;
  }
  
  .header-right .notification-icon i,
  .header-right button i {
    font-size: 1.3rem;
    color: #fff;
  }
  
  .header-right .notification-icon:hover i,
  .header-right button:hover i {
    color: #FFD700;
  }
  
  /* ========== MAIN CONTENT ========== */
  .main-content {
    margin-left: 250px;
    padding-top: 60px;
    padding-left: 2rem;
    padding-right: 2rem;
    padding-bottom: 2rem;
    transition: margin-left 0.3s ease;
    background: transparent;
    overflow-y: auto;
    min-height: calc(100vh - 60px);
  }
  
  body.sidebar-collapsed .main-content {
    margin-left: 70px;
  }
  
  /* ========== FOOTER ========== */
  .dashboard-footer {
    margin-left: 250px;
    padding: 1rem 2rem;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    text-align: center;
    font-size: 0.85rem;
    color: #6c757d;
    transition: margin-left 0.3s ease;
  }
  
  body.sidebar-collapsed .dashboard-footer {
    margin-left: 70px;
  }
  
  /* ========== SIDEBAR BACKDROP & CLOSE BUTTON ========== */
  .sidebar-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
  }
  
  body.sidebar-mobile-open .sidebar-backdrop {
    opacity: 1;
    visibility: visible;
  }
  
  .sidebar-close-btn {
    display: none;
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #fff;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.2s ease;
    z-index: 10;
  }
  
  .sidebar-close-btn:hover {
    color: #FFD700;
  }
  
  @media (max-width: 768px) {
    .sidebar-close-btn {
      display: block;
    }
  }
  
  /* ========== RESPONSIVE ========== */
  @media (max-width: 768px) {
    .sidebar {
      transform: translateX(-100%);
      z-index: 1001;
    }
    
    body.sidebar-mobile-open .sidebar {
      transform: translateX(0);
    }
    
    .top-header {
      left: 0 !important;
      padding: 0 1rem;
    }
    
    .main-content {
      margin-left: 0 !important;
      padding: 1rem;
    }
    
    .dashboard-footer {
      margin-left: 0 !important;
    }
    
    .header-title {
      font-size: 0.85rem;
      line-height: 1.5;
      padding: 0 8px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 100%;
    }
    
    .header-left {
      gap: 0.5rem;
      flex: 1;
      min-width: 0;
    }
    
    .header-right {
      gap: 1rem;
      flex-shrink: 0;
    }
    
    .header-right .notification-icon,
    .header-right button {
      min-width: 44px;
      min-height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .hamburger-btn {
      min-width: 44px;
      min-height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }
  
  /* ========== PAGE-SPECIFIC STYLES ========== */
  .help-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
  }
  
  .help-header {
    background: linear-gradient(90deg, #009639 60%, #87b237 100%);
    border-radius: 18px;
    padding: 24px 32px;
    margin-bottom: 32px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 20px;
  }
  
  .help-icon {
    font-size: 2.5rem;
    color: #fff;
  }
  
  .help-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
  }
  
  .help-subtitle {
    font-size: 1rem;
    color: #e0ffe0;
    margin: 0;
  }
  
  .help-card {
    background: var(--glass-bg);
    backdrop-filter: var(--glass-blur);
    -webkit-backdrop-filter: var(--glass-blur);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 24px;
    box-shadow: var(--glass-shadow);
    border: 1px solid var(--glass-border);
  }
  
  .help-section-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text-primary, #333);
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #009639;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .help-section-title i {
    color: #009639;
  }
  
  .troubleshooting-item {
    padding: 16px 0;
    border-bottom: 1px solid var(--glass-border);
  }
  
  .troubleshooting-item:last-child {
    border-bottom: none;
  }
  
  .troubleshooting-title {
    font-weight: 600;
    color: var(--text-primary, #333);
    font-size: 1.05rem;
    margin-bottom: 8px;
  }
  
  .troubleshooting-content {
    color: var(--text-secondary, #6c757d);
    font-size: 0.95rem;
    line-height: 1.6;
  }
  
  @media (max-width: 768px) {
    .help-container {
      margin: 1rem auto;
      padding: 0;
    }
    
    .help-header {
      padding: 20px;
    }
    
    .help-title {
      font-size: 1.3rem;
    }
    
    .help-card {
      padding: 20px;
    }
  }
  </style>
</head>
<body>
  <!-- DRTS-Style Layout -->
  <div class="dashboard-layout">
    
    <!-- Left Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Backdrop Overlay for Mobile -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    
    <!-- Top Header -->
    <header class="top-header">
      <div class="header-left">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle sidebar">
          <i class="bi bi-list"></i>
        </button>
        <h1 class="header-title">HELP CENTER</h1>
      </div>
      
      <div class="header-right">
        <button class="btn btn-link p-0" 
                id="themeToggle" 
                title="Toggle Dark Mode" 
                aria-label="Toggle between light and dark mode"
                onclick="toggleTheme()">
          <i class="bi bi-moon-fill" id="themeIcon"></i>
        </button>
        
        <button class="btn btn-link p-0" 
                id="logoutBtn" 
                title="Logout"
                aria-label="Logout from dashboard">
          <i class="bi bi-box-arrow-right"></i>
        </button>
      </div>
    </header>
    
    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
      <main class="main-content">
        
        <div class="help-container">
          <!-- Help Header -->
          <div class="help-header">
            <i class="bi bi-question-circle-fill help-icon"></i>
            <div>
              <h2 class="help-title">Help Center</h2>
              <p class="help-subtitle">Find answers to common questions and troubleshooting guides</p>
            </div>
          </div>
          
          <!-- Basic Troubleshooting Card -->
          <div class="help-card">
            <h3 class="help-section-title">
              <i class="bi bi-tools"></i>
              Basic Troubleshooting
            </h3>
            
            <div class="troubleshooting-item">
              <div class="troubleshooting-title">
                <i class="bi bi-exclamation-circle text-warning"></i> Content will be added soon
              </div>
              <div class="troubleshooting-content">
                This section is currently under development. Troubleshooting guides and FAQs will be added here to help you resolve common issues with the Smart Farm system.
              </div>
            </div>
            
            <div class="troubleshooting-item">
              <div class="troubleshooting-title">
                <i class="bi bi-info-circle text-info"></i> Placeholder for troubleshooting topics
              </div>
              <div class="troubleshooting-content">
                Topics will include: sensor connectivity issues, data synchronization problems, account access issues, and more.
              </div>
            </div>
          </div>
          
          <!-- Quick Links Card -->
          <div class="help-card">
            <h3 class="help-section-title">
              <i class="bi bi-link-45deg"></i>
              Quick Links
            </h3>
            
            <div class="troubleshooting-item">
              <div class="troubleshooting-title">
                <i class="bi bi-envelope"></i> Contact Support
              </div>
              <div class="troubleshooting-content">
                Need more help? <a href="contact-us.php" style="color: #009639; text-decoration: none; font-weight: 600;">Contact our team</a> for personalized assistance.
              </div>
            </div>
            
            <div class="troubleshooting-item">
              <div class="troubleshooting-title">
                <i class="bi bi-arrow-left-circle"></i> Back to Settings
              </div>
              <div class="troubleshooting-content">
                <a href="settings.php" style="color: #009639; text-decoration: none; font-weight: 600;">Return to Settings</a> to manage your account.
              </div>
            </div>
          </div>
          
        </div>
        
      </main>
    </div>
    
    <!-- Footer -->
    <footer class="dashboard-footer">
      <p>&copy; 2026 CLSU Smart Farm. All rights reserved.</p>
    </footer>
  </div>

  <!-- Logout Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to logout?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <a href="user_logout.php" class="btn btn-danger">Logout</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/sidebar.js"></script>
  
  <script>
    // Logout button
    document.getElementById('logoutBtn').addEventListener('click', () => {
      const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
      modal.show();
    });
  </script>
</body>
</html>
