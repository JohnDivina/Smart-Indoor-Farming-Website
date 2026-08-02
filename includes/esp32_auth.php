<?php
// includes/esp32_auth.php
// Configuration for ESP32 Shared-Secret Authentication

// A strong shared secret for device authentication
define('ESP32_SECRET', 'CHANGE_ME_TO_A_STRONG_RANDOM_SECRET_KEY');

// Toggle for enforcement. MUST BE FALSE UNTIL ALL DEVICES ARE FLASHED!
define('ENFORCE_ESP32_AUTH', false);

/**
 * Validates the X-ESP32-Secret HTTP header.
 * If ENFORCE_ESP32_AUTH is false, it always returns true but logs the attempt.
 * If ENFORCE_ESP32_AUTH is true, it exits and returns 401/403 on failure.
 */
function verify_esp32_auth() {
    $header_secret = '';
    
    // Check various ways the header might be sent
    if (isset($_SERVER['HTTP_X_ESP32_SECRET'])) {
        $header_secret = trim($_SERVER['HTTP_X_ESP32_SECRET']);
    } else {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        if (isset($headers['X-ESP32-Secret'])) {
            $header_secret = trim($headers['X-ESP32-Secret']);
        } elseif (isset($headers['X-Esp32-Secret'])) {
            $header_secret = trim($headers['X-Esp32-Secret']);
        } elseif (isset($headers['x-esp32-secret'])) {
            $header_secret = trim($headers['x-esp32-secret']);
        }
    }
    
    $endpoint = basename($_SERVER['SCRIPT_NAME'] ?? 'Unknown Endpoint');
    $is_present = !empty($header_secret);
    $is_valid = false;
    
    if ($is_present) {
        $is_valid = hash_equals(ESP32_SECRET, $header_secret);
    }
    
    // Logging behavior
    if ($is_present) {
        if ($is_valid) {
            error_log("[ESP32 Auth] Valid secret received on $endpoint");
        } else {
            error_log("[ESP32 Auth] INVALID secret received on $endpoint");
        }
    } else {
        error_log("[ESP32 Auth] Missing secret header on $endpoint");
    }

    // Enforcement behavior
    if (ENFORCE_ESP32_AUTH) {
        if (!$is_present || !$is_valid) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized Device']);
            exit();
        }
    }
    
    // If not enforcing, or if valid
    return true;
}
?>
