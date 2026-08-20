import { NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import { sendOTPEmail } from '@/lib/email';

export async function POST() {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const userId = Number(session.user.id);
    const user = await prisma.user.findUnique({
      where: { id: userId },
    });

    if (!user) {
      return NextResponse.json({ success: false, message: 'User not found' }, { status: 404 });
    }

    // Invalidate prior account deletion OTPs
    await prisma.loginOtp.updateMany({
      where: { userId, reason: 'account_deletion', used: false },
      data: { used: true },
    });

    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    const expiresAt = new Date(Date.now() + 10 * 60 * 1000);

    await prisma.loginOtp.create({
      data: {
        userId,
        otp,
        expiresAt,
        reason: 'account_deletion',
      },
    });

    await sendOTPEmail({
      toEmail: user.email,
      username: user.username,
      otp,
      reason: 'account_deletion',
    });

    return NextResponse.json({
      success: true,
      message: 'Account deletion confirmation code has been sent to your email.',
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, message: error?.message || 'Failed to send OTP' }, { status: 500 });
  }
}
