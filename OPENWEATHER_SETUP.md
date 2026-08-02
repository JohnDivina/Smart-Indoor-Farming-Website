# OpenWeatherMap API Integration

This Smart Farm dashboard integrates external weather data from OpenWeatherMap API with secure server-side implementation and caching.

## 🔧 Setup Instructions

### Step 1: Get Your API Key

1. Go to [OpenWeatherMap](https://openweathermap.org/api)
2. Sign up for a free account
3. Navigate to "API keys" section
4. Copy your API key

### Step 2: Configure API Key

1. Open: `config/weather.php`
2. Replace `YOUR_OPENWEATHER_API_KEY_HERE` with your actual API key:

```php
define('OPENWEATHER_API_KEY', 'your_actual_api_key_here');
```

3. Save the file

### Step 3: Test the Integration

Visit: `http://localhost/smartfarm2/api/get_external_weather.php`

You should see JSON output like:

```json
{
  "temperature": 29.5,
  "humidity": 70,
  "condition": "broken clouds",
  "wind_speed": 4.02,
  "city": "Santa Cruz",
  "last_updated": "2026-02-11 14:15:00"
}
```

## 📁 File Structure

```
smartfarm2/
├── config/
│   └── weather.php          # API key and configuration
├── api/
│   └── get_external_weather.php  # Weather endpoint
└── cache/
    ├── .gitignore           # Excludes cache from git
    └── weather.json         # Cached weather data (auto-generated)
```

## 🔒 Security Features

✅ **API key stored server-side only** - Never exposed to frontend JavaScript
✅ **Secure configuration** - API key in separate config file
✅ **Error handling** - Errors don't expose sensitive information
✅ **Fixed coordinates** - User input not allowed (prevents abuse)

## ⚡ Caching System

- **Cache Duration**: 15 minutes (900 seconds)
- **Cache Location**: `/cache/weather.json`
- **Logic**:
  - First request: Fetches from OpenWeatherMap (slower)
  - Subsequent requests within 15 minutes: Returns cached data (instant)
  - After 15 minutes: Re-fetches fresh data

## 🌍 Location

The weather data is fetched for:
- **Latitude**: 14.5955
- **Longitude**: 120.9844
- **Location**: Santa Cruz, Laguna (CLSU area)

To change the location, edit `config/weather.php`:

```php
define('WEATHER_LAT', 14.5955);  // Your latitude
define('WEATHER_LON', 120.9844); // Your longitude
```

## 📊 API Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `temperature` | float | Temperature in Celsius |
| `humidity` | integer | Humidity percentage |
| `condition` | string | Weather condition description |
| `wind_speed` | float | Wind speed in m/s |
| `city` | string | City name |
| `last_updated` | string | Last fetch timestamp |

## 🔧 Troubleshooting

### Error: "API key not configured"

**Solution**: Update `config/weather.php` with your actual API key

### Error: "Unable to fetch weather data"

**Possible causes**:
1. Invalid API key
2. No internet connection
3. OpenWeatherMap API is down
4. API rate limit exceeded (free tier: 60 calls/minute)

**Solution**: Check PHP error log for details:
- Windows XAMPP: `C:\xampp\apache\logs\error.log`

### Cache not updating

**Solution**: Manually delete `cache/weather.json` to force refresh

## 💡 Usage in Dashboard

To use this weather data in your dashboard, make an AJAX call:

```javascript
fetch('/smartfarm2/api/get_external_weather.php')
  .then(response => response.json())
  .then(data => {
    if (data.error) {
      console.error('Weather error:', data.error);
      return;
    }
    
    // Use the weather data
    console.log('Temperature:', data.temperature + '°C');
    console.log('Humidity:', data.humidity + '%');
    console.log('Condition:', data.condition);
  });
```

## 📝 Notes

- Free tier allows 1,000 API calls per day
- Weather data updates every 15 minutes
- Cache file is automatically created on first request
- API key is never sent to the browser
