import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format, startOfDay, endOfDay } from 'date-fns';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const { searchParams } = new URL(req.url);
    const dateParam = searchParams.get('date');

    const targetDate = dateParam ? new Date(dateParam) : new Date();

    const readings = await prisma.npkSensor.findMany({
      where: {
        timestamp: {
          gte: startOfDay(targetDate),
          lte: endOfDay(targetDate),
        },
      },
      orderBy: { timestamp: 'asc' },
    });

    if (readings.length === 0) {
      return NextResponse.json({
        success: true,
        count: 0,
        average: { nitrogen: 0, phosphorus: 0, potassium: 0 },
        readings: [],
      });
    }

    const totalN = readings.reduce((acc, r) => acc + r.nitrogen, 0);
    const totalP = readings.reduce((acc, r) => acc + r.phosphorus, 0);
    const totalK = readings.reduce((acc, r) => acc + r.potassium, 0);

    return NextResponse.json({
      success: true,
      count: readings.length,
      average: {
        nitrogen: Number((totalN / readings.length).toFixed(1)),
        phosphorus: Number((totalP / readings.length).toFixed(1)),
        potassium: Number((totalK / readings.length).toFixed(1)),
      },
      readings: readings.map((r) => ({
        id: r.id,
        nitrogen: r.nitrogen,
        phosphorus: r.phosphorus,
        potassium: r.potassium,
        timestamp: format(new Date(r.timestamp), 'yyyy-MM-dd HH:mm:ss'),
        timeLabel: format(new Date(r.timestamp), 'h:mm a'),
      })),
    });
  } catch (error: any) {
    console.error('NPK Data By Date Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Error querying data' }, { status: 500 });
  }
}
