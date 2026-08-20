import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format } from 'date-fns';

export async function GET() {
  try {
    let ctrl = await prisma.solarPanelControl.findUnique({
      where: { id: 1 },
    });

    if (!ctrl) {
      ctrl = await prisma.solarPanelControl.create({
        data: {
          id: 1,
          mode: 'manual',
          desiredState: 'off',
          actualState: 'off',
        },
      });
    }

    const latestTelemetry = await prisma.solarPanelStatus.findFirst({
      orderBy: { timestamp: 'desc' },
    });

    const lastHb = ctrl.lastHeartbeat ? new Date(ctrl.lastHeartbeat).getTime() : 0;
    const now = Date.now();
    const isOnline = lastHb > 0 && (now - lastHb) <= 60000;

    return NextResponse.json({
      success: true,
      mode: ctrl.mode || 'manual',
      desired_state: ctrl.desiredState.toLowerCase(),
      actual_state: ctrl.actualState.toLowerCase(),
      esp_online: isOnline,
      voltage: latestTelemetry?.voltage ?? 12.6,
      current: latestTelemetry?.current ?? 1.8,
      power: latestTelemetry?.power ?? 22.68,
      schedule_time: ctrl.scheduleTime ? format(new Date(ctrl.scheduleTime), 'HH:mm') : null,
      schedule_stop_time: ctrl.scheduleStopTime ? format(new Date(ctrl.scheduleStopTime), 'HH:mm') : null,
      config_version: ctrl.configVersion,
      last_heartbeat: ctrl.lastHeartbeat,
    });
  } catch (error: any) {
    console.error('Solar Status API Error:', error);
    return NextResponse.json({
      success: false,
      mode: 'manual',
      desired_state: 'off',
      actual_state: 'off',
      esp_online: false,
      error_message: error?.message,
    });
  }
}

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const voltage = parseFloat(body.voltage);
    const current = parseFloat(body.current);
    const power = parseFloat(body.power) || (voltage * current);

    const now = new Date();

    const [telemetry] = await prisma.$transaction([
      prisma.solarPanelStatus.create({
        data: {
          voltage: isNaN(voltage) ? null : voltage,
          current: isNaN(current) ? null : current,
          power: isNaN(power) ? null : power,
          timestamp: now,
        },
      }),
      prisma.solarPanelControl.upsert({
        where: { id: 1 },
        update: { lastHeartbeat: now, lastUpdated: now },
        create: { id: 1, lastHeartbeat: now, lastUpdated: now },
      }),
    ]);

    return NextResponse.json({ success: true, id: telemetry.id });
  } catch (error: any) {
    return NextResponse.json({ success: false, error: error?.message }, { status: 500 });
  }
}
