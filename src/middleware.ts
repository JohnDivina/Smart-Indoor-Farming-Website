import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import { getToken } from 'next-auth/jwt';

export async function middleware(req: NextRequest) {
  const { pathname } = req.nextUrl;

  // 1. Allow static assets, images, and public files
  if (
    pathname.startsWith('/_next') ||
    pathname.startsWith('/assets') ||
    pathname.startsWith('/favicon.ico') ||
    pathname.startsWith('/api/auth') ||
    pathname.startsWith('/api/esp32')
  ) {
    return NextResponse.next();
  }

  // 2. Allow auth pages
  const isAuthPage =
    pathname === '/login' ||
    pathname === '/register' ||
    pathname === '/verify-otp' ||
    pathname === '/forgot-password' ||
    pathname === '/reset-password';

  // 3. Get JWT token
  const token = await getToken({
    req,
    secret: process.env.NEXTAUTH_SECRET || 'smartfarm3-super-secure-jwt-secret-key-development-mode',
  });

  const isAuthenticated = Boolean(token);

  // If user is on an auth page while authenticated, redirect to dashboard
  if (isAuthPage) {
    if (isAuthenticated) {
      return NextResponse.redirect(new URL('/dashboard', req.url));
    }
    return NextResponse.next();
  }

  // If root path '/', redirect to dashboard if authenticated, or login if not
  if (pathname === '/') {
    if (isAuthenticated) {
      return NextResponse.redirect(new URL('/dashboard', req.url));
    }
    return NextResponse.redirect(new URL('/login', req.url));
  }

  // For protected dashboard and control routes:
  if (!isAuthenticated) {
    const loginUrl = new URL('/login', req.url);
    loginUrl.searchParams.set('callbackUrl', encodeURIComponent(pathname));
    return NextResponse.redirect(loginUrl);
  }

  // If user is in guest mode and tries to access settings or admin actions
  if (token?.isGuest && pathname.startsWith('/settings')) {
    return NextResponse.redirect(new URL('/dashboard?guest_restricted=1', req.url));
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/((?!_next/static|_next/image|assets|favicon.ico).*)'],
};
