import { DefaultSession } from 'next-auth';

declare module 'next-auth' {
  interface Session {
    user: {
      id?: string;
      username?: string;
      phonenumber?: string;
      isGuest?: boolean;
      role?: string; // 'admin' | 'farm_manager' | 'farmer' | 'viewer'
      authProvider?: string; // 'credentials' | 'google'
      approved?: boolean;
    } & DefaultSession['user'];
  }

  interface User {
    id?: string;
    username?: string;
    phonenumber?: string;
    isGuest?: boolean;
    role?: string;
    authProvider?: string;
    approved?: boolean;
  }
}

declare module '@auth/core/types' {
  interface Session {
    user: {
      id?: string;
      username?: string;
      phonenumber?: string;
      isGuest?: boolean;
      role?: string;
      authProvider?: string;
      approved?: boolean;
    } & DefaultSession['user'];
  }

  interface User {
    id?: string;
    username?: string;
    phonenumber?: string;
    isGuest?: boolean;
    role?: string;
    authProvider?: string;
    approved?: boolean;
  }
}

declare module 'next-auth/jwt' {
  interface JWT {
    id?: string;
    username?: string;
    phonenumber?: string;
    isGuest?: boolean;
    role?: string;
    authProvider?: string;
    approved?: boolean;
  }
}
