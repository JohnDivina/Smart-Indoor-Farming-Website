<?php
session_start();
if (!isset($_SESSION["id"])) { header("Location: login.php"); exit(); }
$isGuest = !empty($_SESSION["is_guest"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Indoor Farming - Dashboard</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/clsu-official-logo.png">
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Premium Vanilla CSS -->
    <link rel="stylesheet" href="css/styles.css">
    
    <!-- Third-Party Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <!-- Bootstrap 5 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Theme Support -->
    <script src="js/theme.js"></script>

    <style>
        .guest-locked-card {
            position: relative;
            opacity: 0.85;
            cursor: pointer;
            border-color: rgba(234, 179, 8, 0.3) !important;
        }
        .guest-locked-card:hover {
            border-color: rgba(234, 179, 8, 0.7) !important;
            transform: translateY(-2px);
        }
        .guest-lock-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(234, 179, 8, 0.15);
            color: #eab308;
            border: 1px solid rgba(234, 179, 8, 0.3);
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 2;
        }
    </style>
</head>
<body class="sticky-header-page">

<div class="app-container">
    
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        
        <!-- Header -->
        <header class="top-header">
            <div class="page-title">
                <h1>Overview</h1>
                <p>Welcome back, here's what's happening in the greenhouse.</p>
            </div>
            <div class="header-actions">
                <button id="themeToggle" class="icon-button">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </header>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            
            <!-- Sensor Title -->
            <div class="section-header" style="grid-column: 1 / -1;">
                <div class="section-bullet"></div>
                <h2>Environmental Sensors</h2>
            </div>

            <!-- Temperature Card -->
            <a href="temp-humidity.php" class="glass-panel sensor-card">
                <div class="sensor-header">
                    <div class="sensor-icon-wrapper">
                        <i class="fa-solid fa-temperature-half"></i>
                    </div>
                </div>
                <div class="sensor-info">
                    <h3>Temperature</h3>
                    <div class="sensor-value" id="dashTemp">
                        -- <span class="sensor-unit">°C</span>
                    </div>
                </div>
            </a>

            <!-- Humidity Card -->
            <a href="temp-humidity.php" class="glass-panel sensor-card">
                <div class="sensor-header">
                    <div class="sensor-icon-wrapper" style="color: #0088cc; background: rgba(0, 136, 204, 0.1); border-color: rgba(0,136,204,0.2);">
                        <i class="fa-solid fa-droplet"></i>
                    </div>
                </div>
                <div class="sensor-info">
                    <h3>Humidity</h3>
                    <div class="sensor-value" id="dashHumid">
                        -- <span class="sensor-unit">%</span>
                    </div>
                </div>
            </a>

            <!-- Light Intensity Card -->
            <a href="light-intensity.php" class="glass-panel sensor-card">
                <div class="sensor-header">
                    <div class="sensor-icon-wrapper" style="color: #f5a623; background: rgba(245, 166, 35, 0.1); border-color: rgba(245,166,35,0.2);">
                        <i class="fa-solid fa-sun"></i>
                    </div>
                </div>
                <div class="sensor-info">
                    <h3>Light Intensity</h3>
                    <div class="sensor-value" id="dashLux">
                        -- <span class="sensor-unit">Lux</span>
                    </div>
                </div>
            </a>

            <!-- Controls Title -->
            <div class="section-header" style="grid-column: 1 / -1;">
                <div class="section-bullet"></div>
                <h2>System Controls</h2>
            </div>

            <?php if ($isGuest): ?>
                <!-- Fan Control (Guest Locked) -->
                <a href="javascript:void(0)" onclick="openGuestRestrictedModal('Fan Control')" class="glass-panel control-card guest-locked-card" id="dashFanCard" style="grid-column: span 4; text-decoration: none;">
                    <div class="guest-lock-badge"><i class="fa-solid fa-lock"></i> Locked</div>
                    <div class="control-status-indicator" id="dashFanInd"></div>
                    <div class="control-icon-wrapper" id="dashFanIcon">
                        <i class="fa-solid fa-fan"></i>
                    </div>
                    <h3>Auxiliary Fan</h3>
                    <p id="dashFanStatus">Checking...</p>
                </a>

                <!-- Fertigation Control (Guest Locked) -->
                <a href="javascript:void(0)" onclick="openGuestRestrictedModal('Fertigation Control')" class="glass-panel control-card guest-locked-card" id="dashFertCard" style="grid-column: span 4; text-decoration: none;">
                    <div class="guest-lock-badge"><i class="fa-solid fa-lock"></i> Locked</div>
                    <div class="control-status-indicator" id="dashFertInd"></div>
                    <div class="control-icon-wrapper" id="dashFertIcon">
                        <i class="fa-solid fa-faucet-drip"></i>
                    </div>
                    <h3>Fertigation</h3>
                    <p id="dashFertStatus">Checking...</p>
                </a>

                <!-- Solar Panel Control (Guest Locked) -->
                <a href="javascript:void(0)" onclick="openGuestRestrictedModal('Solar Panel Control')" class="glass-panel control-card guest-locked-card" id="dashSolarCard" style="grid-column: span 4; text-decoration: none;">
                    <div class="guest-lock-badge"><i class="fa-solid fa-lock"></i> Locked</div>
                    <div class="control-status-indicator" id="dashSolarInd"></div>
                    <div class="control-icon-wrapper" id="dashSolarIcon">
                        <i class="fa-solid fa-solar-panel"></i>
                    </div>
                    <h3>Solar Panel</h3>
                    <p id="dashSolarStatus">Checking...</p>
                </a>
            <?php else: ?>
                <!-- Fan Control (4-col for even 3-card layout) -->
                <a href="auxiliary-fan.php" class="glass-panel control-card" id="dashFanCard" style="grid-column: span 4;">
                    <div class="control-status-indicator" id="dashFanInd"></div>
                    <div class="control-icon-wrapper" id="dashFanIcon">
                        <i class="fa-solid fa-fan"></i>
                    </div>
                    <h3>Auxiliary Fan</h3>
                    <p id="dashFanStatus">Checking...</p>
                </a>

                <!-- Fertigation Control (4-col for even 3-card layout) -->
                <a href="fertigation.php" class="glass-panel control-card" id="dashFertCard" style="grid-column: span 4;">
                    <div class="control-status-indicator" id="dashFertInd"></div>
                    <div class="control-icon-wrapper" id="dashFertIcon">
                        <i class="fa-solid fa-faucet-drip"></i>
                    </div>
                    <h3>Fertigation</h3>
                    <p id="dashFertStatus">Checking...</p>
                </a>

                <!-- Solar Panel Control (4-col for even 3-card layout) -->
                <a href="solar-panel.php" class="glass-panel control-card" id="dashSolarCard" style="grid-column: span 4;">
                    <div class="control-status-indicator" id="dashSolarInd"></div>
                    <div class="control-icon-wrapper" id="dashSolarIcon">
                        <i class="fa-solid fa-solar-panel"></i>
                    </div>
                    <h3>Solar Panel</h3>
                    <p id="dashSolarStatus">Checking...</p>
                </a>
            <?php endif; ?>

            <!-- Soil Sensor Cards (from NPK sensor data) -->
            <div class="section-header" style="grid-column: 1 / -1; margin-top: 8px;">
                <div class="section-bullet"></div>
                <h2>Soil Sensors</h2>
            </div>

            <!-- NPK Summary moved here under Soil Sensors -->
            <a href="npk.php" class="glass-panel npk-section" style="grid-column: 1 / -1; text-decoration:none; color:inherit; display:block;">
                <h3 style="margin-bottom: 24px;">Soil Nutrients (NPK)</h3>
                <div class="npk-inner-grid">
                    <div class="npk-row" style="border-bottom: none; padding-bottom: 0; flex-direction:column; gap:8px; align-items:center;">
                        <div class="npk-label"><div class="npk-letter npk-n">N</div><span class="npk-name">Nitrogen</span></div>
                        <div class="npk-value" id="dashN">-- <span class="npk-unit">mg/kg</span></div>
                    </div>
                    <div class="npk-row" style="border-bottom: none; padding-bottom: 0; flex-direction:column; gap:8px; align-items:center;">
                        <div class="npk-label"><div class="npk-letter npk-p">P</div><span class="npk-name">Phosphorus</span></div>
                        <div class="npk-value" id="dashP">-- <span class="npk-unit">mg/kg</span></div>
                    </div>
                    <div class="npk-row" style="border-bottom: none; padding-bottom: 0; flex-direction:column; gap:8px; align-items:center;">
                        <div class="npk-label"><div class="npk-letter npk-k">K</div><span class="npk-name">Potassium</span></div>
                        <div class="npk-value" id="dashK">-- <span class="npk-unit">mg/kg</span></div>
                    </div>
                </div>
            </a>

            <!-- Soil Electrical Conductivity -->
            <a href="npk.php" class="glass-panel sensor-card" style="text-decoration:none; color:inherit;">
                <div class="sensor-header">
                    <div class="sensor-icon-wrapper" style="color:#f5a623; background:rgba(245,166,35,0.1); border-color:rgba(245,166,35,0.2);">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                </div>
                <div class="sensor-info">
                    <h3>Soil EC</h3>
                    <div class="sensor-value" id="dashEC">-- <span class="sensor-unit">dS m⁻¹</span></div>
                    <div class="sensor-status-text" id="dashECStatus" style="font-size:11px; font-weight:600; margin-top:4px;">CHECKING...</div>
                </div>
            </a>

            <!-- Soil Moisture Level -->
            <a href="npk.php" class="glass-panel sensor-card" style="text-decoration:none; color:inherit;">
                <div class="sensor-header">
                    <div class="sensor-icon-wrapper" style="color:#17a2b8; background:rgba(23,162,184,0.1); border-color:rgba(23,162,184,0.2);">
                        <i class="fa-solid fa-water"></i>
                    </div>
                </div>
                <div class="sensor-info">
                    <h3>Soil Moisture</h3>
                    <div class="sensor-value" id="dashMoist">-- <span class="sensor-unit">%</span></div>
                    <div class="sensor-status-text" id="dashMoistStatus" style="font-size:11px; font-weight:600; margin-top:4px;">CHECKING...</div>
                </div>
            </a>

            <!-- Soil pH -->
            <a href="npk.php" class="glass-panel sensor-card" style="text-decoration:none; color:inherit;">
                <div class="sensor-header">
                    <div class="sensor-icon-wrapper" style="color:#28a745; background:rgba(40,167,69,0.1); border-color:rgba(40,167,69,0.2);">
                        <i class="fa-solid fa-flask"></i>
                    </div>
                </div>
                <div class="sensor-info">
                    <h3>Soil pH</h3>
                    <div class="sensor-value" id="dashPH">--</div>
                    <div class="sensor-status-text" id="dashPHStatus" style="font-size:11px; font-weight:600; margin-top:4px;">CHECKING...</div>
                </div>
            </a>

            <!-- Soil Temperature -->
            <a href="npk.php" class="glass-panel sensor-card" style="text-decoration:none; color:inherit;">
                <div class="sensor-header">
                    <div class="sensor-icon-wrapper" style="color:#e74c3c; background:rgba(231,76,60,0.1); border-color:rgba(231,76,60,0.2);">
                        <i class="fa-solid fa-thermometer-half"></i>
                    </div>
                </div>
                <div class="sensor-info">
                    <h3>Soil Temperature</h3>
                    <div class="sensor-value" id="dashSoilTemp">-- <span class="sensor-unit">°C</span></div>
                    <div class="sensor-status-text" id="dashSoilTempStatus" style="font-size:11px; font-weight:600; margin-top:4px;">CHECKING...</div>
                </div>
            </a>

            <!-- Environmental Trends Chart (with sensor dropdown + date) -->
            <div class="glass-panel graph-section">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom: 20px;">
                    <h3 style="margin:0;">Environmental Trends</h3>
                    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <label for="sensorMode" style="font-size:13px; font-weight:600; color:var(--text-secondary);">Sensor:</label>
                            <select id="sensorMode" style="padding:6px 10px; border-radius:8px; border:1px solid var(--glass-border); background:var(--glass-bg); color:var(--text-primary); font-size:13px; cursor:pointer;">
                                <option value="temphumidity">Temperature &amp; Humidity</option>
                                <option value="light">Light Intensity</option>
                                <option value="npk">NPK</option>
                            </select>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <label for="chartDate" style="font-size:13px; font-weight:600; color:var(--text-secondary);">Date:</label>
                            <input type="date" id="chartDate" style="padding:6px 10px; border-radius:8px; border:1px solid var(--glass-border); background:var(--glass-bg); color:var(--text-primary); font-size:13px; cursor:pointer;">
                        </div>
                    </div>
                </div>
                <div id="chartLegend" style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:12px;"></div>
                <div style="height: 300px; width: 100%;">
                    <canvas id="overviewChart"></canvas>
                </div>
            </div>

            <!-- Greenhouse Location Map -->
            <div class="glass-panel" style="grid-column: 1 / -1; padding: 20px;">
                <h3 style="margin:0 0 16px;"><i class="fa-solid fa-location-dot" style="color:#e74c3c; margin-right:8px;"></i>Greenhouse Location</h3>
                <div style="position:relative; border-radius:12px; overflow:hidden; height:280px;">
                    <iframe
                        src="https://www.google.com/maps?q=15.742082,120.944123&z=16&output=embed"
                        width="100%" height="100%"
                        style="border:0; display:block;"
                        allowfullscreen loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    <div style="position:absolute; bottom:12px; left:12px; background:rgba(255,255,255,0.95); border-radius:8px; padding:8px 14px; box-shadow:0 2px 8px rgba(0,0,0,0.15); font-size:13px;">
                        <span style="font-weight:700;">📍 Central Luzon State University</span><br>
                        <a href="https://www.google.com/maps?q=15.742082,120.944123" target="_blank" style="color:#009639; font-size:12px; text-decoration:none;">View larger map</a>
                    </div>
                </div>
            </div>

        </div> <!-- end dashboard-grid -->
    </main>
</div>

<script>
// Dashboard Data Polling
async function fetchDashboardData() {
    const endpoints = {
        temp: 'api/environment/get_temp_humid.php',
        light: 'api/environment/get_light.php',
        npk: 'api/npk/get_data.php',
        fan: 'AUXILIARYBLOWER/status.php',
        fert: 'api/fertigation/get_status.php',
        solar: 'api/solar/get_panel_ui_status.php'
    };

    const fetchData = async (key, url) => {
        try {
            const res = await fetch(url);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return await res.json();
        } catch (e) {
            console.error(`Dashboard error [${key}]:`, e);
            return null;
        }
    };

    // Fetch all in parallel
    const [temp, light, npk, fan, fert, solar] = await Promise.all([
        fetchData('temp', endpoints.temp),
        fetchData('light', endpoints.light),
        fetchData('npk', endpoints.npk),
        fetchData('fan', endpoints.fan),
        fetchData('fert', endpoints.fert),
        fetchData('solar', endpoints.solar)
    ]);

    // Update UI Elements independently
    if (temp && temp.success) {
        const opacity = temp.status === 'connected' ? '1' : '0.5';
        document.getElementById('dashTemp').style.opacity = opacity;
        document.getElementById('dashHumid').style.opacity = opacity;
        document.getElementById('dashTemp').innerHTML = `${temp.temperature.toFixed(1)} <span class="sensor-unit">°C</span>`;
        document.getElementById('dashHumid').innerHTML = `${temp.humidity.toFixed(1)} <span class="sensor-unit">%</span>`;
    }

    if (light && light.success) {
        const opacity = light.status === 'connected' ? '1' : '0.5';
        const el = document.getElementById('dashLux');
        el.style.opacity = opacity;
        const luxVal = parseFloat(light.lux);
        el.innerHTML = `${Number(luxVal.toFixed(1)).toLocaleString()} <span class="sensor-unit">Lux</span>`;
    }

    if (npk && npk.success && npk.sensorData) {
        const d = npk.sensorData;
        const connected = npk.status === 'connected';
        const statusLabel = connected ? 'CONNECTED' : 'OFFLINE';
        const statusColor = connected ? '#28a745' : '#dc3545';
        const opacity = connected ? '1' : '0.5';

        // Update values and apply opacity if offline
        const npkElements = ['dashN', 'dashP', 'dashK', 'dashEC', 'dashMoist', 'dashPH', 'dashSoilTemp'];
        npkElements.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.opacity = opacity;
        });

        document.getElementById('dashN').innerHTML = `${d.n ?? '--'} <span class="npk-unit">mg/kg</span>`;
        document.getElementById('dashP').innerHTML = `${d.p ?? '--'} <span class="npk-unit">mg/kg</span>`;
        document.getElementById('dashK').innerHTML = `${d.k ?? '--'} <span class="npk-unit">mg/kg</span>`;

        // Electrical Conductivity
        const ec = parseFloat(d.ec ?? 0);
        document.getElementById('dashEC').innerHTML = `${ec.toFixed(2)} <span class="sensor-unit">dS m⁻¹</span>`;
        const ecEl = document.getElementById('dashECStatus');
        ecEl.textContent = statusLabel;
        ecEl.style.color = statusColor;

        // Soil Moisture
        const moist = parseFloat(d.moist ?? 0);
        document.getElementById('dashMoist').innerHTML = `${moist} <span class="sensor-unit">%</span>`;
        const mEl = document.getElementById('dashMoistStatus');
        mEl.textContent = statusLabel;
        mEl.style.color = statusColor;

        // Soil pH
        const ph = parseFloat(d.ph ?? 0);
        document.getElementById('dashPH').innerHTML = ph.toFixed(1);
        const phEl = document.getElementById('dashPHStatus');
        phEl.textContent = statusLabel;
        phEl.style.color = statusColor;

        // Soil Temperature
        const soilTemp = parseFloat(d.temp ?? 0);
        document.getElementById('dashSoilTemp').innerHTML = `${soilTemp} <span class="sensor-unit">°C</span>`;
        const stEl = document.getElementById('dashSoilTempStatus');
        stEl.textContent = statusLabel;
        stEl.style.color = statusColor;
    }

    if (fan) {
        // Fan UI — only animate/activate if device is online AND fan is on
        const fanOn = fan.esp_online && fan.esp_fan_state === 'on';
        const fanCard = document.getElementById('dashFanCard');
        const fanIcon = document.getElementById('dashFanIcon');
        const fanInd = document.getElementById('dashFanInd');
        
        // Apply dimming if offline
        fanCard.style.opacity = fan.esp_online ? '1' : '0.5';

        const fanStatusText = fan.esp_online
            ? `System is ${fan.esp_fan_state === 'on' ? 'ON' : 'OFF'}`
            : 'Device Offline';
        document.getElementById('dashFanStatus').textContent = fanStatusText;
        if (fan.esp_online) {
            fanInd.classList.add('online'); fanInd.classList.remove('offline');
        } else {
            fanInd.classList.remove('online'); fanInd.classList.add('offline');
        }
        if (fanOn) {
            fanCard.classList.add('active-state');
            fanIcon.classList.add('bounce-active');
        } else {
            fanCard.classList.remove('active-state');
            fanIcon.classList.remove('bounce-active');
        }
    }

    if (fert) {
        // Fert UI — only animate/activate if device is online AND pump is on
        const fertOn = fert.esp_online && fert.esp_pump_state === 'on';
        const fertCard = document.getElementById('dashFertCard');
        const fertIcon = document.getElementById('dashFertIcon');
        const fertInd = document.getElementById('dashFertInd');
        
        if (fert.esp_online) {
            window.fertFailCounter = 0;
            fertCard.style.opacity = '1';
            const fertStatusText = `System is ${fert.esp_pump_state === 'on' ? 'ON' : 'OFF'}`;
            document.getElementById('dashFertStatus').textContent = fertStatusText;
            fertInd.classList.add('online'); fertInd.classList.remove('offline');
            
            if (fertOn) {
                fertCard.classList.add('active-state');
                fertIcon.classList.add('bounce-active');
            } else {
                fertCard.classList.remove('active-state');
                fertIcon.classList.remove('bounce-active');
            }
        } else {
            window.fertFailCounter = (window.fertFailCounter || 0) + 1;
            if (window.fertFailCounter >= 3) {
                fertCard.style.opacity = '0.5';
                document.getElementById('dashFertStatus').textContent = 'Device Offline';
                fertInd.classList.remove('online'); fertInd.classList.add('offline');
                fertCard.classList.remove('active-state');
                fertIcon.classList.remove('bounce-active');
            }
        }
    }

    if (solar && solar.success) {
        // Solar Panel UI
        const solarCard = document.getElementById('dashSolarCard');
        const solarIcon = document.getElementById('dashSolarIcon');
        const solarStatus = document.getElementById('dashSolarStatus');
        const solarInd = document.getElementById('dashSolarInd');
        
        const nowMs = Date.now();
        const lsDate = new Date(solar.last_seen_at);
        const isOffline = (solar.wifi_status === 'offline') || (!solar.last_seen_at) || ((nowMs - lsDate.getTime()) > 60000); 
        
        // Apply dimming if offline
        solarCard.style.opacity = isOffline ? '0.5' : '1';

        if (!isOffline) {
            solarInd.classList.add('online'); solarInd.classList.remove('offline');
        } else {
            solarInd.classList.remove('online'); solarInd.classList.add('offline');
        }

        const isFolded = solar.actual_state === 0;
        const isOpen = solar.actual_state === 1;
        const isRunning = solar.motor_running === 1;
        const modeText = solar.mode ? (solar.mode.charAt(0).toUpperCase() + solar.mode.slice(1)) : 'Unknown';
        const modeHtml = `<span style="font-size:11px; opacity:0.8; display:block; margin-top:4px; font-weight:normal;">(${modeText} Mode)</span>`;
        
        if (isRunning) {
            solarStatus.innerHTML = `Running (${solar.direction}) ${modeHtml}`;
            solarCard.classList.add('active-state');
            solarStatus.style.color = '#f39c12';
            solarIcon.classList.add('bounce-active');
        } else if (isOpen) {
            solarStatus.innerHTML = `OPEN ${modeHtml}`;
            solarCard.classList.add('active-state');
            solarIcon.style.color = '#f39c12';
            solarStatus.style.color = '#f39c12';
            solarIcon.classList.remove('bounce-active');
        } else if (isFolded) {
            solarStatus.innerHTML = `FOLDED ${modeHtml}`;
            solarCard.classList.remove('active-state');
            solarIcon.style.color = '';
            solarStatus.style.color = '';
            solarIcon.classList.remove('bounce-active');
        } else {
            solarStatus.innerHTML = `Unknown ${modeHtml}`;
            solarIcon.classList.remove('bounce-active');
        }
    }
}

