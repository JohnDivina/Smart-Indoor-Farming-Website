import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';

export async function GET(req: NextRequest) {
  return NextResponse.json({ success: true, timestamp: new Date().toISOString() });
}

export async function POST(req: NextRequest) {
  try {
    let device = 'all';
    const contentType = req.headers.get('content-type') || '';

    if (contentType.includes('application/json')) {
      const body = await req.json().catch(() => ({}));
      device = body.device || 'all';
    } else {
      const formData = await req.formData().catch(() => null);
      if (formData) {
        device = (formData.get('device') as string) || 'all';
      }
    }

    const now = new Date();

    if (device === 'fan' || device === 'all') {
      await prisma.fanState.upsert({
        where: { id: 1 },
        update: { lastHeartbeat: now, lastUpdated: now },
        create: { id: 1, lastHeartbeat: now, lastUpdated: now },
      });
      await prisma.fanControl.upsert({
        where: { id: 1 },
        update: { lastHeartbeat: now, lastUpdated: now },
        create: { id: 1, lastHeartbeat: now, lastUpdated: now },
      });
    }

    if (device === 'fertigation' || device === 'all') {
      await prisma.fertigationControl.upsert({
        where: { id: 1 },
        update: { lastHeartbeat: now, lastUpdated: now },
        create: { id: 1, lastHeartbeat: now, lastUpdated: now },
      });
      await prisma.fertigationState.upsert({
        where: { id: 1 },
        update: { lastHeartbeat: now, lastUpdated: now },
        create: { id: 1, lastHeartbeat: now, lastUpdated: now },
      });
    }

    if (device === 'solar' || device === 'all') {
      await prisma.solarPanelControl.upsert({
        where: { id: 1 },
        update: { lastHeartbeat: now, lastUpdated: now },
        create: { id: 1, lastHeartbeat: now, lastUpdated: now },
      });
    }

    return NextResponse.json({
      success: true,
      message: `Heartbeat recorded for device: ${device}`,
      timestamp: now.toISOString(),
    });
  } catch (error: any) {
    console.error('Heartbeat API Error:', error);
    return NextResponse.json({ success: false, error: error?.message }, { status: 500 });
  }
}
