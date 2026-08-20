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
      // Default: Last 24 hours
      whereClause = {
        timestamp: {
          gte: subDays(new Date(), 1),
        },
      };
    }

    const readings = await prisma.tempHumiditySensor.findMany({
      where: whereClause,
      orderBy: { timestamp: 'asc' },
      take: Math.min(limitParam, 500),
    });

    const labels: string[] = [];
    const temperature: number[] = [];
    const humidity: number[] = [];

    readings.forEach((r) => {
      labels.push(format(new Date(r.timestamp), 'h:mm a'));
      temperature.push(Number(r.temperature.toFixed(1)));
      humidity.push(Number(r.humidity.toFixed(1)));
    });

    return NextResponse.json({
      success: true,
      labels,
      temperature,
      humidity,
      count: readings.length,
      raw: readings.map((r) => ({
        id: r.id,
        temperature: r.temperature,
        humidity: r.humidity,
        timestamp: format(new Date(r.timestamp), 'yyyy-MM-dd HH:mm:ss'),
      })),
    });
  } catch (error: any) {
    console.error('Temp/Humid Chart API Error:', error);
    return NextResponse.json({
      success: false,
      labels: [],
      temperature: [],
      humidity: [],
      error: error?.message,
    });
  }
}
