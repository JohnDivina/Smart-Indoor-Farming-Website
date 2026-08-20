import { DefaultSession } from 'next-auth';

declare module 'next-auth' {
  interface Session {
    user: {
      id?: string;
      username?: string;
      phonenumber?: string;
      isGuest?: boolean;
    } & DefaultSession['user'];
  }

  interface User {
    id?: string;
    username?: string;
    phonenumber?: string;
    isGuest?: boolean;
  }
}

declare module '@auth/core/types' {
  interface Session {
    user: {
      id?: string;
      username?: string;
      phonenumber?: string;
      isGuest?: boolean;
    } & DefaultSession['user'];
  }

  interface User {
    id?: string;
    username?: string;
    phonenumber?: string;
    isGuest?: boolean;
  }
}

declare module 'next-auth/jwt' {
  interface JWT {
    id?: string;
    username?: string;
    phonenumber?: string;
    isGuest?: boolean;
  }
}
