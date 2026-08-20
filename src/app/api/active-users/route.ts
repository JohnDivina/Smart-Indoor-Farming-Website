import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import { format, subMinutes } from 'date-fns';

export async function GET() {
  try {
    const fiveMinutesAgo = subMinutes(new Date(), 5);

    const activeRecords = await prisma.activeUser.findMany({
      where: {
        lastActivity: {
          gte: fiveMinutesAgo,
        },
      },
      include: {
        user: {
          select: {
            id: true,
            username: true,
          },
        },
      },
      orderBy: {
        lastActivity: 'desc',
      },
    });

    const users = activeRecords.map((r) => ({
      userId: r.userId,
      username: r.user?.username || `User #${r.userId}`,
      currentPage: r.currentPage || 'Dashboard',
      lastSeen: format(new Date(r.lastActivity), 'h:mm a'),
    }));

    return NextResponse.json({
      success: true,
      count: users.length,
      users,
    });
  } catch (error: any) {
    console.error('Active Users API Error:', error);
    return NextResponse.json({ success: false, count: 0, users: [] });
  }
}

export async function POST(req: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: true, message: 'Guest presence skipped' });
    }

    const userId = Number(session.user.id);
    if (!userId || isNaN(userId)) {
      return NextResponse.json({ success: false }, { status: 400 });
    }

    const body = await req.json().catch(() => ({}));
    const currentPage = (body.currentPage as string) || 'Dashboard Overview';

    await prisma.activeUser.upsert({
      where: { userId },
      update: {
        lastActivity: new Date(),
        currentPage,
      },
      create: {
        userId,
        lastActivity: new Date(),
        currentPage,
      },
    });

    return NextResponse.json({ success: true });
  } catch (error: any) {
    return NextResponse.json({ success: false }, { status: 500 });
  }
}
