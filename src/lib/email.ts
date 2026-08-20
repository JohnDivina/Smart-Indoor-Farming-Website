import nodemailer from 'nodemailer';

function getTransporter() {
  const user = (process.env.GMAIL_USER || process.env.SMTP_USER || '').trim();
  let pass = (process.env.GMAIL_APP_PASSWORD || process.env.SMTP_PASSWORD || process.env.SMTP_PASS || '').trim();
  // Strip all whitespace from Google App Passwords (e.g. 'xxxx yyyy zzzz wwww' -> 'xxxxyyyyzzzzwwww')
  pass = pass.replace(/\s+/g, '');

  if (!user || !pass) {
    return null;
  }

  return {
    user,
    transporter: nodemailer.createTransport({
      host: process.env.SMTP_HOST || 'smtp.gmail.com',
      port: Number(process.env.SMTP_PORT) || 465,
      secure: process.env.SMTP_SECURE === 'true' || (Number(process.env.SMTP_PORT) || 465) === 465,
      auth: {
        user,
        pass,
      },
    }),
  };
}

interface SendOTPEmailParams {
  toEmail: string;
  username: string;
  otp: string;
  reason?: 'account_creation' | 'password_reset' | 'login' | 'account_deletion';
}

export async function sendOTPEmail({
  toEmail,
  username,
  otp,
  reason = 'account_creation',
}: SendOTPEmailParams): Promise<{ success: boolean; message: string }> {
  const reasonTitles = {
    account_creation: 'Verify Your Email Address',
    password_reset: 'Reset Your Password',
    login: 'Two-Factor Login Code',
    account_deletion: 'Confirm Account Deletion',
  };

  const reasonDescriptions = {
    account_creation: 'Thank you for registering with CLSU Smart Farm Dashboard. Use the OTP code below to verify your email address.',
    password_reset: 'We received a request to reset your password. Use the OTP code below to proceed with setting a new password.',
    login: 'Use the following one-time code to complete your secure login.',
    account_deletion: 'You have requested to permanently delete your Smart Farm account. Use the code below to confirm this action.',
  };

  const title = reasonTitles[reason] || 'Your Verification Code';
  const description = reasonDescriptions[reason] || 'Here is your one-time verification code:';

  const htmlContent = `
    <!DOCTYPE html>
    <html>
      <head>
        <meta charset="utf-8">
        <style>
          body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #1a232c; }
          .card { max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 36px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
          .header { text-align: center; margin-bottom: 28px; }
          .brand { font-size: 20px; font-weight: 800; color: #006600; }
          .brand-sub { font-size: 12px; color: #718096; text-transform: uppercase; letter-spacing: 1px; }
          .otp-box { background: #f0fdf4; border: 2px dashed #006600; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0; }
          .otp-code { font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #006600; margin: 0; font-family: monospace; }
          .footer { font-size: 12px; color: #a0aec0; text-align: center; margin-top: 32px; border-top: 1px solid #edf2f7; padding-top: 16px; }
        </style>
      </head>
      <body>
        <div class="card">
          <div class="header">
            <div class="brand">CLSU SMART FARM</div>
            <div class="brand-sub">Indoor Precision Agriculture</div>
          </div>
          <h2 style="color: #1a232c; font-size: 18px; margin-bottom: 12px;">Hello, ${username}!</h2>
          <p style="color: #4a5568; font-size: 14px; line-height: 1.6;">${description}</p>
          <div class="otp-box">
            <div class="otp-code">${otp}</div>
          </div>
          <p style="color: #718096; font-size: 13px; text-align: center;">This code will expire in <strong>10 minutes</strong>. If you did not make this request, please ignore this email.</p>
          <div class="footer">
            &copy; ${new Date().getFullYear()} Central Luzon State University - Smart Indoor Farming Project.
          </div>
        </div>
      </body>
    </html>
  `;

  const transportConfig = getTransporter();

  // Fallback in case Gmail credentials are not configured yet during dev/testing
  if (!transportConfig) {
    console.log(`\n========================================`);
    console.log(`[SMTP DEV SIMULATION] OTP Email to ${toEmail}`);
    console.log(`Username: ${username} | Reason: ${reason}`);
    console.log(`Verification OTP Code: ${otp}`);
    console.log(`========================================\n`);
    return {
      success: true,
      message: 'OTP generated (logged to server console in dev mode).',
    };
  }

  try {
    await transportConfig.transporter.sendMail({
      from: `"CLSU Smart Farm" <${transportConfig.user}>`,
      to: toEmail,
      subject: `[CLSU Smart Farm] ${title} - ${otp}`,
      html: htmlContent,
    });
    return { success: true, message: 'Verification email sent successfully.' };
  } catch (error: any) {
    console.error('SMTP SendMail Error:', error);
    return {
      success: false,
      message: error?.message || 'Failed to send verification email.',
    };
  }
}
