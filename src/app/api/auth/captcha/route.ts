import { NextResponse } from 'next/server';
import { generateCaptcha } from '@/lib/captcha';

export const dynamic = 'force-dynamic';
export const revalidate = 0;

// GET /api/auth/captcha - Generate fresh security CAPTCHA
export async function GET() {
  try {
    const captcha = generateCaptcha();
    return NextResponse.json(
      {
        success: true,
        svg: captcha.svg,
        token: captcha.token,
      },
      {
        headers: {
          'Cache-Control': 'no-store, no-cache, must-revalidate, proxy-revalidate',
          Pragma: 'no-cache',
          Expires: '0',
        },
      }
    );
  } catch (error: any) {
    console.error('CAPTCHA Generation Error:', error);
    return NextResponse.json(
      { success: false, message: 'Failed to generate security challenge' },
      { status: 500 }
    );
  }
}
