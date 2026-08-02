<?php
session_start();
if (!isset($_SESSION["id"])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>About Us - Smart Farm</title>
    <link rel="stylesheet" href="css/about.css">
</head>
<body class="sticky-header-page">

<div class="app-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <div class="page-title">
                <p><a href="index.php" style="color:var(--accent-primary); text-decoration:none;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a></p>
                <h1 style="margin-top: 12px;">About Us</h1>
                <p>The team behind the CLSU Smart Indoor Farming initiative.</p>
            </div>
            <div class="header-actions">
                <button id="themeToggle" class="icon-button">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </header>

        <!-- Section Banner -->
        <div class="about-section-header">
            <div class="about-section-icon">
                <i class="fa-solid fa-people-group"></i>
            </div>
            <div>
                <h2>Meet Our Team</h2>
                <p>Smart Indoor-Farming for Hot Pepper and Tomato Production</p>
            </div>
        </div>

        <!-- Team Grid -->
        <div class="team-grid">

            <!-- Member 1 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project1.png" alt="Franz Marielle Nogoy Garcia, PhD">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Franz Marielle Nogoy Garcia, PhD</h3>
                    <p class="team-role">Project Leader / Associate Professor V</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-leaf"></i> Agriculture</span>
                        <span class="expertise-tag"><i class="fa-solid fa-seedling"></i> Crop Science</span>
                    </div>
                    <p class="team-bio">Leading the development of smart farming solutions with over 15 years of experience in agricultural technology and IoT integration.</p>
                </div>
            </div>

            <!-- Member 2 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project2.png" alt="Engr. Roldan T. Quitos">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Engr. Roldan T. Quitos</h3>
                    <p class="team-role">Associate Professor I</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-gears"></i> Agr. Mechanization</span>
                        <span class="expertise-tag"><i class="fa-solid fa-microchip"></i> Electronics</span>
                    </div>
                    <p class="team-bio">Specializes in designing and implementing sensor networks and control systems for precision agriculture applications.</p>
                </div>
            </div>

            <!-- Member 3 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project3.png" alt="Sylvester A. Badua, PhD">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Sylvester A. Badua, PhD</h3>
                    <p class="team-role">Associate Professor V</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-gears"></i> Agr. Mechanization</span>
                        <span class="expertise-tag"><i class="fa-solid fa-database"></i> Instr. &amp; Controls</span>
                    </div>
                    <p class="team-bio">Develops intuitive dashboards and data visualization tools to help farmers make informed decisions based on real-time sensor data.</p>
                </div>
            </div>

            <!-- Member 4 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project4.png" alt="Engr. John Vincent A. Nate">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Engr. John Vincent A. Nate</h3>
                    <p class="team-role">Former Faculty</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-vial"></i> Bio-processing</span>
                        <span class="expertise-tag"><i class="fa-solid fa-water"></i> Soils &amp; Water</span>
                    </div>
                    <p class="team-bio">Provides expertise in crop management and optimal growing conditions for hot pepper and tomato production in controlled environments.</p>
                </div>
            </div>

            <!-- Member 5 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project5.png" alt="Ivan Christian L. Salinas">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Ivan Christian L. Salinas</h3>
                    <p class="team-role">Instructor I</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-code"></i> Programming</span>
                        <span class="expertise-tag"><i class="fa-solid fa-network-wired"></i> Networking</span>
                    </div>
                    <p class="team-bio">Analyzes system performance and develops automation algorithms to optimize resource usage and maximize crop yield.</p>
                </div>
            </div>

            <!-- Member 6 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project6.png" alt="John Rey L. Divina">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">John Rey L. Divina</h3>
                    <p class="team-role">Project Technical Assistant</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-bolt"></i> Electronics</span>
                        <span class="expertise-tag"><i class="fa-solid fa-microchip"></i> Automation</span>
                        <span class="expertise-tag"><i class="fa-solid fa-wifi"></i> Data Comm.</span>
                    </div>
                    <p class="team-bio">Conducts field experiments and maintains comprehensive documentation of system performance and crop growth patterns.</p>
                </div>
            </div>

            <!-- Member 7 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/projectx.png" alt="Alcris R. Dumale">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Alcris R. Dumale</h3>
                    <p class="team-role">Project Laborer I</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-screwdriver-wrench"></i> Tech Support</span>
                        <span class="expertise-tag"><i class="fa-solid fa-toolbox"></i> Maintenance</span>
                    </div>
                    <p class="team-bio">Provides crucial on-site technical support and routine system maintenance to assure uninterrupted operational capacity.</p>
                </div>
            </div>

            <!-- Member 8 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project8.png" alt="Emelie C. Ablaza, PhD">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Emelie C. Ablaza, PhD</h3>
                    <p class="team-role">Associate Professor V</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-earth-americas"></i> Environmental</span>
                        <span class="expertise-tag"><i class="fa-solid fa-magnifying-glass-chart"></i> Impact Assmt.</span>
                    </div>
                    <p class="team-bio">Evaluates the environmental sustainability and long-term societal impacts of deployed smart farming protocols in the local sector.</p>
                </div>
            </div>

            <!-- Member 9 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project7.png" alt="Jeannie-Rose G. Fabulla, PhD">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Jeannie-Rose G. Fabulla, PhD</h3>
                    <p class="team-role">Associate Professor II</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-water"></i> Soils &amp; Water</span>
                        <span class="expertise-tag"><i class="fa-solid fa-magnifying-glass-chart"></i> Impact Assmt.</span>
                    </div>
                    <p class="team-bio">Researches ideal soil configurations and hydrodynamic modeling to tailor precision irrigation to specific plant cultivars.</p>
                </div>
            </div>

            <!-- Member 10 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project9.png" alt="Marilou M. Sarong, PhD">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Marilou M. Sarong, PhD</h3>
                    <p class="team-role">Associate Professor IV</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-seedling"></i> Soil Fertility</span>
                        <span class="expertise-tag"><i class="fa-solid fa-mountain"></i> Soil Physics</span>
                    </div>
                    <p class="team-bio">Specialist in soil nutrient management, analyzing optimal NPK thresholds for greenhouse-based crop production.</p>
                </div>
            </div>

            <!-- Member 11 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project12.png" alt="Cesar V. Ortinero, PhD">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Cesar V. Ortinero, PhD</h3>
                    <p class="team-role">Professor III</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-earth-americas"></i> Environmental</span>
                        <span class="expertise-tag"><i class="fa-solid fa-chart-line"></i> Impact Assmt.</span>
                    </div>
                    <p class="team-bio">Collaborates on strategic environmental assessments and sustainability frameworks for integrated indoor farming ventures.</p>
                </div>
            </div>

            <!-- Member 12 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project10.png" alt="Katherine DA. Bautista">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Katherine DA. Bautista</h3>
                    <p class="team-role">Assistant Professor I</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-earth-americas"></i> Environmental</span>
                        <span class="expertise-tag"><i class="fa-solid fa-flask"></i> Assessment</span>
                    </div>
                    <p class="team-bio">Research focus on ecological monitoring and environmental compliance within the smart indoor agriculture framework.</p>
                </div>
            </div>

            <!-- Member 13 -->
            <div class="team-card">
                <div class="team-image-overlay"></div>
                <div class="team-card-header">
                    <div class="team-avatar">
                        <img src="assets/images/team/project11.jpg" alt="Fernando P. Ferrer">
                    </div>
                </div>
                <div class="team-card-body">
                    <h3 class="team-name">Fernando P. Ferrer</h3>
                    <p class="team-role">Assistant Professor II</p>
                    <div class="team-expertise">
                        <span class="expertise-tag"><i class="fa-solid fa-store"></i> Marketing</span>
                        <span class="expertise-tag"><i class="fa-solid fa-calculator"></i> Cost Control</span>
                    </div>
                    <p class="team-bio">Manages the economic feasibility and marketing strategies for distributed greenhouse products in domestic markets.</p>
                </div>
            </div>

        </div><!-- /.team-grid -->
    </main>
</div>

<script>
// Set overlay background image from each card's <img> src
document.querySelectorAll('.team-card').forEach(card => {
    const img = card.querySelector('.team-avatar img');
    const overlay = card.querySelector('.team-image-overlay');
    if (img && overlay) {
        overlay.style.backgroundImage = `url('${img.src}')`;
    }
});


</script>

</body>
</html>
