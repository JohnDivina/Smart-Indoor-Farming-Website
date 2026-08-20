import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format } from 'date-fns';

export async function GET() {
  try {
    const latest = await prisma.lightIntensitySensor.findFirst({
      orderBy: { timestamp: 'desc' },
    });

    if (!latest) {
      return NextResponse.json({
        success: true,
        lux: 650.0,
        timestamp: format(new Date(), 'MMMM d, yyyy h:mm a'),
        status: 'disconnected',
      });
    }

    const ageSecs = (Date.now() - new Date(latest.timestamp).getTime()) / 1000;
    const isConnected = ageSecs <= 60;

    return NextResponse.json({
      success: true,
      lux: Number(latest.lux.toFixed(0)),
      timestamp: format(new Date(latest.timestamp), 'MMMM d, yyyy h:mm a'),
      status: isConnected ? 'connected' : 'disconnected',
      ageSecs: Math.round(ageSecs),
    });
  } catch (error: any) {
    console.error('Light Sensor API Error:', error);
    return NextResponse.json({
      success: false,
      lux: 0,
      timestamp: '--',
      status: 'disconnected',
      error: error?.message,
    });
  }
}

export async function POST(req: NextRequest) {
  try {
    let lux: number = 0;
    const contentType = req.headers.get('content-type') || '';

    if (contentType.includes('application/json')) {
      const body = await req.json();
      lux = parseFloat(body.lux);
    } else {
      const formData = await req.formData();
      lux = parseFloat(formData.get('lux') as string);
    }

    if (isNaN(lux)) {
      return NextResponse.json({ success: false, message: 'Invalid lux value' }, { status: 400 });
    }

    const now = new Date();

    const [entry] = await prisma.$transaction([
      prisma.lightIntensitySensor.create({
        data: {
          lux,
          timestamp: now,
        },
      }),
      prisma.liveLightReading.create({
        data: {
          lux,
          timestamp: now,
        },
      }),
      prisma.lightStatus.upsert({
        where: { id: 1 },
        update: { status: 'connected', updatedAt: now },
        create: { id: 1, status: 'connected', updatedAt: now },
      }),
    ]);

    return NextResponse.json({
      success: true,
      id: entry.id,
      lux,
      timestamp: entry.timestamp,
    });
  } catch (error: any) {
    console.error('Ingest Light Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Ingestion failed' }, { status: 500 });
  }
}
