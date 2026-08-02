# ✅ OTP Email System - Ready for Configuration

## Current Status

The OTP email system is **correctly implemented** and follows the exact flow you requested:

### ✅ Correct Flow (Already Implemented)

```
1. User enters identifier (username/email/number) + password
         ↓
2. System detects identifier type
         ↓
3. Fetch user from database
         ↓
4. Verify password (MD5 hash)
         ↓
5. ✅ PASSWORD VERIFIED → Generate 6-digit OTP
         ↓
6. Hash OTP with password_hash()
         ↓
7. Store in database (user_id, otp_hash, expires_at = +5 minutes)
         ↓
8. Send OTP via PHPMailer (Gmail SMTP)
         ↓
9. Redirect to verify_otp.php
```

**Location**: `login.php` lines 67-90

---

## 🔧 What You Need to Do

### Step 1: Get Gmail App Password

1. Go to: https://myaccount.google.com/security
2. Enable **2-Step Verification**
3. Go to **App passwords**
4. Generate password for "Mail" → "Other (Smart Farm)"
5. **Copy the 16-character password** (e.g., `abcd efgh ijkl mnop`)

### Step 2: Update Credentials

Open `send_otp_email.php` and update **lines 56-57**:

```php
$mail->Username   = 'youremail@gmail.com';        // Your Gmail
$mail->Password   = 'abcd efgh ijkl mnop';        // Your App Password
```

### Step 3: Run Database Setup

Open in browser:
```
http://localhost/smartfarm2/setup_otp_database.php
```

Verify you see: ✅ Table 'login_otps' created successfully

### Step 4: Test Login

1. Go to: `http://localhost/smartfarm2/login.php`
2. Enter username/email/number + password
3. **Check email** for 6-digit OTP
4. Enter OTP on verification page

---

## 🐛 Debug Mode (Currently Enabled)

**Debug output location**: `C:\xampp\php\logs\php_error_log`

The system will log all SMTP communication to help you troubleshoot.

**After successful testing**, disable debug in `send_otp_email.php` line 62:
```php
$mail->SMTPDebug = 0; // Change from 2 to 0
```

---

## 🔒 Security Features (Already Implemented)

✅ Email sent **ONLY after password verification**  
✅ OTP stored **hashed** (never plain text)  
✅ OTP expires after **5 minutes**  
✅ Maximum **3 verification attempts**  
✅ Rate limiting: **5 login attempts per 15 minutes**  
✅ Generic error messages (no information disclosure)  

---

## 📧 Email Configuration Details

**Current Settings** (in `send_otp_email.php`):

| Setting | Value |
|---------|-------|
| Host | `smtp.gmail.com` |
| Port | `587` |
| Security | `STARTTLS` |
| Auth | `true` |
| Debug | `2` (enabled for localhost) |
| SSL Verify | `false` (localhost only) |

---

## 🚨 Localhost-Specific Settings

These settings are **ONLY for localhost testing**:

### 1. Debug Mode (Line 62)
```php
$mail->SMTPDebug = 2;
```
**Remove in production**: Set to `0`

### 2. SSL Bypass (Lines 67-73)
```php
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
```
**Remove in production**: Delete entire block

---

## ✅ Verification Checklist

Before testing:
- [ ] Gmail App Password generated
- [ ] `send_otp_email.php` updated with credentials (lines 56-57)
- [ ] Database setup script run (`setup_otp_database.php`)
- [ ] Test user account exists with valid email

After testing:
- [ ] OTP email received successfully
- [ ] OTP verification works
- [ ] Debug mode disabled (`SMTPDebug = 0`)
- [ ] SSL bypass removed (delete `SMTPOptions` block)

---

## 🔍 Troubleshooting

### "SMTP connect() failed"
- Check Gmail credentials
- Ensure App Password (not regular password)
- Verify firewall allows port 587

### "Authentication failed"
- Regenerate App Password
- Copy password without spaces
- Ensure 2-Step Verification enabled

### Email not received
- Check spam folder
- Verify user's email in database
- Check `C:\xampp\php\logs\php_error_log`

### SSL Certificate errors
- Verify `SMTPOptions` is set (lines 67-73)
- Check OpenSSL enabled in `php.ini`

---

## 📝 Quick Reference

**Files involved**:
- `login.php` - Password verification → OTP generation
- `send_otp_email.php` - OTP email sending (UPDATE THIS)
- `verify_otp.php` - OTP verification page
- `setup_otp_database.php` - Database setup (RUN ONCE)

**Database table**: `login_otps`

**SMTP Provider**: Gmail (smtp.gmail.com:587)

**Next step**: Update Gmail credentials in `send_otp_email.php`
