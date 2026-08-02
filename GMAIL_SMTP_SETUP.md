# Gmail SMTP Configuration for OTP Emails

## Quick Setup Guide

### Step 1: Enable Gmail App Password

1. **Go to Google Account Settings**: https://myaccount.google.com/
2. **Enable 2-Step Verification**:
   - Click "Security" in the left menu
   - Under "Signing in to Google", click "2-Step Verification"
   - Follow the prompts to enable it
3. **Generate App Password**:
   - Go back to Security settings
   - Under "Signing in to Google", click "App passwords"
   - Select "Mail" and "Other (Custom name)"
   - Enter "Smart Farm OTP" as the name
   - Click "Generate"
   - **Copy the 16-character password** (format: xxxx xxxx xxxx xxxx)

### Step 2: Update send_otp_email.php

Open `send_otp_email.php` and update lines 48-49:

```php
$mail->Username   = 'youremail@gmail.com';        // Your Gmail address
$mail->Password   = 'abcd efgh ijkl mnop';        // Your 16-character App Password
```

**Example**:
```php
$mail->Username   = 'smartfarm2026@gmail.com';
$mail->Password   = 'qwer tyui asdf ghjk';
```

### Step 3: Test the System

1. **Run database setup** (if not done):
   ```
   http://localhost/smartfarm2/setup_otp_database.php
   ```

2. **Test login**:
   ```
   http://localhost/smartfarm2/login.php
   ```

3. **Enter credentials**:
   - Username/Email/Number: (your test account)
   - Password: (your test password)

4. **Check for OTP email** in the registered email inbox

### Step 4: Debug Mode

The system is currently in **debug mode** for localhost testing.

**Debug output location**: `C:\xampp\php\logs\php_error_log`

**To view debug output**:
- Check the PHP error log file
- Or watch the browser console (if errors occur)

**After successful testing**, disable debug mode in `send_otp_email.php`:
```php
$mail->SMTPDebug = 0; // Change from 2 to 0
```

### Step 5: Remove Localhost SSL Bypass (Production)

**IMPORTANT**: Before deploying to production, remove the SSL bypass:

In `send_otp_email.php`, **DELETE** these lines (currently lines 58-64):
```php
// Localhost SSL fix (ONLY for development - REMOVE IN PRODUCTION)
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
```

## Troubleshooting

### Error: "SMTP connect() failed"

**Solution**: 
- Verify Gmail credentials are correct
- Ensure 2-Step Verification is enabled
- Use App Password, NOT your regular Gmail password
- Check firewall allows outbound port 587

### Error: "Invalid credentials"

**Solution**:
- Double-check the App Password (16 characters, no spaces in code)
- Regenerate App Password if needed
- Ensure you're using the correct Gmail address

### Error: "Could not authenticate"

**Solution**:
- Gmail may block "less secure apps"
- Use App Password (not regular password)
- Check if Gmail account is locked or suspended

### Email not received

**Solution**:
- Check spam/junk folder
- Verify the user's email address in database is correct
- Check PHP error log for SMTP errors
- Ensure Gmail account has sending limits available

### SSL Certificate Error

**Solution**:
- The localhost SSL bypass should handle this
- If still occurring, verify `$mail->SMTPOptions` is set correctly
- Check that OpenSSL extension is enabled in PHP

## Testing Checklist

- [ ] Gmail App Password generated
- [ ] `send_otp_email.php` updated with credentials
- [ ] Database table `login_otps` created
- [ ] Test login with username → OTP received
- [ ] Test login with email → OTP received
- [ ] Test login with phone number → OTP received
- [ ] OTP verification works correctly
- [ ] Debug mode disabled after testing
- [ ] SSL bypass removed before production

## Security Notes

1. **Never commit credentials to version control**
   - Add `send_otp_email.php` to `.gitignore`
   - Or use environment variables

2. **App Password vs Regular Password**
   - Always use App Password for SMTP
   - Never use your regular Gmail password in code

3. **Production Deployment**
   - Remove debug mode (`SMTPDebug = 0`)
   - Remove SSL bypass (`SMTPOptions`)
   - Use environment variables for credentials
   - Enable HTTPS on your server

4. **Gmail Sending Limits**
   - Free Gmail: ~500 emails/day
   - Google Workspace: ~2000 emails/day
   - Consider SendGrid/Mailgun for high volume

## Alternative SMTP Providers

If Gmail doesn't work or you need higher limits:

### SendGrid (Recommended for Production)
```php
$mail->Host       = 'smtp.sendgrid.net';
$mail->Username   = 'apikey';
$mail->Password   = 'YOUR_SENDGRID_API_KEY';
$mail->Port       = 587;
```

### Mailgun
```php
$mail->Host       = 'smtp.mailgun.org';
$mail->Username   = 'postmaster@your-domain.mailgun.org';
$mail->Password   = 'YOUR_MAILGUN_PASSWORD';
$mail->Port       = 587;
```

### Outlook/Hotmail
```php
$mail->Host       = 'smtp-mail.outlook.com';
$mail->Username   = 'your-email@outlook.com';
$mail->Password   = 'your-password';
$mail->Port       = 587;
```
