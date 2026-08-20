import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format } from 'date-fns';

export async function GET() {
  try {
    const logs = await prisma.fanLog.findMany({
      orderBy: { timestamp: 'desc' },
      take: 20,
    });

    return NextResponse.json({
      success: true,
      logs: logs.map((l) => ({
        id: l.id,
        action: l.action,
        timestamp: l.timestamp ? format(new Date(l.timestamp), 'MMM d, yyyy h:mm:ss a') : '--',
      })),
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, logs: [] });
  }
}

export async function POST(req: NextRequest) {
  try {
    const body = await req.json().catch(() => ({}));
    const action = (body.action || 'START').toUpperCase();

    const entry = await prisma.fanLog.create({
      data: {
        action,
        timestamp: new Date(),
      },
    });

    return NextResponse.json({ success: true, id: entry.id, action: entry.action });
  } catch (error: any) {
    return NextResponse.json({ success: false, error: error?.message }, { status: 500 });
  }
}
