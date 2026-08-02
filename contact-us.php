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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us - Smart Indoor Farming Dashboard</title>
  <link rel="icon" type="image/png" href="assets/clsu-official-logo.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/drts-layout.css">
  
  <style>
  .contact-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
  }
  
  .contact-header {
    background: linear-gradient(90deg, #009639 60%, #87b237 100%);
    border-radius: 18px;
    padding: 24px 32px;
    margin-bottom: 32px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 20px;
  }
  
  .contact-icon {
    font-size: 2.5rem;
    color: #fff;
  }
  
  .contact-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
  }
  
  .contact-subtitle {
    font-size: 1rem;
    color: #e0ffe0;
    margin: 0;
  }
  
  .team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
  }
  
  .team-card {
    background: var(--card-bg, #fff);
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border: 1px solid var(--border-color, #e0e0e0);
    transition: all 0.3s ease;
    text-align: center;
  }
  
  .team-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    transform: translateY(-4px);
  }
  
  .team-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #009639 0%, #87b237 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 2.5rem;
    color: #fff;
    font-weight: 700;
  }
  
  .team-name {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-primary, #333);
    margin-bottom: 8px;
  }
  
  .team-role {
    font-size: 0.95rem;
    color: #009639;
    font-weight: 600;
    margin-bottom: 16px;
  }
  
  .team-contact-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 20px;
  }
  
  .contact-info-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: var(--body-bg, #f8f9fa);
    border-radius: 8px;
    font-size: 0.9rem;
    color: var(--text-secondary, #6c757d);
  }
  
  .contact-info-item i {
    color: #009639;
    font-size: 1.1rem;
  }
  
  .placeholder-text {
    font-style: italic;
    color: #999;
  }
  
  @media (max-width: 768px) {
    .contact-container {
      margin: 1rem auto;
      padding: 0;
    }
    
    .contact-header {
      padding: 20px;
    }
    
    .contact-title {
      font-size: 1.3rem;
    }
    
    .team-grid {
      grid-template-columns: 1fr;
    }
  }
  </style>
</head>
<body>
  <!-- DRTS-Style Layout -->
  <div class="dashboard-layout">
    
    <!-- Left Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <img src="assets/clsu-official-logo.png" alt="CLSU Logo" class="sidebar-logo" />
        <span class="sidebar-title">CLSU SMART FARM</span>
      </div>
      <nav class="sidebar-nav">
        <a href="index.php" class="sidebar-nav-item">
          <i class="bi bi-speedometer2"></i>
          <span>Dashboard</span>
        </a>
        <a href="about-us.php" class="sidebar-nav-item">
          <i class="bi bi-people-fill"></i>
          <span>About Us</span>
        </a>
        <a href="settings.php" class="sidebar-nav-item">
          <i class="bi bi-gear-fill"></i>
          <span>Settings</span>
        </a>
      </nav>
    </aside>
    
    <!-- Backdrop Overlay for Mobile -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    
    <!-- Top Header -->
    <header class="top-header">
      <div class="header-left">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle sidebar">
          <i class="bi bi-list"></i>
        </button>
        <h1 class="header-title">CONTACT US</h1>
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
        
        <div class="contact-container">
          <!-- Contact Header -->
          <div class="contact-header">
            <i class="bi bi-envelope-fill contact-icon"></i>
            <div>
              <h2 class="contact-title">Contact Us</h2>
              <p class="contact-subtitle">Reach out to our team for assistance and support</p>
            </div>
          </div>
          
          <!-- Team Cards Grid -->
          <div class="team-grid">
            
            <!-- Project Leader -->
            <div class="team-card">
              <div class="team-avatar">FG</div>
              <div class="team-name">Franz Marielle Nogoy Garcia</div>
              <div class="team-role">Project Leader</div>
              
              <div class="team-contact-info">
                <div class="contact-info-item">
                  <i class="bi bi-telephone-fill"></i>
                  <span class="placeholder-text">Phone number will be added</span>
                </div>
                <div class="contact-info-item">
                  <i class="bi bi-envelope-fill"></i>
                  <span class="placeholder-text">fmcnogoy@clsu.edu.ph</span>
                </div>
              </div>
            </div>
            
            <!-- Technical Assistant 1 -->
            <div class="team-card">
              <div class="team-avatar">IS</div>
              <div class="team-name">Ivan Christian Salinas</div>
              <div class="team-role">Instructor I / Project Staff</div>
              
              <div class="team-contact-info">
                <div class="contact-info-item">
                  <i class="bi bi-telephone-fill"></i>
                  <span class="placeholder-text">Phone number will be added</span>
                </div>
                <div class="contact-info-item">
                  <i class="bi bi-envelope-fill"></i>
                  <span class="placeholder-text">banbansalinas@clsu.edu.ph</span>
                </div>
              </div>
            </div>
            
            <!-- Technical Assistant 2 -->
            <div class="team-card">
              <div class="team-avatar">JD</div>
              <div class="team-name">John Rey Divina</div>
              <div class="team-role">Project Technical Assistant</div>
              
              <div class="team-contact-info">
                <div class="contact-info-item">
                  <i class="bi bi-telephone-fill"></i>
                  <span class="placeholder-text">Phone number will be added</span>
                </div>
                <div class="contact-info-item">
                  <i class="bi bi-envelope-fill"></i>
                  <span class="placeholder-text">johnrey_divina@clsu.edu.ph</span>
                </div>
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
