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

  // 2. Allow auth & public informational pages
  const isAuthPage =
    pathname === '/login' ||
    pathname === '/register' ||
    pathname === '/verify-otp' ||
    pathname === '/forgot-password' ||
    pathname === '/reset-password';

  const isPublicPage =
    isAuthPage ||
    pathname === '/terms' ||
    pathname === '/privacy' ||
    pathname === '/about' ||
    pathname === '/help' ||
    pathname === '/contact';

  if (isPublicPage && !isAuthPage) {
    return NextResponse.next();
  }

  // 3. Get JWT token with HTTPS & NextAuth v5 cookie name support
  const secret =
    process.env.AUTH_SECRET ||
    process.env.NEXTAUTH_SECRET ||
    'smartfarm3-super-secure-jwt-secret-key-development-mode';

  let token = await getToken({ req, secret });

  if (!token) {
    const isHttps = req.nextUrl.protocol === 'https:' || req.headers.get('x-forwarded-proto') === 'https';
    if (isHttps) {
      token = await getToken({ req, secret, secureCookie: true });
    }
  }
  if (!token) {
    token = await getToken({ req, secret, cookieName: '__Secure-authjs.session-token' });
  }
  if (!token) {
    token = await getToken({ req, secret, cookieName: 'authjs.session-token' });
  }
  if (!token) {
    token = await getToken({ req, secret, cookieName: '__Secure-next-auth.session-token' });
  }

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
