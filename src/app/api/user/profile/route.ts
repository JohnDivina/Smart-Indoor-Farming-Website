import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import { updateProfileSchema } from '@/lib/validators';
import { format } from 'date-fns';

export async function GET() {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const userId = Number(session.user.id);
    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: {
        id: true,
        username: true,
        email: true,
        phonenumber: true,
        role: true,
        authProvider: true,
        totpEnabled: true,
        emailVerified: true,
        createdAt: true,
      },
    });

    if (!user) {
      return NextResponse.json({ success: false, message: 'User not found' }, { status: 404 });
    }

    return NextResponse.json({
      success: true,
      user: {
        ...user,
        createdAt: format(new Date(user.createdAt), 'MMMM d, yyyy'),
      },
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, message: error?.message || 'Error fetching profile' }, { status: 500 });
  }
}

export async function PUT(req: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const userId = Number(session.user.id);
    const body = await req.json();
    const parseResult = updateProfileSchema.safeParse(body);

    if (!parseResult.success) {
      return NextResponse.json({ success: false, message: 'Invalid profile data' }, { status: 400 });
    }

    const { username, email, phonenumber } = parseResult.data;

    // Check if new username or email is taken by someone else
    if (username || email) {
      const existing = await prisma.user.findFirst({
        where: {
          OR: [
            username ? { username } : {},
            email ? { email } : {},
          ],
          NOT: { id: userId },
        },
      });

      if (existing) {
        if (existing.username === username) {
          return NextResponse.json({ success: false, message: 'Username is already in use.' }, { status: 409 });
        }
        if (existing.email === email) {
          return NextResponse.json({ success: false, message: 'Email address is already in use.' }, { status: 409 });
        }
      }
    }

    const updated = await prisma.user.update({
      where: { id: userId },
      data: {
        ...(username ? { username } : {}),
        ...(email ? { email } : {}),
        ...(phonenumber ? { phonenumber } : {}),
      },
    });

    return NextResponse.json({
      success: true,
      message: 'Profile updated successfully.',
      user: {
        username: updated.username,
        email: updated.email,
        phonenumber: updated.phonenumber,
      },
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, message: error?.message || 'Failed to update profile' }, { status: 500 });
  }
}
