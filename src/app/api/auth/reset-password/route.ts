import { NextRequest, NextResponse } from 'next/server';
import bcrypt from 'bcryptjs';
import prisma from '@/lib/prisma';
import { resetPasswordSchema } from '@/lib/validators';
import { authLimiter } from '@/lib/rate-limit';

export async function POST(req: NextRequest) {
  try {
    const ip = req.headers.get('x-forwarded-for') || '127.0.0.1';
    const rateLimit = await authLimiter.limit(`reset-pw:${ip}`);
    if (!rateLimit.success) {
      return NextResponse.json(
        { success: false, message: 'Too many attempts. Please wait a minute.' },
        { status: 429 }
      );
    }

    const body = await req.json();
    const parseResult = resetPasswordSchema.safeParse(body);

    if (!parseResult.success) {
      const errorMsg = parseResult.error.errors[0]?.message || 'Invalid password reset data';
      return NextResponse.json({ success: false, message: errorMsg }, { status: 400 });
    }

    const { userId, otp, newPassword } = parseResult.data;

    // Verify OTP
    const activeOtp = await prisma.loginOtp.findFirst({
      where: {
        userId,
        reason: 'password_reset',
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
        { success: false, message: 'Invalid or expired password reset code.' },
        { status: 400 }
      );
    }

    // Hash new password with bcrypt
    const hashedPassword = await bcrypt.hash(newPassword, 12);

    // Update password & mark OTP used
    await prisma.$transaction([
      prisma.user.update({
        where: { id: userId },
        data: { password: hashedPassword },
      }),
      prisma.loginOtp.update({
        where: { id: activeOtp.id },
        data: { used: true },
      }),
    ]);

    return NextResponse.json({
      success: true,
      message: 'Password reset successful! You can now log in with your new password.',
    });
  } catch (error: any) {
    console.error('Reset Password Error:', error);
    return NextResponse.json(
      { success: false, message: error?.message || 'Failed to reset password.' },
      { status: 500 }
    );
  }
}
