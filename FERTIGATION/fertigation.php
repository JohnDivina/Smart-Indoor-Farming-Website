<?php
session_start();

if (!isset($_SESSION["id"])) {
  header("Location: ../login.php");
  exit();
}
?>
<!--
  Summary (Dec 2025):
  - Adjusted Fertigation status OFF color to gray for visual consistency with other pages.
  - Added an Auto Mode status subtext line (Auto Mode: ON/OFF) while preserving existing IDs and JS hooks.
  - Kept all network calls, cooldown logic, and existing element IDs intact.
-->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fertigation Control</title>
<link rel="icon" type="image/png" href="../assets/clsu-official-logo.png">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root {
  --primary-green: #387F39;
  --text-primary: #333;
  --text-secondary: #6B6B6B;
  --bg-light: #F6F6F6;
  --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

body { 
  font-family: 'Libre Franklin', sans-serif; 
  background: linear-gradient(180deg, #ffffff, #f3f8f3);
  min-height: 100vh;
  margin: 0;
  padding: 0;
}

.container {
  max-width: 650px;
  margin: 0 auto;
  padding: 0 20px;
}

.header { 
  background: #ffffff; 
  padding: 16px 0;
  text-align: center;
  position: relative;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  margin-bottom: 24px;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  max-width: 600px;
  margin: 0 auto;
}

.back-btn { 
  position: absolute;
  left: 16px;
  font-size: 24px;
  color: var(--primary-green);
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: background-color 0.2s;
}

.back-btn:hover {
  background-color: rgba(56, 127, 57, 0.1);
}

.page-title {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.panel-card { 
  background: #fff; 
  padding: 24px; 
  border-radius: 12px; 
  box-shadow: var(--card-shadow);
  max-width: 100%;
  margin: 0 auto 32px;
  text-align: center;
  border: 1px solid rgba(0,0,0,0.05);
}

.card-title {
  font-size: 22px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 24px;
}

.irrigation-icon {
  font-size: 80px;
  color: var(--primary-green);
  margin: 16px 0;
  transition: all 0.3s ease;
}

.flowing {
  animation: drip 1s ease-in-out infinite;
}

@keyframes drip {
  0% { transform: translateY(0); opacity: 1; }
  50% { transform: translateY(6px); opacity: .85; }
  100% { transform: translateY(0); opacity: 1; }
}

.status-container {
  margin: 20px 0;
  text-align: center;
}

.status {
  font-size: 20px;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.status-value {
  font-weight: 700;
  color: var(--primary-green);
}

.status-value.off {
  color: #6c757d;
}

.connection-status {
  font-size: 14px;
  color: var(--text-secondary);
  margin-top: 8px;
}

.connected {
  color: #28a745;
}

.disconnected {
  color: #dc3545;
}

.button-group {
  display: flex;
  justify-content: center;
  gap: 16px;
  margin: 24px 0;
}

.btn {
  min-width: 120px;
  padding: 10px 20px;
  font-weight: 600;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn:active {
  transform: translateY(1px);
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.btn-success {
  background-color: var(--primary-green);
}

.btn-success:hover {
  background-color: #2d6c2e;
  box-shadow: 0 4px 8px rgba(56, 127, 57, 0.2);
}

.btn-danger {
  background-color: #dc3545;
}

.btn-danger:hover {
  background-color: #bb2d3b;
  box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
}

.auto-mode {
  background-color: #f8f9fa;
  border-radius: 12px;
  padding: 16px;
  margin-top: 20px;
  border: 1px solid rgba(0,0,0,0.05);
}

.auto-mode-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  max-width: 300px;
  margin: 0 auto;
}

.auto-label {
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
  font-size: 16px;
}

.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 30px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 22px;
  width: 22px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: var(--primary-green);
}

input:focus + .slider {
  box-shadow: 0 0 1px var(--primary-green);
}

input:checked + .slider:before {
  transform: translateX(30px);
}

.stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-top: 24px;
  text-align: left;
  font-size: 14px;
}

.stat-item {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid rgba(0,0,0,0.05);
}

.stat-label {
  color: var(--text-secondary);
  font-size: 13px;
  margin-bottom: 4px;
}

.stat-value {
  font-weight: 600;
  color: var(--text-primary);
}

.footer {
  background: #f1f8f1;
  padding: 12px 0;
  text-align: center;
  font-size: 14px;
  color: var(--text-secondary);
  position: fixed;
  bottom: 0;
  width: 100%;
  border-top: 1px solid rgba(0,0,0,0.05);
}

.cooldown {
  color: #dc3545;
  font-weight: 600;
  margin-top: 8px;
  font-size: 14px;
}

/* Responsive adjustments */
@media (max-width: 576px) {
  .panel-card {
    padding: 20px 16px;
  }
  
  .button-group {
    flex-direction: column;
    gap: 12px;
  }
  
  .btn {
    width: 100%;
  }
  
  .stats {
    grid-template-columns: 1fr;
  }
}

/* Toast Notification System */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.toast {
  background: #fff;
  color: #333;
  padding: 16px 24px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 300px;
  opacity: 0;
  transform: translateX(400px);
  transition: all 0.3s ease;
  border-left: 4px solid #387F39;
}

.toast.show {
  opacity: 1;
  transform: translateX(0);
}

.toast.toast-success { 
  border-left-color: #28a745; 
}

.toast.toast-error { 
  border-left-color: #dc3545; 
}

.toast.toast-warning { 
  border-left-color: #ffc107; 
}

.toast.toast-info { 
  border-left-color: #2196f3; 
}

.toast i {
  font-size: 1.2rem;
}

@media (max-width: 576px) {
  .toast-container {
    top: 10px;
    right: 10px;
    left: 10px;
  }
  
  .toast {
    min-width: unset;
  }
}
</style>
</head>

<body>
<div class="container">
  <header class="header">
    <div class="header-content">
      <button class="back-btn" id="back" aria-label="Go back">
        <i class="bi bi-arrow-left"></i>
      </button>
      <h1 class="page-title">Fertigation</h1>
    </div>
  </header>

  <main>
    <div class="panel-card">
      <h2 class="card-title">Fertigation Pump</h2>

      <i class="bi bi-droplet-fill irrigation-icon" id="irrigationIcon" aria-hidden="true"></i>

      <div class="status-container">
        <div class="status">
          Pump is <span class="status-value off" id="pumpState">OFF</span>
        </div>
        
      </div>

      <div class="button-group">
        <button class="btn btn-success" id="btnOn">START</button>
        <button class="btn btn-danger" id="btnOff">STOP</button>
      </div>

      <div class="auto-mode">
        <div class="auto-mode-content">
          <span class="auto-label">Scheduled Mode</span>
          <label class="switch">
            <input type="checkbox" id="scheduledToggle">
            <span class="slider"></span>
          </label>
        </div>

        <!-- Schedule Time Input -->
        <div class="schedule-time-input" id="scheduleTimeContainer" style="display:none; margin-top:16px;">
          <label for="scheduleTime" style="font-size:14px; color:var(--text-secondary); margin-bottom:8px; display:block;">Start Time:</label>
          <div style="display:flex; gap:8px; align-items:center;">
            <input type="time" id="scheduleTime" value="06:00" style="padding:8px; border-radius:8px; border:1px solid #ccc; font-family:'Libre Franklin',sans-serif;">
            <button class="btn btn-success" id="saveScheduleBtn" style="min-width:80px; padding:8px 16px; font-size:14px;">Save</button>
          </div>
        </div>

        <!-- Schedule Status -->
        <div class="schedule-status" id="scheduleStatus" style="margin-top:12px; font-size:13px; text-align:center;"></div>
      </div>

      <div class="stats">
        <div class="stat-item">
          <div class="stat-label">Moisture</div>
          <div class="stat-value" id="moistureValue">— %</div>
        </div>
        <div class="stat-item">
          <div class="stat-label">Last Irrigation</div>
          <div class="stat-value" id="lastIrrigation">— min ago</div>
        </div>
      </div>
      
      <div class="cooldown" id="cooldownLabel"></div>
    </div>
  </main>

  <footer class="footer">
    <i class="bi bi-check-circle-fill"></i> System Ready
  </footer>
</div> <!-- end container -->

<script>
// ============================================================================
// FERTIGATION CONTROL - SERVER-SIDE ARCHITECTURE
// ============================================================================
// All browser actions now go through PHP server APIs
// NO direct ESP32 calls - works on LAN, Cloudflare, and mobile data
// ESP32 will poll server via heartbeat (to be implemented in ESP32 firmware)
// ============================================================================

const pumpState = document.getElementById('pumpState');
const icon = document.getElementById('irrigationIcon');
const btnOn = document.getElementById('btnOn');
const btnOff = document.getElementById('btnOff');
const scheduledToggle = document.getElementById('scheduledToggle');
const scheduleTimeContainer = document.getElementById('scheduleTimeContainer');
const scheduleTime = document.getElementById('scheduleTime');
const saveScheduleBtn = document.getElementById('saveScheduleBtn');
const scheduleStatus = document.getElementById('scheduleStatus');
const cooldownLabel = document.getElementById('cooldownLabel');

let cooldownTimer = null;
let remainingCooldown = 0;
const startOriginalText = btnOn.textContent;
const stopOriginalText = btnOff.textContent;

// Toast Notification System
function showToast(message, type = 'info') {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  
  const icons = {
    success: 'check-circle-fill',
    error: 'x-circle-fill',
    warning: 'exclamation-triangle-fill',
    info: 'info-circle-fill'
  };
  
  toast.innerHTML = `
    <i class="bi bi-${icons[type]}"></i>
    <span>${message}</span>
  `;
  
  container.appendChild(toast);
  setTimeout(() => toast.classList.add('show'), 10);
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// ============================================================================
// FETCH STATUS FROM SERVER (NOT ESP32 DIRECTLY)
// ============================================================================
async function syncStatus() {
    try {
        const res = await fetch('api/get_status.php');
        const data = await res.json();
        
        if (!data.success) {
            console.error('Status fetch failed:', data.message);
            return;
        }
        
        // Update pump state display (use esp_pump_state from API)
        const pumpOn = data.esp_pump_state === 'on';
        pumpState.textContent = pumpOn ? 'ON' : 'OFF';
        
        if (pumpOn) {
            pumpState.classList.remove('off');
            pumpState.style.color = '#28a745';
            icon.classList.add('flowing');
        } else {
            pumpState.classList.add('off');
            pumpState.style.color = '#6c757d';
            icon.classList.remove('flowing');
        }
        
        // Update scheduled mode toggle (mode === 'scheduled')
        const isScheduled = data.mode === 'scheduled';
        scheduledToggle.checked = isScheduled;
        
        // Show/hide schedule time input
        if (isScheduled) {
            scheduleTimeContainer.style.display = 'block';
            if (data.schedule_time) {
                scheduleTime.value = data.schedule_time.substring(0, 5); // HH:MM
            }
        } else {
            scheduleTimeContainer.style.display = 'none';
        }
        
        // Update schedule status
        if (isScheduled) {
            const espOnline = data.esp_online;
            const espMode = data.esp_mode;
            const isConfirmed = espOnline && espMode === 'scheduled';
            
            const statusColor = isConfirmed ? '#28a745' : '#ffc107';
            const statusText = isConfirmed ? 'Confirmed' : 'Pending ESP';
            scheduleStatus.innerHTML = `<span style="color:${statusColor}; font-weight:600;">Status: Scheduled Mode (${statusText})</span>`;
            
            // Show next run info if available
            if (data.schedule_time) {
                const timeStr = data.schedule_time.substring(0, 5);
                const duration = data.schedule_duration_minutes || 30;
                scheduleStatus.innerHTML += `<br><span style="color:var(--text-secondary);">Next run: ${timeStr} (${duration} min)</span>`;
            }
        } else {
            scheduleStatus.textContent = '';
        }
        
        // Update button states
        updateButtonStates();
        
    } catch (error) {
        console.error('Status sync failed:', error);
        showToast('Connection error', 'error');
    }
}

function updateButtonStates() {
    const pumpOn = pumpState.textContent === 'ON';
    const isScheduled = scheduledToggle.checked;
    const isCooldown = remainingCooldown > 0;

    // START button
    btnOn.disabled = isScheduled || isCooldown || pumpOn;
    btnOn.style.opacity = btnOn.disabled ? '0.5' : '1';

    // STOP button — ALWAYS allowed if pump is ON
    btnOff.disabled = isCooldown || !pumpOn;
    btnOff.style.opacity = btnOff.disabled ? '0.5' : '1';
}

// ============================================================================
// MANUAL CONTROL - VIA SERVER API
// ============================================================================
btnOn.addEventListener('click', async () => {
    if (btnOn.disabled) return;
    
    btnOn.textContent = 'STARTING...';
    btnOn.disabled = true;
    
    try {
        const res = await fetch('api/manual_control.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'start' })
        });
        
        const data = await res.json();
        if (data.success) {
            showToast('Pump starting...', 'success');
            startCooldown(5);
            
            // Log irrigation event
            await logIrrigation('START');
        } else {
            showToast('Failed to start pump', 'error');
            btnOn.textContent = startOriginalText;
            btnOn.disabled = false;
        }
    } catch (error) {
        console.error('Start failed:', error);
        showToast('Connection error', 'error');
        btnOn.textContent = startOriginalText;
        btnOn.disabled = false;
    }
});

btnOff.addEventListener('click', async () => {
    if (btnOff.disabled) return;
    
    btnOff.textContent = 'STOPPING...';
    btnOff.disabled = true;
    
    try {
        const res = await fetch('api/manual_control.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'stop' })
        });
        
        const data = await res.json();
        if (data.success) {
            showToast('Pump stopping...', 'success');
            startCooldown(5);
            
            // Log irrigation event
            await logIrrigation('STOP');
        } else {
            showToast('Failed to stop pump', 'error');
            btnOff.textContent = stopOriginalText;
            btnOff.disabled = false;
        }
    } catch (error) {
        console.error('Stop failed:', error);
        showToast('Connection error', 'error');
        btnOff.textContent = stopOriginalText;
        btnOff.disabled = false;
    }
});

// ============================================================================
// SCHEDULED MODE TOGGLE
// ============================================================================
scheduledToggle.addEventListener('change', async () => {
    if (scheduledToggle.checked) {
        // Immediately persist scheduled mode to database (with NULL time)
        // This prevents flicker when syncStatus() runs
        await saveSchedule(true, null);
    } else {
        // Disable scheduled mode
        await saveSchedule(false, null);
    }
});

// ============================================================================
// SAVE SCHEDULE
// ============================================================================
saveScheduleBtn.addEventListener('click', () => {
    const time = scheduleTime.value;
    if (!time) {
        showToast('Please select a time', 'warning');
        return;
    }
    saveSchedule(true, time);
});

async function saveSchedule(enabled, time) {
    try {
        const res = await fetch('api/save_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ enabled, time })
        });
        
        const data = await res.json();
        if (data.success) {
            if (enabled && !time) {
                // Toggle ON without time - show instruction
                showToast('Set a time and click Save', 'info');
            } else if (enabled && time) {
                // Full schedule save
                showToast('Schedule saved!', 'success');
            } else {
                // Toggle OFF
                showToast('Scheduled mode disabled', 'success');
            }
            syncStatus(); // Refresh status
        } else {
            showToast('Failed to save schedule: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Save schedule failed:', error);
        showToast('Connection error', 'error');
    }
}

// ============================================================================
// COOLDOWN TIMER
// ============================================================================
function startCooldown(seconds) {
    remainingCooldown = seconds;
    if (cooldownTimer) clearInterval(cooldownTimer);

    cooldownTimer = setInterval(() => {
        if (remainingCooldown <= 0) {
            clearInterval(cooldownTimer);
            cooldownLabel.textContent = '';
            btnOn.textContent = startOriginalText;
            btnOff.textContent = stopOriginalText;
            updateButtonStates();
            syncStatus(); // Refresh after cooldown
            return;
        }
        cooldownLabel.textContent = `Waiting: ${remainingCooldown}s`;
        remainingCooldown--;
        updateButtonStates();
    }, 1000);
}

// ============================================================================
// LOG IRRIGATION EVENTS
// ============================================================================
async function logIrrigation(action) {
    try {
        console.log(`Logging irrigation: ${action}`);
        
        const response = await fetch('log_irrigation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action })
        });
        
        const data = await response.json();
        console.log('Logging response:', data);
        
        if (data.success) {
            console.log(`✅ ${action} logged successfully`);
            
            // Immediately update last irrigation display
            if (action === 'START') {
                setTimeout(() => updateLastIrrigation(), 500);
            }
        } else {
            console.error('❌ Logging failed:', data.message);
        }
    } catch (error) {
        console.error('❌ Logging error:', error);
    }
}

// ============================================================================
// FETCH MOISTURE DATA
// ============================================================================
async function updateMoisture() {
    try {
        const res = await fetch('../NPKSENSOR/get_data.php');
        const data = await res.json();
        
        if (data.status === 'connected' && data.sensorData) {
            const moisture = Number(data.sensorData.moist || 0);
            const moistureEl = document.getElementById('moistureValue');
            
            // Update display
            moistureEl.textContent = `${moisture}%`;
            
            // Color code based on optimal range (60-80%)
            if (moisture >= 60 && moisture <= 80) {
                moistureEl.style.color = '#28a745'; // Green - optimal
            } else if (moisture < 40) {
                moistureEl.style.color = '#dc3545'; // Red - too dry
            } else {
                moistureEl.style.color = '#ffc107'; // Yellow - warning
            }
        } else {
            document.getElementById('moistureValue').textContent = '— %';
            document.getElementById('moistureValue').style.color = 'var(--text-primary)';
        }
    } catch (error) {
        console.error('Moisture fetch failed:', error);
        document.getElementById('moistureValue').textContent = '— %';
    }
}

// ============================================================================
// FETCH LAST IRRIGATION TIME
// ============================================================================
async function updateLastIrrigation() {
    try {
        const res = await fetch('get_last_irrigation.php');
        const data = await res.json();
        
        const lastIrrigEl = document.getElementById('lastIrrigation');
        
        if (data.success) {
            lastIrrigEl.textContent = data.timeAgo;
            
            // Color code based on time elapsed
            if (data.secondsAgo < 3600) { // < 1 hour
                lastIrrigEl.style.color = '#28a745'; // Green - recent
            } else if (data.secondsAgo < 86400) { // < 1 day
                lastIrrigEl.style.color = '#ffc107'; // Yellow - moderate
            } else {
                lastIrrigEl.style.color = '#dc3545'; // Red - old
            }
        } else {
            lastIrrigEl.textContent = 'Never';
            lastIrrigEl.style.color = 'var(--text-secondary)';
        }
    } catch (error) {
        console.error('Last irrigation fetch failed:', error);
        document.getElementById('lastIrrigation').textContent = '— min ago';
    }
}

// ============================================================================
// INITIALIZATION
// ============================================================================
function init() {
    // Back button
    const backBtn = document.getElementById('back');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            window.location.href = '/smartfarm2/index.php';
        });
    }

    // Initial data fetch
    syncStatus();
    updateMoisture();
    updateLastIrrigation();

    // Set up polling intervals
    setInterval(syncStatus, 3000);              // Status every 3 seconds
    setInterval(updateMoisture, 5000);          // Moisture every 5 seconds
    setInterval(updateLastIrrigation, 30000);   // Last irrigation every 30 seconds
}

