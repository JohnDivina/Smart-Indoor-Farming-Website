<?php
/**
 * External Weather API Endpoint
 * 
 * Fetches weather data from OpenWeatherMap API with 15-minute caching
 * Returns simplified JSON for dashboard use
 */

// Set timezone
date_default_timezone_set('Asia/Manila');

// Set JSON header
header('Content-Type: application/json');

// Include weather configuration
require_once __DIR__ . '/../config/weather.php';

// Define cache directory and file
$cacheDir = __DIR__ . '/../cache';
$cacheFile = $cacheDir . '/weather.json';

// Create cache directory if it doesn't exist
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

/**
 * Check if cache is valid
 */
function isCacheValid($cacheFile, $duration) {
    if (!file_exists($cacheFile)) {
        return false;
    }
    
    $cacheAge = time() - filemtime($cacheFile);
    return $cacheAge < $duration;
}

/**
 * Fetch weather data from OpenWeatherMap API
 */
function fetchWeatherData($lat, $lon, $apiKey) {
    $url = OPENWEATHER_API_URL . '?' . http_build_query([
        'lat' => $lat,
        'lon' => $lon,
        'appid' => $apiKey,
        'units' => 'metric'
    ]);
    
    // Use cURL for better error handling
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error || $httpCode !== 200) {
        error_log("OpenWeatherMap API Error: HTTP $httpCode - $error");
        return null;
    }
    
    return json_decode($response, true);
}

/**
 * Extract simplified weather data
 */
function simplifyWeatherData($data) {
    if (!$data || !isset($data['main']) || !isset($data['weather'])) {
        return null;
    }
    
    return [
        'temperature' => round($data['main']['temp'], 1),
        'humidity' => $data['main']['humidity'],
        'condition' => $data['weather'][0]['description'] ?? 'unknown',
        'wind_speed' => round($data['wind']['speed'] ?? 0, 2),
        'city' => $data['name'] ?? 'Unknown',
        'last_updated' => date('Y-m-d H:i:s')
    ];
}

// Main logic
try {
    // Check if cache is valid
    if (isCacheValid($cacheFile, WEATHER_CACHE_DURATION)) {
        // Return cached data
        $cachedData = file_get_contents($cacheFile);
        echo $cachedData;
        exit;
    }
    
    // Check if API key is configured
    if (OPENWEATHER_API_KEY === 'YOUR_OPENWEATHER_API_KEY_HERE') {
        echo json_encode([
            'error' => 'API key not configured. Please update config/weather.php'
        ]);
        exit;
    }
    
    // Fetch fresh data from OpenWeatherMap
    $weatherData = fetchWeatherData(WEATHER_LAT, WEATHER_LON, OPENWEATHER_API_KEY);
    
    if (!$weatherData) {
        echo json_encode([
            'error' => 'Unable to fetch weather data'
        ]);
        exit;
    }
    
    // Simplify the data
    $simplifiedData = simplifyWeatherData($weatherData);
    
    if (!$simplifiedData) {
        echo json_encode([
            'error' => 'Unable to process weather data'
        ]);
        exit;
    }
    
    // Save to cache
    $jsonData = json_encode($simplifiedData, JSON_PRETTY_PRINT);
    file_put_contents($cacheFile, $jsonData);
    
    // Return the data
    echo $jsonData;
    
} catch (Exception $e) {
    error_log("Weather API Error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Unable to fetch weather data'
    ]);
}
