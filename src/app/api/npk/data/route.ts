import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format } from 'date-fns';

export async function GET() {
  try {
    const latest = await prisma.npkSensor.findFirst({
      orderBy: { timestamp: 'desc' },
    });

    if (!latest) {
      return NextResponse.json({
        success: true,
        nitrogen: 45.0,
        phosphorus: 30.0,
        potassium: 50.0,
        timestamp: format(new Date(), 'MMMM d, yyyy h:mm a'),
        status: 'disconnected',
      });
    }

    const ageSecs = (Date.now() - new Date(latest.timestamp).getTime()) / 1000;
    const isConnected = ageSecs <= 60;

    return NextResponse.json({
      success: true,
      nitrogen: Number(latest.nitrogen.toFixed(1)),
      phosphorus: Number(latest.phosphorus.toFixed(1)),
      potassium: Number(latest.potassium.toFixed(1)),
      timestamp: format(new Date(latest.timestamp), 'MMMM d, yyyy h:mm a'),
      status: isConnected ? 'connected' : 'disconnected',
      ageSecs: Math.round(ageSecs),
    });
  } catch (error: any) {
    console.error('NPK Sensor API Error:', error);
    return NextResponse.json({
      success: false,
      nitrogen: 0,
      phosphorus: 0,
      potassium: 0,
      timestamp: '--',
      status: 'disconnected',
      error: error?.message,
    });
  }
}

export async function POST(req: NextRequest) {
  try {
    let nitrogen: number = 0;
    let phosphorus: number = 0;
    let potassium: number = 0;

    const contentType = req.headers.get('content-type') || '';

    if (contentType.includes('application/json')) {
      const body = await req.json();
      nitrogen = parseFloat(body.nitrogen);
      phosphorus = parseFloat(body.phosphorus);
      potassium = parseFloat(body.potassium);
    } else {
      const formData = await req.formData();
      nitrogen = parseFloat(formData.get('nitrogen') as string);
      phosphorus = parseFloat(formData.get('phosphorus') as string);
      potassium = parseFloat(formData.get('potassium') as string);
    }

    if (isNaN(nitrogen) || isNaN(phosphorus) || isNaN(potassium)) {
      return NextResponse.json({ success: false, message: 'Invalid NPK values' }, { status: 400 });
    }

    const now = new Date();

    const [entry] = await prisma.$transaction([
      prisma.npkSensor.create({
        data: {
          nitrogen,
          phosphorus,
          potassium,
          timestamp: now,
        },
      }),
      prisma.npkStatus.upsert({
        where: { id: 1 },
        update: { status: 'connected', updatedAt: now },
        create: { id: 1, status: 'connected', updatedAt: now },
      }),
    ]);

    return NextResponse.json({
      success: true,
      id: entry.id,
      nitrogen,
      phosphorus,
      potassium,
      timestamp: entry.timestamp,
    });
  } catch (error: any) {
    console.error('Ingest NPK Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Ingestion failed' }, { status: 500 });
  }
}
