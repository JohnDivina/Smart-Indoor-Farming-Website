/*
 * FertigationController.ino
 * ─────────────────────────────────────────────────────────────────────────────
 * Solar-like autonomous fertigation schedule controller.
 *
 * Architecture:
 *   - ESP32 actively polls the server every POLL_INTERVAL_MS for config.
 *   - Schedule execution, manual control, and sensor_auto all happen locally.
 *   - Server only stores config (mode, schedule times, desired_pump_state).
 *   - After each action, ESP32 pushes its actual state back to the server.
 *   - Existing legacy routes (/status, /relay/on, /relay/off, /force_manual)
 *     are KEPT for testing/fallback during migration. They still work.
 *
 * Modes (received from server):
 *   manual      → match pump GPIO to desired_pump_state
 *   scheduled   → turn pump ON inside [schedule_time, schedule_stop_time],
 *                 OFF outside that window (using server_time for comparison)
 *   sensor_auto → run local moisture-sensor threshold logic
 *
 * Wiring:
 *   PUMP_RELAY_PIN  GPIO 26  (LOW = pump ON for active-low relay, adjust if needed)
 *   MOISTURE_PIN    GPIO 34  (ADC input, adjust threshold as needed)
 *
 * Configuration:
 *   Update WIFI_SSID, WIFI_PASSWORD, SERVER_IP, SERVER_PATH at the top.
 * ─────────────────────────────────────────────────────────────────────────────
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>   // Install via Library Manager: "ArduinoJson" by Benoit Blanchon
#include <WebServer.h>

// ─── Network & Server Config ────────────────────────────────────────────────
#define WIFI_SSID        "YOUR_WIFI_SSID"
#define WIFI_PASSWORD    "YOUR_WIFI_PASSWORD"
#define SERVER_IP        "192.168.0.109"          // adjust to your server IP
#define SERVER_PATH      "/smartfarm2/FERTIGATION" // path prefix on the server

// ─── Hardware Pins ──────────────────────────────────────────────────────────
#define PUMP_RELAY_PIN   26    // GPIO for relay (active-LOW assumed)
#define MOISTURE_PIN     34    // Analog ADC pin for soil moisture sensor

// ─── Timing ─────────────────────────────────────────────────────────────────
#define POLL_INTERVAL_MS 4000  // How often to poll server for config (ms)
#define HTTP_TIMEOUT_MS  5000  // HTTP request timeout

// ─── Sensor Auto Threshold ──────────────────────────────────────────────────
#define MOISTURE_DRY_THRESHOLD   2400  // ADC value below which soil is "too dry"
#define MOISTURE_WET_THRESHOLD   1800  // ADC value above which soil is "wet enough"

// ─── Global state ──────────────────────────────────────────────────────────
WebServer server(80);

String currentMode       = "manual";
String desiredPumpState  = "off";
String actualPumpState   = "off";
String scheduleTime      = "";    // "HH:MM"
String scheduleStopTime  = "";    // "HH:MM"
int    configVersion     = 0;
int    ackConfigVersion  = 0;

bool   sensorAutoRunning = false; // tracks moisture-driven ON state

// ─── Pump Control ───────────────────────────────────────────────────────────
void setPump(bool on, const char* reason = "") {
    bool level = on ? LOW : HIGH;  // active-LOW relay
    digitalWrite(PUMP_RELAY_PIN, level);
    actualPumpState = on ? "on" : "off";
    if (strlen(reason) > 0) {
        Serial.printf("[PUMP] %s  (%s)\n", on ? "ON" : "OFF", reason);
    }
}

// ─── Time Helpers ───────────────────────────────────────────────────────────
// Convert "HH:MM:SS" or "HH:MM" to minutes-since-midnight
int timeToMinutes(const String& t) {
    if (t.length() < 5) return -1;
    int h = t.substring(0, 2).toInt();
    int m = t.substring(3, 5).toInt();
    return h * 60 + m;
}

bool isInsideWindow(const String& serverTime, const String& start, const String& stop) {
    int nowMin   = timeToMinutes(serverTime);  // serverTime is "YYYY-MM-DD HH:MM:SS"
    // Extract HH:MM from the server_time string (chars 11–15)
    String hhmm = "";
    if (serverTime.length() >= 16) hhmm = serverTime.substring(11, 16);
    nowMin = timeToMinutes(hhmm);

    int startMin = timeToMinutes(start);
    int stopMin  = timeToMinutes(stop);
    if (nowMin < 0 || startMin < 0 || stopMin < 0) return false;

    if (startMin <= stopMin) {
        return nowMin >= startMin && nowMin < stopMin;
    } else {
        // Overnight window (e.g. 22:00 → 06:00)
        return nowMin >= startMin || nowMin < stopMin;
    }
}

// ─── Push Status to Server ──────────────────────────────────────────────────
void pushStatus(const String& extraMsg = "") {
    if (WiFi.status() != WL_CONNECTED) return;
    HTTPClient http;
    String url = "http://" + String(SERVER_IP) + SERVER_PATH + "/update_fert_status.php";
    http.begin(url);
    http.setTimeout(HTTP_TIMEOUT_MS);
    http.addHeader("Content-Type", "application/json");

    StaticJsonDocument<256> doc;
    doc["actual_pump_state"]  = actualPumpState;
    doc["wifi_status"]        = "connected";
    doc["ack_config_version"] = ackConfigVersion;
    if (extraMsg.length() > 0) doc["last_message"] = extraMsg;

    String body;
    serializeJson(doc, body);
    int code = http.POST(body);
    if (code > 0) Serial.printf("[PUSH] Status pushed: %s  HTTP %d\n", actualPumpState.c_str(), code);
    else          Serial.printf("[PUSH] Failed: %s\n", http.errorToString(code).c_str());
    http.end();
}

// ─── Poll Config from Server ─────────────────────────────────────────────────
void pollConfig() {
    if (WiFi.status() != WL_CONNECTED) return;
    HTTPClient http;
    String url = "http://" + String(SERVER_IP) + SERVER_PATH + "/get_fert_control.php";
    http.begin(url);
    http.setTimeout(HTTP_TIMEOUT_MS);
    int code = http.GET();
    if (code != 200) {
        Serial.printf("[POLL] HTTP error %d\n", code);
        http.end();
        return;
    }

    String payload = http.getString();
    http.end();

    StaticJsonDocument<512> doc;
    DeserializationError err = deserializeJson(doc, payload);
    if (err) { Serial.println("[POLL] JSON parse error"); return; }
    if (!doc["success"].as<bool>()) { Serial.println("[POLL] success=false"); return; }

    // ── Accept new config ──
    int newVer = doc["config_version"] | 0;
    bool configChanged = (newVer != configVersion);

    currentMode      = doc["mode"].as<String>();
    desiredPumpState = doc["desired_pump_state"].as<String>();
    scheduleTime     = doc["schedule_time"].as<String>();
    scheduleStopTime = doc["schedule_stop_time"].as<String>();
    configVersion    = newVer;
    ackConfigVersion = newVer;       // Acknowledge immediately

    String serverTime = doc["server_time"].as<String>();

    Serial.printf("[POLL] mode=%s cfg_ver=%d server_time=%s\n",
                  currentMode.c_str(), configVersion, serverTime.c_str());

    // ── Execute current mode ──
    if (currentMode == "manual") {
        bool wantOn = (desiredPumpState == "on");
        if (wantOn != (actualPumpState == "on")) {
            setPump(wantOn, "manual command");
            pushStatus("Manual " + String(wantOn ? "START" : "STOP"));
        } else if (configChanged) {
            pushStatus("ack manual cfg");
        }

    } else if (currentMode == "scheduled") {
        if (scheduleTime.length() > 0 && scheduleStopTime.length() > 0) {
            bool inWindow = isInsideWindow(serverTime, scheduleTime, scheduleStopTime);
            bool pumpOn   = (actualPumpState == "on");
            if (inWindow && !pumpOn) {
                setPump(true, "scheduled START");
                pushStatus("Scheduled window START");
            } else if (!inWindow && pumpOn) {
                setPump(false, "scheduled STOP");
                pushStatus("Scheduled window STOP");
            } else if (configChanged) {
                pushStatus("ack scheduled cfg");
            }
        } else {
            // No schedule saved — safety: turn off
            if (actualPumpState == "on") { setPump(false, "no schedule set"); pushStatus("No schedule"); }
        }

    } else if (currentMode == "sensor_auto") {
        // Moisture logic runs in the main loop via the sensorAutoLoop()
        if (configChanged) pushStatus("ack sensor_auto cfg");
    }
}

// ─── Sensor Auto Loop (only active when mode == sensor_auto) ─────────────────
void sensorAutoLoop() {
    if (currentMode != "sensor_auto") {
        if (sensorAutoRunning) {
            setPump(false, "sensor_auto exited");
            sensorAutoRunning = false;
        }
        return;
    }
    int moisture = analogRead(MOISTURE_PIN);
    Serial.printf("[SENSOR] moisture=%d  running=%s\n", moisture, sensorAutoRunning ? "true" : "false");

    if (!sensorAutoRunning && moisture > MOISTURE_DRY_THRESHOLD) {
        setPump(true, "sensor dry");
        sensorAutoRunning = true;
        pushStatus("Sensor AUTO: soil dry, pump ON");
    } else if (sensorAutoRunning && moisture < MOISTURE_WET_THRESHOLD) {
        setPump(false, "sensor wet");
        sensorAutoRunning = false;
        pushStatus("Sensor AUTO: soil wet, pump OFF");
    }
}

// ─── Legacy Routes (kept for testing/fallback) ──────────────────────────────
void handleStatus() {
    String json = "{\"success\":true,\"actual_pump_state\":\"" + actualPumpState +
                  "\",\"mode\":\"" + currentMode + "\",\"config_version\":" +
                  String(configVersion) + "}";
    server.send(200, "application/json", json);
}

void handleRelayOn() {
    setPump(true, "legacy /relay/on");
    desiredPumpState = "on";
    pushStatus("legacy relay ON");
    server.send(200, "application/json", "{\"success\":true,\"message\":\"Relay ON (legacy)\"}");
}

void handleRelayOff() {
    setPump(false, "legacy /relay/off");
    desiredPumpState = "off";
    pushStatus("legacy relay OFF");
    server.send(200, "application/json", "{\"success\":true,\"message\":\"Relay OFF (legacy)\"}");
}

void handleForceManual() {
    currentMode = "manual";
    setPump(false, "force_manual");
    pushStatus("Force manual via /force_manual");
    server.send(200, "application/json", "{\"success\":true,\"message\":\"Forced to manual mode, pump off\"}");
}

// ─── Setup ───────────────────────────────────────────────────────────────────
void setup() {
    Serial.begin(115200);
    pinMode(PUMP_RELAY_PIN, OUTPUT);
    setPump(false);  // Safe default

    // WiFi
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    Serial.print("Connecting to WiFi");
    unsigned long t0 = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - t0 < 15000) {
        delay(500); Serial.print(".");
    }
    Serial.println();
    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("WiFi connected: " + WiFi.localIP().toString());
    } else {
        Serial.println("WiFi failed — running in local mode");
    }

    // Legacy routes
    server.on("/status",       HTTP_GET,  handleStatus);
    server.on("/relay/on",     HTTP_GET,  handleRelayOn);
    server.on("/relay/off",    HTTP_GET,  handleRelayOff);
    server.on("/force_manual", HTTP_GET,  handleForceManual);
    server.begin();
    Serial.println("HTTP server started (legacy routes active)");

    // Initial poll
    pollConfig();
}

// ─── Loop ────────────────────────────────────────────────────────────────────
unsigned long lastPoll       = 0;
unsigned long lastSensorCheck = 0;

void loop() {
    server.handleClient();  // serve legacy routes

    unsigned long now = millis();

    // Poll server config every POLL_INTERVAL_MS
    if (now - lastPoll >= POLL_INTERVAL_MS) {
        lastPoll = now;
        pollConfig();
    }

    // Sensor auto check every 5 seconds (doesn't need to be as fast as poll)
    if (now - lastSensorCheck >= 5000) {
        lastSensorCheck = now;
        sensorAutoLoop();
    }
}
