import crypto from 'crypto';

const CAPTCHA_SECRET = process.env.NEXTAUTH_SECRET || 'smartfarm_captcha_secure_key_2026';
const CHARS = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Exclude ambiguous chars like 0/O, 1/I

export interface CaptchaData {
  token: string;
  svg: string;
}

export function generateCaptcha(): CaptchaData {
  let text = '';
  for (let i = 0; i < 5; i++) {
    text += CHARS.charAt(Math.floor(Math.random() * CHARS.length));
  }

  const timestamp = Date.now();
  const payload = `${text.toUpperCase()}:${timestamp}`;
  const hmac = crypto.createHmac('sha256', CAPTCHA_SECRET).update(payload).digest('hex');
  const token = Buffer.from(`${payload}:${hmac}`).toString('base64');

  // Render high-quality stylized SVG CAPTCHA with visual noise
  const width = 160;
  const height = 48;

  let charElements = '';
  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    const x = 20 + i * 26 + (Math.random() * 6 - 3);
    const y = 32 + (Math.random() * 6 - 3);
    const rot = (Math.random() - 0.5) * 24; // Subtle tilt
    const color = i % 2 === 0 ? '#10b981' : '#f2a900'; // Emerald & Gold
    charElements += `<text x="${x}" y="${y}" font-family="monospace, sans-serif" font-weight="900" font-size="26" fill="${color}" transform="rotate(${rot} ${x} ${y})">${char}</text>`;
  }

  // Noise lines
  let noiseLines = '';
  for (let i = 0; i < 3; i++) {
    const x1 = Math.random() * width;
    const y1 = Math.random() * height;
    const x2 = Math.random() * width;
    const y2 = Math.random() * height;
    noiseLines += `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="rgba(16,185,129,0.3)" stroke-width="1.5" />`;
  }

  // Noise dots
  let noiseDots = '';
  for (let i = 0; i < 20; i++) {
    const cx = Math.random() * width;
    const cy = Math.random() * height;
    noiseDots += `<circle cx="${cx}" cy="${cy}" r="1" fill="rgba(255,255,255,0.4)" />`;
  }

  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}" style="border-radius:10px;background:rgba(0,30,0,0.55);border:1px solid rgba(16,185,129,0.3);user-select:none;">
    ${noiseLines}
    ${noiseDots}
    ${charElements}
  </svg>`;

  return { token, svg };
}

export function verifyCaptcha(userAnswer: string, token: string): boolean {
  if (!userAnswer || !token) return false;

  try {
    const decoded = Buffer.from(token, 'base64').toString('utf-8');
    const [correctAnswer, timestampStr, hmac] = decoded.split(':');

    if (!correctAnswer || !timestampStr || !hmac) return false;

    // Check expiration (valid for 5 minutes)
    const timestamp = parseInt(timestampStr, 10);
    if (isNaN(timestamp) || Date.now() - timestamp > 5 * 60 * 1000) {
      return false;
    }

    // Verify HMAC integrity
    const expectedPayload = `${correctAnswer}:${timestamp}`;
    const expectedHmac = crypto.createHmac('sha256', CAPTCHA_SECRET).update(expectedPayload).digest('hex');

    if (crypto.timingSafeEqual(Buffer.from(hmac), Buffer.from(expectedHmac))) {
      return userAnswer.trim().toUpperCase() === correctAnswer.trim().toUpperCase();
    }
  } catch (e) {
    return false;
  }

  return false;
}
