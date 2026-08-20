import { NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format } from 'date-fns';

export async function GET() {
  try {
    let state = await prisma.fertigationControl.findUnique({
      where: { id: 1 },
    });

    if (!state) {
      state = await prisma.fertigationControl.create({
        data: {
          id: 1,
          mode: 'manual',
          desiredPumpState: 'off',
          espPumpState: 'off',
          scheduleDurationMinutes: 30,
        },
      });
    }

    const lastHb = state.lastHeartbeat ? new Date(state.lastHeartbeat).getTime() : 0;
    const now = Date.now();
    const isOnline = lastHb > 0 && (now - lastHb) <= 60000;

    return NextResponse.json({
      success: true,
      mode: state.mode || 'manual',
      desired_pump_state: state.desiredPumpState.toLowerCase(),
      actual_pump_state: state.espPumpState.toLowerCase(),
      esp_pump_state: state.espPumpState.toLowerCase(),
      esp_online: isOnline,
      schedule_time: state.scheduleTime ? format(new Date(state.scheduleTime), 'HH:mm') : null,
      schedule_stop_time: state.scheduleStopTime ? format(new Date(state.scheduleStopTime), 'HH:mm') : null,
      duration_minutes: state.scheduleDurationMinutes,
      config_version: state.configVersion,
      ack_config_version: state.ackConfigVersion,
      last_heartbeat: state.lastHeartbeat,
    });
  } catch (error: any) {
    console.error('Fertigation Status API Error:', error);
    return NextResponse.json({
      success: false,
      mode: 'manual',
      desired_pump_state: 'off',
      actual_pump_state: 'off',
      esp_online: false,
      error_message: error?.message,
    });
  }
}
