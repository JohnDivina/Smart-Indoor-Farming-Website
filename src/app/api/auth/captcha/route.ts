import { NextResponse } from 'next/server';
import { generateCaptcha } from '@/lib/captcha';

// GET /api/auth/captcha - Generate fresh security CAPTCHA
export async function GET() {
  try {
    const captcha = generateCaptcha();
    return NextResponse.json({
      success: true,
      svg: captcha.svg,
      token: captcha.token,
    });
  } catch (error: any) {
    console.error('CAPTCHA Generation Error:', error);
    return NextResponse.json(
      { success: false, message: 'Failed to generate security challenge' },
      { status: 500 }
    );
  }
}
