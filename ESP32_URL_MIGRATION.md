# SmartFarm ESP32 Firmware URL Migration Guide

This document lists all URL endpoint migrations between the legacy PHP backend (`smartfarm2`) and the modern Next.js 14 backend (`smartfarm3`).

---

## Base URLs

- **Legacy Local/Vercel (PHP)**: `http://<server-ip>/smartfarm2/` or `https://smartfarm.vercel.app/`
- **Next.js 14 API Base**: `https://<your-vercel-domain>.vercel.app/api/` (or `http://<local-ip>:3000/api/`)

---

## Authentication for ESP32

All ESP32 endpoints accept an optional header or query parameter for secure communication:
- **Header**: `X-API-Key: your_esp32_secret_api_key`
- **Query Parameter**: `?api_key=your_esp32_secret_api_key`

---

## Endpoint Mapping Table

| Device / Function | Old PHP Endpoint | New Next.js 14 Endpoint | HTTP Method | Payload Format |
|---|---|---|---|---|
| **Heartbeat (All Devices)** | `api/heartbeat.php` | `/api/heartbeat` or `/api/esp32/heartbeat` | `POST` | `{"device": "fan" \| "fertigation" \| "solar"}` |
| **Fertigation Status Poll** | `api/fertigation/get_status.php` | `/api/fertigation/status` or `/api/esp32/fertigation-poll` | `GET` | *(None)* |
| **Fertigation Manual Control** | `api/fertigation/manual_control.php` | `/api/fertigation/manual-control` | `POST` | `{"action": "on" \| "off"}` |
| **Fertigation Log** | `api/fertigation/log_irrigation.php` | `/api/fertigation/log` | `POST` | `{"action": "START" \| "STOP", "source": "esp32"}` |
| **Fertigation Set Mode** | `api/fertigation/set_mode.php` | `/api/fertigation/mode` | `POST` | `{"mode": "manual" \| "schedule" \| "auto"}` |
| **Fan Status Poll** | `api/fan/get_status.php` | `/api/fan/status` or `/api/esp32/fan-poll` | `GET` | *(None)* |
| **Fan Manual Control** | `api/fan/manual_control.php` | `/api/fan/manual-control` | `POST` | `{"action": "on" \| "off"}` |
| **Fan Log** | `api/fan/log_fan.php` | `/api/fan/log` | `POST` | `{"action": "START" \| "STOP"}` |
| **Solar Status Poll** | `api/solar/get_panel_ui_status.php` | `/api/solar/status` or `/api/esp32/solar-poll` | `GET` | *(None)* |
| **Solar Status Update** | `SOLARPANEL/update_panel_status.php` | `/api/solar/status` | `POST` | `{"voltage": 12.4, "current": 1.2, "power": 14.88}` |
| **Temp & Humidity Ingest** | `TEMPHUMIDITYSENSOR/receive_data.php` | `/api/environment/temp-humid` | `POST` | `{"temperature": 27.5, "humidity": 65.2}` |
| **Light Intensity Ingest** | `LIGHTINTENSITYSENSOR/receive_data.php` | `/api/environment/light` | `POST` | `{"lux": 540.0}` |
| **NPK Soil Ingest** | `NPKSENSOR/get_data.php` (POST) | `/api/npk/data` | `POST` | `{"nitrogen": 45, "phosphorus": 30, "potassium": 55}` |

---

## ESP32 C++ Code Migration Examples

### 1. Temperature & Humidity Ingestion (HTTP POST)

**Legacy PHP Arduino Snippet:**
```cpp
http.begin(client, "http://192.168.1.100/smartfarm2/TEMPHUMIDITYSENSOR/receive_data.php");
http.addHeader("Content-Type", "application/x-www-form-urlencoded");
String postData = "temperature=" + String(temp) + "&humidity=" + String(hum);
int httpCode = http.POST(postData);
```

**New Next.js 14 Snippet:**
```cpp
http.begin(client, "https://smartfarm3.vercel.app/api/environment/temp-humid");
http.addHeader("Content-Type", "application/json");
http.addHeader("X-API-Key", "your_esp32_secret_api_key");
String jsonPayload = "{\"temperature\":" + String(temp) + ",\"humidity\":" + String(hum) + "}";
int httpCode = http.POST(jsonPayload);
```

---

### 2. Fertigation Pump Status Polling (HTTP GET)

**Legacy PHP Arduino Snippet:**
```cpp
http.begin(client, "http://192.168.1.100/smartfarm2/api/fertigation/get_status.php");
int httpCode = http.GET();
String payload = http.getString();
```

**New Next.js 14 Snippet:**
```cpp
http.begin(client, "https://smartfarm3.vercel.app/api/fertigation/status");
http.addHeader("X-API-Key", "your_esp32_secret_api_key");
int httpCode = http.GET();
String payload = http.getString();
```

---

## Compatibility Note

For maximum backward-compatibility during testing, the Next.js API endpoints accept both `application/json` AND URL-encoded form data (`application/x-www-form-urlencoded`), as well as JSON response structures compatible with existing ESP32 parsers.
