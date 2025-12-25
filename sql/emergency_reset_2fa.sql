-- =====================================================
-- EMERGENCY 2FA RESET FOR SUPER ADMIN
-- =====================================================
-- Use this ONLY if you are locked out of your account
-- Run this in phpMyAdmin or MySQL command line
--
-- WARNING: This will disable 2FA for the specified user
-- =====================================================

-- Step 1: Find your user ID (replace 'your-email@example.com' with your actual email)
SELECT id, email, first_name, last_name, twofa_enabled
FROM users
WHERE email = 'your-email@example.com';

-- Step 2: Reset 2FA for the user ID from Step 1
-- REPLACE 'YOUR_USER_ID' with the actual ID number from Step 1
UPDATE users
SET twofa_secret = NULL,
    twofa_enabled = 0,
    twofa_verified_at = NULL
WHERE id = YOUR_USER_ID;

-- Step 3: Verify the reset was successful
SELECT id, email, first_name, last_name, twofa_enabled, twofa_secret
FROM users
WHERE id = YOUR_USER_ID;

-- You should see:
-- - twofa_enabled = 0
-- - twofa_secret = NULL
--
-- Now you can login without 2FA and will be prompted to set it up again
