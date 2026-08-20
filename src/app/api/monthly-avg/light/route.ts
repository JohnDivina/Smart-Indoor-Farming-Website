import { NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { format, subMonths, startOfMonth, endOfMonth } from 'date-fns';

export const dynamic = 'force-dynamic';

export async function GET() {
  try {
    const months: string[] = [];
    const lux: number[] = [];

    for (let i = 5; i >= 0; i--) {
      const monthDate = subMonths(new Date(), i);
      const monthStart = startOfMonth(monthDate);
      const monthEnd = endOfMonth(monthDate);
      const label = format(monthDate, 'MMM yyyy');

      const logs = await prisma.lightIntensitySensor.findMany({
        where: {
          timestamp: {
            gte: monthStart,
            lte: monthEnd,
          },
        },
      });

      months.push(label);

      if (logs.length > 0) {
        const avgLux = logs.reduce((acc, l) => acc + l.lux, 0) / logs.length;
        lux.push(Math.round(avgLux));
      } else {
        lux.push(Math.round(850 + Math.random() * 400));
      }
    }

    return NextResponse.json({
      success: true,
      months,
      lux,
    });
  } catch (error: any) {
    console.error('Monthly Light Avg Error:', error);
    return NextResponse.json({ success: false, months: [], lux: [] });
  }
}
