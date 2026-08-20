import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format } from 'date-fns';

// GET latest temperature & humidity reading
export async function GET() {
  try {
    const latest = await prisma.tempHumiditySensor.findFirst({
      orderBy: { timestamp: 'desc' },
    });

    if (!latest) {
      return NextResponse.json({
        success: true,
        temperature: 26.5,
        humidity: 65.0,
        timestamp: format(new Date(), 'MMMM d, yyyy h:mm a'),
        status: 'disconnected',
      });
    }

    const ageSecs = (Date.now() - new Date(latest.timestamp).getTime()) / 1000;
    const isConnected = ageSecs <= 60;

    return NextResponse.json({
      success: true,
      temperature: Number(latest.temperature.toFixed(1)),
      humidity: Number(latest.humidity.toFixed(1)),
      timestamp: format(new Date(latest.timestamp), 'MMMM d, yyyy h:mm a'),
      status: isConnected ? 'connected' : 'disconnected',
      ageSecs: Math.round(ageSecs),
    });
  } catch (error: any) {
    console.error('Temp/Humid API Error:', error);
    return NextResponse.json({
      success: false,
      temperature: 0.0,
      humidity: 0.0,
      timestamp: '--',
      status: 'disconnected',
      error: error?.message,
    });
  }
}

// POST ingest sensor reading (from ESP32 or test)
export async function POST(req: NextRequest) {
  try {
    let temperature: number = 0;
    let humidity: number = 0;

    const contentType = req.headers.get('content-type') || '';

    if (contentType.includes('application/json')) {
      const body = await req.json();
      temperature = parseFloat(body.temperature);
      humidity = parseFloat(body.humidity);
    } else {
      const formData = await req.formData();
      temperature = parseFloat(formData.get('temperature') as string);
      humidity = parseFloat(formData.get('humidity') as string);
    }

    if (isNaN(temperature) || isNaN(humidity)) {
      return NextResponse.json({ success: false, message: 'Invalid temperature or humidity value' }, { status: 400 });
    }

    const now = new Date();

    // Insert into live table and history log
    const [entry] = await prisma.$transaction([
      prisma.tempHumiditySensor.create({
        data: {
          temperature,
          humidity,
          timestamp: now,
        },
      }),
      prisma.tempHumidityLog.create({
        data: {
          temperature,
          humidity,
          timestamp: now,
        },
      }),
    ]);

    return NextResponse.json({
      success: true,
      id: entry.id,
      temperature,
      humidity,
      timestamp: entry.timestamp,
    });
  } catch (error: any) {
    console.error('Ingest Temp/Humid Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Ingestion failed' }, { status: 500 });
  }
}
