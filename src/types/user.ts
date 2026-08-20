export interface UserProfile {
  id: number;
  username: string;
  email: string;
  phonenumber?: string | null;
  totpEnabled: boolean;
  emailVerified: boolean;
  createdAt: string;
}

export interface ActiveUserInfo {
  userId: number;
  username: string;
  currentPage: string;
  lastActivity: string;
}