fetchDashboardData();
setInterval(fetchDashboardData, 5000);

// =====================================================
// OVERVIEW CHART — Multi-sensor with dropdown + date
// =====================================================
Chart.defaults.font.family = 'Inter, -apple-system, sans-serif';

const ctx = document.getElementById('overviewChart').getContext('2d');
let overviewChart = new Chart(ctx, {
    type: 'line',
    data: { labels: [], datasets: [] },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(128,128,128,0.1)' } },
            y: { display: true, position: 'left', grid: { color: 'rgba(128,128,128,0.1)' } },
            y1: { display: false, position: 'right', grid: { drawOnChartArea: false } }
        }
    }
});

// In-memory caches keyed by date
const thCache = new Map(), lightCache = new Map(), npkCache = new Map();
let currentSensorMode = 'temphumidity';
let userHasSelectedDateManually = false;

// Set today as default date
const chartDateInput = document.getElementById('chartDate');

function renderLegend(items) {
    const el = document.getElementById('chartLegend');
    if (!el) return;
    el.innerHTML = items.map(i =>
        `<span style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--text-secondary);"><span style="width:14px;height:14px;border-radius:50%;background:${i.color};display:inline-block;"></span>${i.label}</span>`
    ).join('');
}

async function loadSensorChart(mode, dateStr) {
    if (!dateStr) return;
    try {
        let raw = [];
        if (mode === 'temphumidity') {
            if (!thCache.has(dateStr)) {
                const res = await fetch(`api/environment/get_temp_humid_chart.php?date=${dateStr}`);
                thCache.set(dateStr, await res.json());
            }
            raw = thCache.get(dateStr) || [];
            overviewChart.data.labels = raw.map(d => new Date(d.timestamp.replace(' ','T')).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}));
            overviewChart.data.datasets = [
                { label:'Temp (°C)', borderColor:'#e74c3c', backgroundColor:'rgba(231,76,60,0.1)', data:raw.map(d=>d.temp), tension:0.3, fill:true, yAxisID:'y' },
                { label:'Humidity (%)', borderColor:'#3498db', backgroundColor:'rgba(52,152,219,0.1)', data:raw.map(d=>d.hum), tension:0.3, fill:true, yAxisID:'y1' }
            ];
            overviewChart.options.scales.y1.display = true;
            renderLegend([{color:'#e74c3c',label:'Temperature (°C)'},{color:'#3498db',label:'Humidity (%)'}]);

        } else if (mode === 'light') {
            if (!lightCache.has(dateStr)) {
                const res = await fetch(`api/environment/get_light_chart.php?date=${dateStr}`);
                lightCache.set(dateStr, await res.json());
            }
            raw = lightCache.get(dateStr) || [];
            overviewChart.data.labels = raw.map(d => new Date(d.timestamp.replace(' ','T')).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}));
            overviewChart.data.datasets = [
                { label:'Lux', borderColor:'#f5a623', backgroundColor:'rgba(245,166,35,0.1)', data:raw.map(d=>d.lux), tension:0.3, fill:true, yAxisID:'y' }
            ];
            overviewChart.options.scales.y1.display = false;
            renderLegend([{color:'#f5a623',label:'Light Intensity (Lux)'}]);

        } else if (mode === 'npk') {
            if (!npkCache.has(dateStr)) {
                const res = await fetch(`api/npk/get_data_by_date.php?date=${dateStr}`);
                npkCache.set(dateStr, await res.json());
            }
            raw = npkCache.get(dateStr) || [];
            overviewChart.data.labels = raw.map(d => new Date(d.timestamp.replace(' ','T')).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}));
            overviewChart.data.datasets = [
                { label:'Temp (°C)', borderColor:'#e74c3c', backgroundColor:'rgba(231,76,60,0.05)', data:raw.map(d=>d.temp), tension:0.3, fill:false, yAxisID:'y' },
                { label:'Moist (%)', borderColor:'#3498db', backgroundColor:'rgba(52,152,219,0.05)', data:raw.map(d=>d.moist), tension:0.3, fill:false, yAxisID:'y' },
                { label:'N (ppm)', borderColor:'#28a745', backgroundColor:'rgba(40,167,69,0.05)', data:raw.map(d=>d.n), tension:0.3, fill:false, yAxisID:'y' },
                { label:'P (ppm)', borderColor:'#f5a623', backgroundColor:'rgba(245,166,35,0.05)', data:raw.map(d=>d.p), tension:0.3, fill:false, yAxisID:'y' },
                { label:'K (ppm)', borderColor:'#9b59b6', backgroundColor:'rgba(155,89,182,0.05)', data:raw.map(d=>d.k), tension:0.3, fill:false, yAxisID:'y' }
            ];
            overviewChart.options.scales.y1.display = false;
            renderLegend([
                {color:'#e74c3c',label:'Temp (°C)'},
                {color:'#3498db',label:'Moist (%)'},
                {color:'#28a745',label:'N (ppm)'},
                {color:'#f5a623',label:'P (ppm)'},
                {color:'#9b59b6',label:'K (ppm)'}
            ]);
        }
        
        if (window.applyChartTheme) window.applyChartTheme();
        overviewChart.update();
    } catch(e) { console.error('Chart load error:', e); }
}

