import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import { fertigationControlSchema } from '@/lib/validators';

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
    const parseResult = fertigationControlSchema.safeParse(body);

    if (!parseResult.success) {
      return NextResponse.json({ success: false, message: 'Invalid pump command. Must be on or off.' }, { status: 400 });
    }

    const { action } = parseResult.data;
    const now = new Date();

    const [updated] = await prisma.$transaction([
      prisma.fertigationControl.upsert({
        where: { id: 1 },
        update: {
          desiredPumpState: action,
          mode: 'manual',
          lastUpdated: now,
          configVersion: { increment: 1 },
        },
        create: {
          id: 1,
          desiredPumpState: action,
          mode: 'manual',
          lastUpdated: now,
          configVersion: 1,
        },
      }),
      prisma.fertigationState.upsert({
        where: { id: 1 },
        update: {
          desiredPumpState: action,
          mode: 'manual',
          lastUpdated: now,
          configVersion: { increment: 1 },
        },
        create: {
          id: 1,
          desiredPumpState: action,
          mode: 'manual',
          lastUpdated: now,
          configVersion: 1,
        },
      }),
      prisma.fertigationLog.create({
        data: {
          action: action === 'on' ? 'START' : 'STOP',
          timestamp: now,
        },
      }),
      prisma.irrigationLog.create({
        data: {
          action: action === 'on' ? 'START' : 'STOP',
          source: session?.user?.name || 'web-dashboard',
          timestamp: now,
        },
      }),
    ]);

    return NextResponse.json({
      success: true,
      desired_pump_state: action,
      mode: 'manual',
      config_version: updated.configVersion,
      message: `Fertigation pump command: ${action.toUpperCase()}`,
    });
  } catch (error: any) {
    console.error('Fertigation Manual Control Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Control command failed' }, { status: 500 });
  }
}
