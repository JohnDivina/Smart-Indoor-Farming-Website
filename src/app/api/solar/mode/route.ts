import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import { solarModeSchema } from '@/lib/validators';

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
    const parseResult = solarModeSchema.safeParse(body);

    if (!parseResult.success) {
      return NextResponse.json({ success: false, message: 'Invalid mode' }, { status: 400 });
    }

    const { mode } = parseResult.data;
    const now = new Date();

    await prisma.solarPanelControl.upsert({
      where: { id: 1 },
      update: { mode, lastUpdated: now, configVersion: { increment: 1 } },
      create: { id: 1, mode, lastUpdated: now, configVersion: 1 },
    });

    return NextResponse.json({ success: true, mode });
  } catch (error: any) {
    return NextResponse.json({ success: false, message: error?.message || 'Error updating mode' }, { status: 500 });
  }
}
