import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format } from 'date-fns';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const { searchParams } = new URL(req.url);
    const actualState = searchParams.get('actual_state');
    const ackVersion = searchParams.get('ack_version') ? parseInt(searchParams.get('ack_version')!, 10) : null;

    const now = new Date();

    const updateData: any = {
      lastHeartbeat: now,
      lastUpdated: now,
    };

    if (actualState) {
      updateData.actualState = actualState.toLowerCase();
    }
    if (ackVersion !== null && !isNaN(ackVersion)) {
      updateData.ackConfigVersion = ackVersion;
    }

    const state = await prisma.solarPanelControl.upsert({
      where: { id: 1 },
      update: updateData,
      create: {
        id: 1,
        mode: 'manual',
        desiredState: 'off',
        actualState: actualState ? actualState.toLowerCase() : 'off',
        lastHeartbeat: now,
        lastUpdated: now,
      },
    });

    return NextResponse.json({
      success: true,
      desired_state: state.desiredState.toLowerCase(),
      mode: state.mode || 'manual',
      schedule_time: state.scheduleTime ? format(new Date(state.scheduleTime), 'HH:mm') : null,
      schedule_stop_time: state.scheduleStopTime ? format(new Date(state.scheduleStopTime), 'HH:mm') : null,
      config_version: state.configVersion,
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, error: error?.message }, { status: 500 });
  }
}
