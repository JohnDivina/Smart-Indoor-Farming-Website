import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import { solarCommandSchema } from '@/lib/validators';

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
    const parseResult = solarCommandSchema.safeParse(body);

    if (!parseResult.success) {
      return NextResponse.json({ success: false, message: 'Invalid command. Must be on or off.' }, { status: 400 });
    }

    const { action } = parseResult.data;
    const now = new Date();

    const updated = await prisma.solarPanelControl.upsert({
      where: { id: 1 },
      update: {
        desiredState: action,
        mode: 'manual',
        lastUpdated: now,
        configVersion: { increment: 1 },
      },
      create: {
        id: 1,
        desiredState: action,
        mode: 'manual',
        lastUpdated: now,
        configVersion: 1,
      },
    });

    return NextResponse.json({
      success: true,
      desired_state: action,
      mode: 'manual',
      config_version: updated.configVersion,
      message: `Solar panel power command: ${action.toUpperCase()}`,
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, message: error?.message || 'Command failed' }, { status: 500 });
  }
}
