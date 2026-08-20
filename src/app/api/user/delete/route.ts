import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';

export async function DELETE(req: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const userId = Number(session.user.id);
    const body = await req.json().catch(() => ({}));
    const { otp } = body;

    if (!otp || typeof otp !== 'string') {
      return NextResponse.json({ success: false, message: 'Please provide the 6-digit confirmation code.' }, { status: 400 });
    }

    // Verify deletion OTP
    const validOtp = await prisma.loginOtp.findFirst({
      where: {
        userId,
        otp,
        reason: 'account_deletion',
        used: false,
        expiresAt: {
          gt: new Date(),
        },
      },
    });

    if (!validOtp) {
      return NextResponse.json({ success: false, message: 'Invalid or expired confirmation code.' }, { status: 400 });
    }

    // Mark OTP used
    await prisma.loginOtp.update({
      where: { id: validOtp.id },
      data: { used: true },
    });

    // Delete user (cascades related records in PostgreSQL)
    await prisma.user.delete({
      where: { id: userId },
    });

    return NextResponse.json({
      success: true,
      message: 'Account deleted permanently.',
    });
  } catch (error: any) {
    console.error('Delete Account Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Failed to delete account' }, { status: 500 });
  }
}
