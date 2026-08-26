import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import { fanControlSchema } from '@/lib/validators';

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
    const parseResult = fanControlSchema.safeParse(body);

    if (!parseResult.success) {
      return NextResponse.json({ success: false, message: 'Invalid action. Must be on or off.' }, { status: 400 });
    }

    const { action } = parseResult.data;
    const now = new Date();

    const updated = await prisma.$transaction([
      prisma.fanState.upsert({
        where: { id: 1 },
        update: {
          desiredFanState: action,
          mode: 'manual',
          lastUpdated: now,
          configVersion: { increment: 1 },
        },
        create: {
          id: 1,
          desiredFanState: action,
          mode: 'manual',
          lastUpdated: now,
          configVersion: 1,
        },
      }),
      prisma.fanLog.create({
        data: {
          action: action === 'on' ? 'START' : 'STOP',
          timestamp: now,
        },
      }),
    ]);

    return NextResponse.json({
      success: true,
      desired_fan_state: action,
      mode: 'manual',
      config_version: updated[0].configVersion,
      message: `Auxiliary fan requested to turn ${action.toUpperCase()}`,
    });
  } catch (error: any) {
    console.error('Fan Manual Control Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Control update failed' }, { status: 500 });
  }
}
