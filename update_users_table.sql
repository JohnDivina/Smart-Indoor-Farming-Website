-- Add email verification and last login tracking to users table

-- Add email_verified column (0 = not verified, 1 = verified)
ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER email;

-- Add last_login column to track user activity
ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER email_verified;

-- Migrate existing users (mark as verified and set last login to now)
UPDATE users SET email_verified = 1 WHERE email_verified = 0 OR email_verified IS NULL;
UPDATE users SET last_login = NOW() WHERE last_login IS NULL;

-- Verify changes
SELECT 'Migration completed successfully!' AS status;
SELECT COUNT(*) AS total_users, 
       SUM(email_verified) AS verified_users,
       COUNT(last_login) AS users_with_login_time
FROM users;
