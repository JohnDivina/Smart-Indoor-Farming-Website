import { NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format } from 'date-fns';

export async function GET() {
  try {
    const lastRun = await prisma.fanLog.findFirst({
      where: { action: 'START' },
      orderBy: { timestamp: 'desc' },
    });

    return NextResponse.json({
      success: true,
      last_run: lastRun?.timestamp ? format(new Date(lastRun.timestamp), 'MMMM d, yyyy h:mm a') : 'No records yet',
      timestamp: lastRun?.timestamp || null,
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, last_run: '--' });
  }
}
