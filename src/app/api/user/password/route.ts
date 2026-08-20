import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';
import bcrypt from 'bcryptjs';
import crypto from 'crypto';
import { changePasswordSchema } from '@/lib/validators';

export async function PUT(req: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const userId = Number(session.user.id);
    const body = await req.json();
    const parseResult = changePasswordSchema.safeParse(body);

    if (!parseResult.success) {
      const errorMsg = parseResult.error.errors[0]?.message || 'Invalid password data';
      return NextResponse.json({ success: false, message: errorMsg }, { status: 400 });
    }

    const { currentPassword, newPassword } = parseResult.data;

    const user = await prisma.user.findUnique({
      where: { id: userId },
    });

    if (!user) {
      return NextResponse.json({ success: false, message: 'User not found' }, { status: 404 });
    }

    // Verify current password
    let isValid = false;
    if (user.password.startsWith('$2')) {
      isValid = await bcrypt.compare(currentPassword, user.password);
    } else {
      const md5 = crypto.createHash('md5').update(currentPassword).digest('hex');
      isValid = md5 === user.password;
    }

    if (!isValid) {
      return NextResponse.json({ success: false, message: 'Current password is incorrect.' }, { status: 400 });
    }

    // Hash new password with bcrypt
    const newHashed = await bcrypt.hash(newPassword, 12);

    await prisma.user.update({
      where: { id: userId },
      data: { password: newHashed },
    });

    return NextResponse.json({
      success: true,
      message: 'Password changed successfully.',
    });
  } catch (error: any) {
    console.error('Change Password Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Error changing password' }, { status: 500 });
  }
}
