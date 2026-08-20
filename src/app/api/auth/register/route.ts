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

    // Check if user already exists
    const existingUser = await prisma.user.findFirst({
      where: {
        OR: [
          { username },
          { email },
          { phonenumber },
        ],
      },
    });

    if (existingUser) {
      if (existingUser.username === username) {
        return NextResponse.json({ success: false, message: 'Username is already taken.' }, { status: 409 });
      }
      if (existingUser.email === email) {
        return NextResponse.json({ success: false, message: 'Email address is already registered.' }, { status: 409 });
      }
      if (existingUser.phonenumber === phonenumber) {
        return NextResponse.json({ success: false, message: 'Phone number is already in use.' }, { status: 409 });
      }
    }

    // Hash password with bcrypt
    const hashedPassword = await bcrypt.hash(password, 12);

    // Create user
    const newUser = await prisma.user.create({
      data: {
        username,
        email,
        phonenumber,
        password: hashedPassword,
        emailVerified: 0,
      },
    });

    // Generate 6-digit OTP
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    const expiresAt = new Date(Date.now() + 10 * 60 * 1000); // 10 minutes

    await prisma.loginOtp.create({
      data: {
        userId: newUser.id,
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
      userId: newUser.id,
      email,
      message: 'Account created! Please check your email for the 6-digit verification code.',
      emailSent: emailResult.success,
    });
  } catch (error: any) {
    console.error('Registration API Error:', error);
    return NextResponse.json(
      { success: false, message: error?.message || 'An unexpected error occurred during registration.' },
      { status: 500 }
    );
  }
}
