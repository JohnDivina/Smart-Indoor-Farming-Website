import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format, startOfDay, endOfDay, subDays } from 'date-fns';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const { searchParams } = new URL(req.url);
    const startDateParam = searchParams.get('startDate');
    const endDateParam = searchParams.get('endDate');

    const startDate = startDateParam ? startOfDay(new Date(startDateParam)) : subDays(new Date(), 7);
    const endDate = endDateParam ? endOfDay(new Date(endDateParam)) : endOfDay(new Date());

    const readings = await prisma.npkSensor.findMany({
      where: {
        timestamp: {
          gte: startDate,
          lte: endDate,
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
        dateLabel: format(new Date(r.timestamp), 'MMM d, h:mm a'),
      })),
    });
  } catch (error: any) {
    console.error('NPK Range API Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Error querying range data' }, { status: 500 });
  }
}
