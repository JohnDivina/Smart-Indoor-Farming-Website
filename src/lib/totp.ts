import { authenticator } from 'otplib';
import QRCode from 'qrcode';

// Configure authenticator options
authenticator.options = {
  window: 1, // Allow 1 step before/after for slight clock drifts
};

export function generateTotpSecret(): string {
  return authenticator.generateSecret();
}

export function generateTotpUri(username: string, secret: string): string {
  return authenticator.keyuri(username, 'CLSU Smart Farm', secret);
}

export async function generateTotpQrCode(uri: string): Promise<string> {
  return QRCode.toDataURL(uri);
}

export function verifyTotpToken(token: string, secret: string): boolean {
  try {
    return authenticator.verify({ token, secret });
  } catch (error) {
    console.error('TOTP Verification error:', error);
    return false;
  }
}
