import { NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format } from 'date-fns';

export async function GET() {
  try {
    let state = await prisma.fanState.findUnique({
      where: { id: 1 },
    });

    if (!state) {
      state = await prisma.fanState.create({
        data: {
          id: 1,
          mode: 'manual',
          desiredFanState: 'off',
          espFanState: 'off',
          scheduleDurationMinutes: 30,
        },
      });
    }

    const lastHb = state.lastHeartbeat ? new Date(state.lastHeartbeat).getTime() : 0;
    const now = Date.now();
    const isOnline = lastHb > 0 && (now - lastHb) <= 60000; // 60s timeout

    const currentState = isOnline && state.espFanState
      ? state.espFanState.toLowerCase()
      : state.desiredFanState.toLowerCase();

    return NextResponse.json({
      success: true,
      esp_fan_state: currentState,
      desired_fan_state: state.desiredFanState.toLowerCase(),
      actual_fan_state: state.espFanState.toLowerCase(),
      mode: state.mode || 'manual',
      schedule_time: state.scheduleTime ? format(new Date(state.scheduleTime), 'HH:mm') : null,
      schedule_stop_time: state.scheduleStopTime ? format(new Date(state.scheduleStopTime), 'HH:mm') : null,
      duration_minutes: state.scheduleDurationMinutes,
      esp_online: isOnline,
      last_heartbeat: state.lastHeartbeat,
      config_version: state.configVersion,
      ack_config_version: state.ackConfigVersion,
    });
  } catch (error: any) {
    console.error('Fan Status API Error:', error);
    return NextResponse.json({
      success: false,
      esp_fan_state: 'off',
      mode: 'manual',
      esp_online: false,
      message: error?.message,
    });
  }
}
