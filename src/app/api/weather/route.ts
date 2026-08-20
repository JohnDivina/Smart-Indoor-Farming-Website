import { NextResponse } from 'next/server';

interface CachedWeather {
  data: any;
  timestamp: number;
}

let weatherCache: CachedWeather | null = null;
const CACHE_TTL_MS = 15 * 60 * 1000; // 15 minutes

export async function GET() {
  const now = Date.now();

  // Return cached data if still valid
  if (weatherCache && now - weatherCache.timestamp < CACHE_TTL_MS) {
    return NextResponse.json(weatherCache.data);
  }

  const apiKey = process.env.OPENWEATHER_API_KEY;

  // If no API key set, return realistic default weather data for CLSU Science City of Muñoz
  if (!apiKey) {
    const mockWeather = {
      success: true,
      city: 'Science City of Muñoz',
      province: 'Nueva Ecija, Philippines',
      temperature: 29.4,
      feelsLike: 33.2,
      humidity: 72,
      description: 'Partly Cloudy',
      icon: '02d',
      windSpeed: 3.6,
      uvIndex: 7.5,
      isMock: true,
    };
    weatherCache = { data: mockWeather, timestamp: now };
    return NextResponse.json(mockWeather);
  }

  try {
    // Muñoz, Nueva Ecija coordinates: 15.7432° N, 120.9416° E
    const url = `https://api.openweathermap.org/data/2.5/weather?lat=15.7432&lon=120.9416&units=metric&appid=${apiKey}`;
    const res = await fetch(url, { next: { revalidate: 900 } });

    if (!res.ok) throw new Error(`Weather API returned ${res.status}`);

    const data = await res.json();

    const weatherData = {
      success: true,
      city: 'Science City of Muñoz',
      province: 'Nueva Ecija',
      temperature: Number(data.main.temp.toFixed(1)),
      feelsLike: Number(data.main.feels_like.toFixed(1)),
      humidity: data.main.humidity,
      description: data.weather[0]?.description || 'Clear Sky',
      icon: data.weather[0]?.icon || '01d',
      windSpeed: data.wind?.speed || 0,
      isMock: false,
    };

    weatherCache = { data: weatherData, timestamp: now };
    return NextResponse.json(weatherData);
  } catch (error: any) {
    console.error('Weather API Error:', error);
    return NextResponse.json({
      success: true,
      city: 'Science City of Muñoz',
      temperature: 28.5,
      humidity: 70,
      description: 'Scattered Clouds',
      icon: '03d',
      isMock: true,
    });
  }
}
