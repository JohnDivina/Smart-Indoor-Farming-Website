import { NextRequest, NextResponse } from 'next/server';
import bcrypt from 'bcryptjs';
import prisma from '@/lib/prisma';
import { registerSchema } from '@/lib/validators';
import { sendOTPEmail } from '@/lib/email';
import { authLimiter } from '@/lib/rate-limit';

export async function POST(req: NextRequest) {
  try {
    const ip = req.headers.get('x-forwarded-for') || '127.0.0.1';
    const rateLimit = await authLimiter.limit(`register:${ip}`);
    if (!rateLimit.success) {
      return NextResponse.json(
        { success: false, message: 'Too many registration attempts. Please try again later.' },
        { status: 429 }
      );
    }

    const body = await req.json();
    const parseResult = registerSchema.safeParse(body);

    if (!parseResult.success) {
      const errorMsg = parseResult.error.errors[0]?.message || 'Invalid registration data';
      return NextResponse.json({ success: false, message: errorMsg }, { status: 400 });
    }

    const { username, email, phonenumber, password } = parseResult.data;

    // Check if user already exists (case-insensitive for username/email)
    const existingUser = await prisma.user.findFirst({
      where: {
        OR: [
          { username: { equals: username, mode: 'insensitive' } },
          { email: { equals: email, mode: 'insensitive' } },
          { phonenumber },
        ],
      },
    });

    const hashedPassword = await bcrypt.hash(password, 12);

    let targetUserId: number;

    if (existingUser) {
      // If user exists AND is already verified, reject registration
      if (existingUser.emailVerified === 1) {
        if (existingUser.username.toLowerCase() === username.toLowerCase()) {
          return NextResponse.json({ success: false, message: 'Username is already registered. Please sign in.' }, { status: 409 });
        }
        if (existingUser.email.toLowerCase() === email.toLowerCase()) {
          return NextResponse.json({ success: false, message: 'Email address is already registered. Please sign in.' }, { status: 409 });
        }
        if (existingUser.phonenumber === phonenumber) {
          return NextResponse.json({ success: false, message: 'Phone number is already in use. Please sign in.' }, { status: 409 });
        }
      }

      // If user exists but is NOT verified (unconfirmed email), allow re-registration and re-issue OTP
      await prisma.user.update({
        where: { id: existingUser.id },
        data: {
          username,
          email,
          phonenumber,
          password: hashedPassword,
        },
      });

      targetUserId = existingUser.id;

      // Invalidate previous unexpired OTPs
      await prisma.loginOtp.updateMany({
        where: { userId: targetUserId, reason: 'account_creation', used: false },
        data: { used: true },
      });
    } else {
      // Create new unverified user
      const newUser = await prisma.user.create({
        data: {
          username,
          email,
          phonenumber,
          password: hashedPassword,
          emailVerified: 0,
        },
      });
      targetUserId = newUser.id;
    }

    // Generate 6-digit OTP
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    const expiresAt = new Date(Date.now() + 10 * 60 * 1000); // 10 minutes

    await prisma.loginOtp.create({
      data: {
        userId: targetUserId,
        otp,
        expiresAt,
        reason: 'account_creation',
      },
    });

    // Send verification email
    const emailResult = await sendOTPEmail({
      toEmail: email,
      username,
      otp,
      reason: 'account_creation',
    });

    return NextResponse.json({
      success: true,
      userId: targetUserId,
      email,
      message: emailResult.success
        ? 'Account registered! A 6-digit verification code has been sent to your email.'
        : `Account registered! Verification code: ${otp} (SMTP: ${emailResult.message})`,
      emailSent: emailResult.success,
      devOtp: !emailResult.success || process.env.NODE_ENV === 'development' ? otp : undefined,
    });
  } catch (error: any) {
    console.error('Registration API Error:', error);
    return NextResponse.json(
      { success: false, message: error?.message || 'An unexpected error occurred during registration.' },
      { status: 500 }
    );
  }
}
