# Email OTP Authentication - Configuration Guide

## SMTP Email Configuration

To enable email OTP functionality, you need to configure SMTP settings in `send_otp_email.php`.

### Option 1: Gmail (Recommended for Testing)

1. **Enable 2-Factor Authentication** on your Gmail account
2. **Generate App Password**:
   - Go to Google Account Settings → Security
   - Under "2-Step Verification", click "App passwords"
   - Select "Mail" and "Other (Custom name)"
   - Copy the 16-character password

3. **Update send_otp_email.php** (lines 37-42):
```php
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'your-email@gmail.com';  // Your Gmail address
$mail->Password   = 'xxxx xxxx xxxx xxxx';   // Your App Password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

### Option 2: SendGrid (Recommended for Production)

1. Sign up at https://sendgrid.com
2. Create an API key
3. Update send_otp_email.php:
```php
$mail->Host       = 'smtp.sendgrid.net';
$mail->SMTPAuth   = true;
$mail->Username   = 'apikey';
$mail->Password   = 'YOUR_SENDGRID_API_KEY';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

### Option 3: Other SMTP Services

- **Mailgun**: smtp.mailgun.org, port 587
- **Amazon SES**: email-smtp.us-east-1.amazonaws.com, port 587
- **Outlook**: smtp-mail.outlook.com, port 587

## Database Setup

Run the setup script to create the required table:

1. Open browser: `http://localhost/smartfarm2/setup_otp_database.php`
2. Verify all checks pass ✅
3. Delete or secure the setup file after running

## Testing the System

### Test 1: Login with Username
1. Go to `http://localhost/smartfarm2/login.php`
2. Enter username + password
3. Check email for OTP
4. Enter OTP on verification page
5. Should redirect to dashboard

### Test 2: Login with Email
1. Enter email address + password
2. Verify OTP email received
3. Complete verification

### Test 3: Login with Phone Number
1. Enter phone number + password
2. Verify OTP email received
3. Complete verification

### Test 4: Security Features
- Try wrong password → Should show "Invalid credentials"
- Try wrong OTP 3 times → Should block and require new code
- Wait 6 minutes → OTP should expire
- Try 5 failed logins → Should rate limit for 15 minutes

## Troubleshooting

### Email Not Sending
- Check SMTP credentials are correct
- Verify firewall allows outbound SMTP (port 587)
- Check PHP error logs: `C:\xampp\php\logs\php_error_log`
- Enable error logging in send_otp_email.php

### OTP Not Working
- Verify `login_otps` table exists
- Check OTP hasn't expired (5 minutes)
- Ensure attempts < 3
- Check database for OTP records

### Rate Limiting Issues
- Clear session: Close browser completely
- Or wait 15 minutes for reset
- Check `$_SESSION['login_attempts']`

## Security Recommendations

1. **Use HTTPS in production** - Protects credentials in transit
2. **Change default database password** - Don't use empty password
3. **Implement CAPTCHA** - Prevent automated attacks
4. **Log failed attempts** - Monitor for suspicious activity
5. **Use environment variables** - Don't hardcode SMTP credentials

## File Checklist

- ✅ `login.php` - Updated with identifier detection
- ✅ `verify_otp.php` - OTP verification page
- ✅ `send_otp_email.php` - Email sending function
- ✅ `setup_otp_database.php` - Database setup script
- ✅ `create_otp_table.sql` - SQL migration file

## Support

If you encounter issues:
1. Check PHP error logs
2. Verify database connection
3. Test SMTP credentials separately
4. Review browser console for JavaScript errors
