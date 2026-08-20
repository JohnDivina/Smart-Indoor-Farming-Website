import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format, subDays, startOfDay, endOfDay } from 'date-fns';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const { searchParams } = new URL(req.url);
    const dateParam = searchParams.get('date');
    const limitParam = parseInt(searchParams.get('limit') || '50', 10);

    let whereClause: any = {};

    if (dateParam) {
      const targetDate = new Date(dateParam.includes('T') ? dateParam : `${dateParam}T00:00:00`);
      whereClause = {
        timestamp: {
          gte: startOfDay(targetDate),
          lte: endOfDay(targetDate),
        },
      };
    } else {
      whereClause = {
        timestamp: {
          gte: subDays(new Date(), 1),
        },
      };
    }

    const readings = await prisma.lightIntensitySensor.findMany({
      where: whereClause,
      orderBy: { timestamp: 'asc' },
      take: Math.min(limitParam, 500),
    });

    const labels: string[] = [];
    const lux: number[] = [];

    readings.forEach((r) => {
      labels.push(format(new Date(r.timestamp), 'h:mm a'));
      lux.push(Math.round(r.lux));
    });

    return NextResponse.json({
      success: true,
      labels,
      lux,
      count: readings.length,
      raw: readings.map((r) => ({
        id: r.id,
        lux: r.lux,
        timestamp: format(new Date(r.timestamp), 'yyyy-MM-dd HH:mm:ss'),
      })),
    });
  } catch (error: any) {
    console.error('Light Chart API Error:', error);
    return NextResponse.json({
      success: false,
      labels: [],
      lux: [],
      error: error?.message,
    });
  }
}
