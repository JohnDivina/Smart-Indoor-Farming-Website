import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { otpVerifySchema } from '@/lib/validators';
import { sendOTPEmail } from '@/lib/email';
import { authLimiter } from '@/lib/rate-limit';

// Verify OTP
export async function POST(req: NextRequest) {
  try {
    const ip = req.headers.get('x-forwarded-for') || '127.0.0.1';
    const rateLimit = await authLimiter.limit(`verify-otp:${ip}`);
    if (!rateLimit.success) {
      return NextResponse.json(
        { success: false, message: 'Too many verification attempts. Please wait a minute.' },
        { status: 429 }
      );
    }

    const body = await req.json();
    const parseResult = otpVerifySchema.safeParse(body);

    if (!parseResult.success) {
      const errorMsg = parseResult.error.errors[0]?.message || 'Invalid verification request';
      return NextResponse.json({ success: false, message: errorMsg }, { status: 400 });
    }

    const { userId, otp, reason } = parseResult.data;

    // Find the latest active OTP for this user
    const activeOtp = await prisma.loginOtp.findFirst({
      where: {
        userId,
        reason,
        used: false,
        expiresAt: {
          gt: new Date(),
        },
      },
      orderBy: {
        createdAt: 'desc',
      },
    });

    if (!activeOtp || activeOtp.otp !== otp) {
      return NextResponse.json(
        { success: false, message: 'Invalid or expired verification code.' },
        { status: 400 }
      );
    }

    // Mark OTP as used
    await prisma.loginOtp.update({
      where: { id: activeOtp.id },
      data: { used: true },
    });

    // If reason is account_creation, mark email verified
    if (reason === 'account_creation') {
      await prisma.user.update({
        where: { id: userId },
        data: { emailVerified: 1 },
      });
    }

    return NextResponse.json({
      success: true,
      message: 'Verification successful! You may now sign in.',
    });
  } catch (error: any) {
    console.error('Verify OTP API Error:', error);
    return NextResponse.json(
      { success: false, message: error?.message || 'Verification failed.' },
      { status: 500 }
    );
  }
}

// Resend OTP
export async function PUT(req: NextRequest) {
  try {
    const ip = req.headers.get('x-forwarded-for') || '127.0.0.1';
    const rateLimit = await authLimiter.limit(`resend-otp:${ip}`);
    if (!rateLimit.success) {
      return NextResponse.json(
        { success: false, message: 'Please wait before requesting another code.' },
        { status: 429 }
      );
    }

    const body = await req.json();
    const userId = Number(body.userId);
    const reason = (body.reason as string) || 'account_creation';

    if (!userId || isNaN(userId)) {
      return NextResponse.json({ success: false, message: 'Invalid user ID' }, { status: 400 });
    }

    const user = await prisma.user.findUnique({
      where: { id: userId },
    });

    if (!user) {
      return NextResponse.json({ success: false, message: 'User not found' }, { status: 404 });
    }

    // Invalidate old unexpired OTPs
    await prisma.loginOtp.updateMany({
      where: { userId, reason, used: false },
      data: { used: true },
    });

    // Generate new OTP
    const newOtp = Math.floor(100000 + Math.random() * 900000).toString();
    const expiresAt = new Date(Date.now() + 10 * 60 * 1000);

    await prisma.loginOtp.create({
      data: {
        userId,
        otp: newOtp,
        expiresAt,
        reason,
      },
    });

    // Send email
    await sendOTPEmail({
      toEmail: user.email,
      username: user.username,
      otp: newOtp,
      reason: reason as any,
    });

    return NextResponse.json({
      success: true,
      message: 'A new 6-digit code has been sent to your email address.',
    });
  } catch (error: any) {
    console.error('Resend OTP Error:', error);
    return NextResponse.json(
      { success: false, message: error?.message || 'Failed to resend code.' },
      { status: 500 }
    );
  }
}
