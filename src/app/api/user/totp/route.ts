import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import { generateTotpSecret, generateTotpUri, generateTotpQrCode, verifyTotpToken } from '@/lib/totp';

// POST: Generate new 2FA secret and QR code
export async function POST() {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const username = session.user.name || session.user.email || 'SmartFarmUser';
    const secret = generateTotpSecret();
    const uri = generateTotpUri(username, secret);
    const qrCode = await generateTotpQrCode(uri);

    return NextResponse.json({
      success: true,
      secret,
      qrCode,
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, message: error?.message || 'Error setting up 2FA' }, { status: 500 });
  }
}

// PUT: Verify and activate 2FA
export async function PUT(req: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const userId = Number(session.user.id);
    const body = await req.json();
    const { code, secret } = body;

    if (!code || !secret) {
      return NextResponse.json({ success: false, message: 'Missing 2FA code or secret' }, { status: 400 });
    }

    const isValid = verifyTotpToken(code, secret);
    if (!isValid) {
      return NextResponse.json({ success: false, message: 'Invalid 6-digit verification code' }, { status: 400 });
    }

    await prisma.user.update({
      where: { id: userId },
      data: {
        totpSecret: secret,
        totpEnabled: true,
      },
    });

    return NextResponse.json({
      success: true,
      message: 'Two-Factor Authentication activated successfully!',
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, message: error?.message || 'Failed to activate 2FA' }, { status: 500 });
  }
}

// DELETE: Disable 2FA
export async function DELETE() {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const userId = Number(session.user.id);

    await prisma.user.update({
      where: { id: userId },
      data: {
        totpSecret: null,
        totpEnabled: false,
      },
    });

    return NextResponse.json({
      success: true,
      message: 'Two-Factor Authentication has been disabled.',
    });
  } catch (error: any) {
    return NextResponse.json({ success: false, message: error?.message || 'Failed to disable 2FA' }, { status: 500 });
  }
}