document.getElementById('sensorMode').addEventListener('change', async function() {
    currentSensorMode = this.value;
    
    // Auto-fetch latest date for this sensor
    try {
        const sensorTypeMap = {
            'temphumidity': 'environment',
            'light': 'light',
            'npk': 'npk'
        };
        const res = await fetch(`api/get_latest_data_date.php?sensor=${sensorTypeMap[currentSensorMode]}`);
        const data = await res.json();
        if (data.latest_date) {
            chartDateInput.value = data.latest_date;
        }
    } catch (e) {
        console.error('Latest date fetch failed:', e);
    }
    
    loadSensorChart(currentSensorMode, chartDateInput.value);
});

chartDateInput.addEventListener('change', function() {
    userHasSelectedDateManually = true;
    loadSensorChart(currentSensorMode, this.value);
});

// Smart load initial data
async function initDashboardChart() {
    const today = new Date().toISOString().split('T')[0];
    chartDateInput.value = today;

    try {
        const sensorType = currentSensorMode === 'temphumidity' ? 'environment' : 
                          currentSensorMode === 'light' ? 'light' : 'npk';
        const res = await fetch(`api/get_latest_data_date.php?sensor=${sensorType}`);
        const latest = await res.json();
        const dateToLoad = latest.latest_date || today;
        chartDateInput.value = dateToLoad;
        loadSensorChart(currentSensorMode, dateToLoad);
    } catch (e) {
        console.error('Initial chart load failed:', e);
        loadSensorChart(currentSensorMode, today);
    }
}
initDashboardChart();
</script>

