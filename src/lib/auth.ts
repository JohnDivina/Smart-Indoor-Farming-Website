import NextAuth from 'next-auth';
import CredentialsProvider from 'next-auth/providers/credentials';
import GoogleProvider from 'next-auth/providers/google';
import crypto from 'crypto';
import bcrypt from 'bcryptjs';
import prisma from '@/lib/prisma';
import { verifyTotpToken } from '@/lib/totp';

export const { handlers, auth, signIn, signOut } = NextAuth({
  session: {
    strategy: 'jwt',
    maxAge: 30 * 24 * 60 * 60, // 30 days
  },
  pages: {
    signIn: '/login',
    error: '/login',
  },
  providers: [
    ...(process.env.GOOGLE_CLIENT_ID && process.env.GOOGLE_CLIENT_SECRET
      ? [
          GoogleProvider({
            clientId: process.env.GOOGLE_CLIENT_ID,
            clientSecret: process.env.GOOGLE_CLIENT_SECRET,
          }),
        ]
      : []),

    CredentialsProvider({
      id: 'credentials',
      name: 'Credentials',
      credentials: {
        identifier: { label: 'Username or Email', type: 'text' },
        password: { label: 'Password', type: 'password' },
        isGuest: { label: 'Guest Login', type: 'text' },
        totpCode: { label: '2FA Code', type: 'text' },
      },
      async authorize(credentials) {
        if (!credentials) return null;

        // 1. Guest Mode Login
        if (credentials.isGuest === 'true' || credentials.identifier === '__guest__') {
          return {
            id: '0',
            name: 'Guest User',
            email: 'guest@smartfarm.local',
            username: 'Guest',
            isGuest: true,
          };
        }

        const identifier = (credentials.identifier as string)?.trim();
        const password = credentials.password as string;
        const totpCode = (credentials.totpCode as string)?.trim();

        if (!identifier || !password) {
          throw new Error('Please provide both username/email and password.');
        }

        // 2. Find user by username, email, or phonenumber (case-insensitive for username and email)
        const user = await prisma.user.findFirst({
          where: {
            OR: [
              { username: { equals: identifier, mode: 'insensitive' } },
              { email: { equals: identifier, mode: 'insensitive' } },
              { phonenumber: identifier },
            ],
          },
        });

        if (!user) {
          throw new Error('Invalid credentials or user not found.');
        }

        // 3. Password Verification (Support bcrypt + legacy PHP MD5 with auto-upgrade)
        let isValidPassword = false;

        if (user.password.startsWith('$2a$') || user.password.startsWith('$2b$') || user.password.startsWith('$2y$')) {
          isValidPassword = await bcrypt.compare(password, user.password);
        } else {
          // Legacy PHP MD5 verification
          const md5Hash = crypto.createHash('md5').update(password).digest('hex');
          if (md5Hash === user.password) {
            isValidPassword = true;
            // Transparently upgrade to bcrypt (salt rounds: 12)
            try {
              const upgradedBcrypt = await bcrypt.hash(password, 12);
              await prisma.user.update({
                where: { id: user.id },
                data: { password: upgradedBcrypt },
              });
            } catch (err) {
              console.error('Failed to auto-upgrade legacy password to bcrypt:', err);
            }
          }
        }

        if (!isValidPassword) {
          throw new Error('Invalid username/email or password.');
        }

        // 4. Check email verification (email_verified == 1)
        if (user.emailVerified === 0) {
          throw new Error(`UNVERIFIED_EMAIL:${user.id}:${encodeURIComponent(user.email)}`);
        }

        // 5. Check 2FA / TOTP if enabled
        if (user.totpEnabled && user.totpSecret) {
          if (!totpCode) {
            throw new Error(`REQUIRE_2FA:${user.id}`);
          }
          const isValidTotp = verifyTotpToken(totpCode, user.totpSecret);
          if (!isValidTotp) {
            throw new Error('Invalid two-factor authentication code.');
          }
        }

        // 6. Record user activity in active_users table
        try {
          await prisma.activeUser.upsert({
            where: { userId: user.id },
            update: {
              lastActivity: new Date(),
              currentPage: 'Dashboard Overview',
            },
            create: {
              userId: user.id,
              lastActivity: new Date(),
              currentPage: 'Dashboard Overview',
            },
          });
        } catch (actErr) {
          console.warn('Active user logging error:', actErr);
        }

        return {
          id: String(user.id),
          name: user.username,
          email: user.email,
          username: user.username,
          phonenumber: user.phonenumber || undefined,
          isGuest: false,
        };
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user, account }) {
      if (user) {
        token.id = user.id;
        token.username = (user as any).username || user.name || '';
        token.phonenumber = (user as any).phonenumber;
        token.isGuest = (user as any).isGuest ?? false;
      }
      return token;
    },
    async session({ session, token }) {
      if (session.user) {
        session.user.id = token.id as string;
        session.user.username = token.username as string;
        session.user.phonenumber = token.phonenumber as string;
        session.user.isGuest = (token.isGuest as boolean) ?? false;
      }
      return session;
    },
  },
});
