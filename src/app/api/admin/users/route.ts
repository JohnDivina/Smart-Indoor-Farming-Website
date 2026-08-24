import { NextRequest, NextResponse } from 'next/server';
import { auth } from '@/lib/auth';
import prisma from '@/lib/prisma';

// GET /api/admin/users - Fetch users list with roles & approval statuses
export async function GET(req: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const currentRole = session.user.role || 'viewer';
    if (currentRole !== 'admin' && currentRole !== 'farm_manager') {
      return NextResponse.json({ success: false, message: 'Access denied: Master Admin or Farm Manager role required.' }, { status: 403 });
    }

    const users = await prisma.user.findMany({
      select: {
        id: true,
        username: true,
        email: true,
        phonenumber: true,
        role: true,
        approved: true,
        authProvider: true,
        emailVerified: true,
        createdAt: true,
        activeUsers: {
          select: {
            lastActivity: true,
            currentPage: true,
          },
          take: 1,
        },
      },
      orderBy: [
        { approved: 'asc' }, // Pending approvals first
        { createdAt: 'desc' },
      ],
    });

    const formattedUsers = users.map((u) => ({
      id: u.id,
      username: u.username,
      email: u.email,
      phonenumber: u.phonenumber || '--',
      role: u.role || 'viewer',
      approved: u.approved,
      authProvider: u.authProvider || 'credentials',
      emailVerified: u.emailVerified === 1,
      createdAt: u.createdAt,
      lastActivity: u.activeUsers[0]?.lastActivity || null,
      currentPage: u.activeUsers[0]?.currentPage || 'Offline',
    }));

    return NextResponse.json({
      success: true,
      users: formattedUsers,
      currentUserRole: currentRole,
    });
  } catch (error: any) {
    console.error('Admin Users GET Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Error fetching user directory.' }, { status: 500 });
  }
}

// PUT /api/admin/users - Update user role & approval status (Admin Only)
export async function PUT(req: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const currentRole = session.user.role;
    if (currentRole !== 'admin') {
      return NextResponse.json({ success: false, message: 'Only Master Administrator can assign roles and approvals.' }, { status: 403 });
    }

    const body = await req.json();
    const { userId, role, approved } = body;

    if (!userId || typeof userId !== 'number') {
      return NextResponse.json({ success: false, message: 'Valid userId is required.' }, { status: 400 });
    }

    const validRoles = ['admin', 'farm_manager', 'farmer', 'viewer'];
    if (role && !validRoles.includes(role)) {
      return NextResponse.json({ success: false, message: 'Invalid role specified.' }, { status: 400 });
    }

    const targetUser = await prisma.user.findUnique({
      where: { id: userId },
    });

    if (!targetUser) {
      return NextResponse.json({ success: false, message: 'Target user not found.' }, { status: 404 });
    }

    // Protect master admin from accidental lock-out
    if (targetUser.email.toLowerCase() === 'johnrey_divina@clsu.edu.ph' && (role !== 'admin' || approved === false)) {
      return NextResponse.json({ success: false, message: 'Master Administrator account cannot be demoted or disabled.' }, { status: 400 });
    }

    const updated = await prisma.user.update({
      where: { id: userId },
      data: {
        ...(role !== undefined ? { role } : {}),
        ...(approved !== undefined ? { approved } : {}),
      },
    });

    return NextResponse.json({
      success: true,
      message: `User ${updated.username} updated successfully. Role: ${updated.role}, Approved: ${updated.approved ? 'Yes' : 'No'}`,
      user: {
        id: updated.id,
        username: updated.username,
        role: updated.role,
        approved: updated.approved,
      },
    });
  } catch (error: any) {
    console.error('Admin Users PUT Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Error updating user.' }, { status: 500 });
  }
}

// DELETE /api/admin/users - Delete user account (Admin can delete anyone except self/admin; Farm Manager can delete farmers & viewers)
export async function DELETE(req: NextRequest) {
  try {
    const session = await auth();
    if (!session?.user?.id || session.user.isGuest) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const currentRole = session.user.role;
    const currentUserId = Number(session.user.id);

    if (currentRole !== 'admin' && currentRole !== 'farm_manager') {
      return NextResponse.json({ success: false, message: 'Access denied: Master Admin or Farm Manager role required.' }, { status: 403 });
    }

    const body = await req.json();
    const { userId } = body;

    if (!userId || typeof userId !== 'number') {
      return NextResponse.json({ success: false, message: 'Valid userId is required.' }, { status: 400 });
    }

    if (userId === currentUserId) {
      return NextResponse.json({ success: false, message: 'You cannot delete your own account from Master Control. Use Settings > Danger Zone.' }, { status: 400 });
    }

    const targetUser = await prisma.user.findUnique({
      where: { id: userId },
    });

    if (!targetUser) {
      return NextResponse.json({ success: false, message: 'Target user not found.' }, { status: 404 });
    }

    // Permission checks:
    // Master admin cannot be deleted
    if (targetUser.role === 'admin' || targetUser.email.toLowerCase() === 'johnrey_divina@clsu.edu.ph') {
      return NextResponse.json({ success: false, message: 'Master Administrator account cannot be deleted.' }, { status: 403 });
    }

    // Farm managers can ONLY delete farmers and viewers (not other farm managers or admins)
    if (currentRole === 'farm_manager' && targetUser.role === 'farm_manager') {
      return NextResponse.json({ success: false, message: 'Farm Managers cannot delete other Farm Managers.' }, { status: 403 });
    }

    // Delete associated records then user
    await prisma.loginOtp.deleteMany({ where: { userId } });
    await prisma.activeUser.deleteMany({ where: { userId } });
    await prisma.account.deleteMany({ where: { userId } });
    await prisma.user.delete({ where: { id: userId } });

    return NextResponse.json({
      success: true,
      message: `Account ${targetUser.username} (${targetUser.email}) has been permanently deleted.`,
    });
  } catch (error: any) {
    console.error('Admin Users DELETE Error:', error);
    return NextResponse.json({ success: false, message: error?.message || 'Error deleting user.' }, { status: 500 });
  }
}