<!-- Guest Restricted Modal -->
<div class="modal fade" id="guestRestrictedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px; border: 1px solid var(--glass-border); background: var(--glass-bg); backdrop-filter: blur(16px); color: var(--text-primary);">
      <div class="modal-header" style="border-bottom: 1px solid var(--glass-border); padding: 20px 24px;">
        <h5 class="modal-title" style="font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0;">
          <i class="fa-solid fa-lock" style="color: #eab308;"></i> Guest Mode Restriction
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--btn-close-filter, none);"></button>
      </div>
      <div class="modal-body" style="padding: 24px; text-align: center;">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.3); color: #eab308; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px;">
          <i class="fa-solid fa-user-lock"></i>
        </div>
        <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;" id="guestModalFeatureTitle">Control Access Restricted</h4>
        <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 20px;">
          You are currently viewing as a <strong>Guest</strong>. You have read-only access to sensor readings and system status. Control parameters and settings require logging in with an authorized account.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center;">
          <a href="user_logout.php" class="btn" style="background: #009639; color: #fff; font-weight: 600; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Log In
          </a>
          <button type="button" class="btn" data-bs-dismiss="modal" style="background: rgba(255,255,255,0.08); border: 1px solid var(--glass-border); color: var(--text-secondary); font-weight: 600; padding: 10px 20px; border-radius: 8px;">
            Continue Browsing
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function openGuestRestrictedModal(featureName) {
    if (featureName) {
        document.getElementById('guestModalFeatureTitle').textContent = featureName + ' Restricted';
    } else {
        document.getElementById('guestModalFeatureTitle').textContent = 'Control Access Restricted';
    }
    const modalEl = document.getElementById('guestRestrictedModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

<?php if (isset($_GET['guest_restricted'])): ?>
window.addEventListener('DOMContentLoaded', () => {
    openGuestRestrictedModal();
});
<?php endif; ?>
</script>

</body>
</html>
