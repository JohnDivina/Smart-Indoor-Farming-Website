import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import { fanScheduleSchema } from '@/lib/validators';

export async function POST(req: NextRequest) {
  try {
    const session = await auth();
    const userRole = session?.user?.role || (session?.user?.isGuest ? 'guest' : 'viewer');
    if (userRole !== 'admin' && userRole !== 'farm_manager') {
      return NextResponse.json(
        { success: false, message: 'Access denied: Master Admin or Farm Manager permission required.' },
        { status: 403 }
      );
    }

    const body = await req.json();
    const parseResult = fanScheduleSchema.safeParse(body);

    if (!parseResult.success) {
      return NextResponse.json({ success: false, message: 'Invalid schedule parameters' }, { status: 400 });
    }

    const { scheduleTime, scheduleStopTime } = parseResult.data;

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

    const updated = await prisma.solarPanelControl.upsert({
      where: { id: 1 },
      update: {
        mode: 'schedule',
        scheduleTime: timeDate,
        scheduleStopTime: stopTimeDate,
        lastUpdated: now,
        configVersion: { increment: 1 },
      },
      create: {
        id: 1,
        mode: 'schedule',
        scheduleTime: timeDate,
        scheduleStopTime: stopTimeDate,
        lastUpdated: now,
        configVersion: 1,
      },
    });

    return NextResponse.json({
      success: true,
      mode: 'schedule',
      schedule_time: scheduleTime,
      schedule_stop_time: scheduleStopTime || null,
      config_version: updated.configVersion,
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, message: error?.message || 'Failed to save schedule' }, { status: 500 });
  }
}