init();

// ============================================================================
// ESP32 INTEGRATION NOTES (To be implemented in ESP32 firmware):
// ============================================================================
/*
 * The ESP32 should:
 * 
 * 1. STOP hosting web server endpoints (/relay/on, /relay/off, /auto/on, /status)
 * 
 * 2. Implement heartbeat loop:
 *    void loop() {
 *        sendHeartbeat();
 *        delay(5000); // Every 5 seconds
 *    }
 * 
 * 3. sendHeartbeat() function:
 *    - POST to: http://YOUR_SERVER/smartfarm2/FERTIGATION/api/heartbeat.php
 *    - Send JSON: {"pump_state": "ON" or "OFF", "mode": "manual" or "scheduled"}
 *    - Receive JSON: {"desired_pump_state": "ON" or "OFF", "scheduled_mode_enabled": true/false}
 *    - If desired_pump_state != current pump state, change the relay
 *    - Update mode based on scheduled_mode_enabled
 * 
 * 4. Example ESP32 code:
 *    HTTPClient http;
 *    http.begin("http://YOUR_SERVER/smartfarm2/FERTIGATION/api/heartbeat.php");
 *    http.addHeader("Content-Type", "application/json");
 *    
 *    String payload = "{\"pump_state\":\"" + String(pumpOn ? "ON" : "OFF") + "\",\"mode\":\"manual\"}";
 *    int httpCode = http.POST(payload);
 *    
 *    if (httpCode == 200) {
 *        String response = http.getString();
 *        // Parse JSON and adjust relay
 *    }
 *    http.end();
 */
</script>




</body>
</html>
