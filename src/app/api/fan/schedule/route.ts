import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import { fanScheduleSchema } from '@/lib/validators';

export async function POST(req: NextRequest) {
  try {
    const session = await auth();
    if (session?.user?.isGuest) {
      return NextResponse.json({ success: false, message: 'Action disabled in guest mode' }, { status: 403 });
    }

    const body = await req.json();
    const parseResult = fanScheduleSchema.safeParse(body);

    if (!parseResult.success) {
      return NextResponse.json({ success: false, message: parseResult.error.errors[0]?.message || 'Invalid schedule' }, { status: 400 });
    }

    const { scheduleTime, scheduleStopTime, durationMinutes = 30 } = parseResult.data;

    // Convert "HH:MM" string to a Date object for PostgreSQL TIME field
    const [h1, m1] = scheduleTime.split(':').map(Number);
    const timeDate = new Date();
    timeDate.setHours(h1, m1, 0, 0);

    let stopTimeDate: Date | null = null;
    if (scheduleStopTime) {
      const [h2, m2] = scheduleStopTime.split(':').map(Number);
      stopTimeDate = new Date();
      stopTimeDate.setHours(h2, m2, 0, 0);
    }

    const now = new Date();

    const updated = await prisma.fanState.upsert({
      where: { id: 1 },
      update: {
        mode: 'schedule',
        scheduleTime: timeDate,
        scheduleStopTime: stopTimeDate,
        scheduleDurationMinutes: durationMinutes,
        lastUpdated: now,
        configVersion: { increment: 1 },
      },
      create: {
        id: 1,
        mode: 'schedule',
        scheduleTime: timeDate,
        scheduleStopTime: stopTimeDate,
        scheduleDurationMinutes: durationMinutes,
        lastUpdated: now,
        configVersion: 1,
      },
    });

    return NextResponse.json({
      success: true,
      message: 'Fan schedule saved successfully.',
      mode: 'schedule',
      schedule_time: scheduleTime,
      schedule_stop_time: scheduleStopTime || null,
      duration_minutes: durationMinutes,
      config_version: updated.configVersion,
    });
  } catch (error: any) {
    console.error('Fan Schedule Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Failed to save schedule' }, { status: 500 });
  }
}
