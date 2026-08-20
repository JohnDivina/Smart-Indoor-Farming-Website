import { NextRequest, NextResponse } from 'next/server';
import prisma from '@/lib/prisma';
import { forgotPasswordSchema } from '@/lib/validators';
import { sendOTPEmail } from '@/lib/email';
import { authLimiter } from '@/lib/rate-limit';

export async function POST(req: NextRequest) {
  try {
    const ip = req.headers.get('x-forwarded-for') || '127.0.0.1';
    const rateLimit = await authLimiter.limit(`forgot-pw:${ip}`);
    if (!rateLimit.success) {
      return NextResponse.json(
        { success: false, message: 'Too many requests. Please wait a minute.' },
        { status: 429 }
      );
    }

    const body = await req.json();
    const parseResult = forgotPasswordSchema.safeParse(body);

    if (!parseResult.success) {
      return NextResponse.json({ success: false, message: 'Please enter your email or username.' }, { status: 400 });
    }

    const { identifier } = parseResult.data;

    const user = await prisma.user.findFirst({
      where: {
        OR: [
          { email: { equals: identifier, mode: 'insensitive' } },
          { username: { equals: identifier, mode: 'insensitive' } },
        ],
      },
    });

    if (!user) {
      // Return ambiguous message for security to prevent user enumeration
      return NextResponse.json({
        success: true,
        message: 'If an account exists with that identifier, a reset code has been sent to the associated email.',
      });
    }

    // Invalidate existing active OTPs for password reset
    await prisma.loginOtp.updateMany({
      where: { userId: user.id, reason: 'password_reset', used: false },
      data: { used: true },
    });

    // Generate 6-digit OTP
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    const expiresAt = new Date(Date.now() + 10 * 60 * 1000);

    await prisma.loginOtp.create({
      data: {
        userId: user.id,
        otp,
        expiresAt,
        reason: 'password_reset',
      },
    });

    // Send email
    await sendOTPEmail({
      toEmail: user.email,
      username: user.username,
      otp,
      reason: 'password_reset',
    });

    return NextResponse.json({
      success: true,
      userId: user.id,
      email: user.email,
      message: 'A 6-digit password reset code has been sent to your email address.',
    });
  } catch (error: any) {
    console.error('Forgot Password Error:', error);
    return NextResponse.json(
      { success: false, message: error?.message || 'Error processing request.' },
      { status: 500 }
    );
  }
}
