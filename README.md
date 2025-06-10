This PHP script implements a secure site lock system designed to control access to your website. It provides a command-line-like interface for administrators to either lock (redirect all traffic to a 404 error page) or unlock their site.

Key Features:
IP-Based Access Control: Only pre-defined, allowed IP addresses can access the administrative panel, adding a crucial layer of security. If an unauthorized IP tries to access the panel, they will be shown a "403 Forbidden" error.

Secure Authentication:

Separate Credentials File: اطلاعات کاربری (نام کاربری و رمز عبور هش شده) در فایل جداگانه .secure_credentials.jpm ذخیره می‌شود. این کار با ایزوله کردن داده‌ها و مجوزهای سخت‌گیرانه (0600)، امنیت را افزایش داده و از دسترسی غیرمجاز جلوگیری می‌کند.

Encryption: The username itself is encrypted using AES-256-CBC, and the password is securely hashed using Argon2ID, a robust algorithm designed to resist brute-force attacks.

Timing Attack Protection: Login comparisons use hash_equals to prevent timing attacks.

Re-authentication: For critical actions like locking/unlocking the site, the system requires re-authentication, enhancing security against session hijacking.

Site Locking Mechanism:

.htaccess Integration: When the site is locked, the script dynamically adds RewriteRule entries to your .htaccess file. These rules redirect all public requests to a 404 Not Found error, effectively making the site inaccessible to regular visitors.

Exclusions: It includes exceptions for common WordPress administrative paths (wp-admin, wp-login.php) and a specific file (tasviehesab.php) to ensure administrators can still manage the site even when it's locked.

Persistence: The site's lock status is stored in a dedicated file (.site_lock_status.jpm), ensuring the setting persists across requests.

How it Works:
IP Check: Upon accessing the script, it first verifies if your current IP address is among the allowed_ips. If not, access is denied.

Authentication: If your IP is allowed, you're presented with a login prompt. You must enter the correct username and password.

Action Prompt: After successful login, the system displays the current site status and asks if you wish to LOCK or UNLOCK the site.

Confirmation & Execution: If you choose to change the status, you'll confirm your choice (y or n). The script then updates the .site_lock_status.jpm file and modifies the .htaccess file accordingly.

Randomized Credentials for Your Use:
For testing purposes, the system has been initialized with the following randomized credentials:

Allowed IPs:

104.244.42.1

185.199.108.153

203.0.113.45

Encryption Key (used internally by PHP): Secr3tK3y_J@mPr0gr@mm3r_R@nd0mStr!ng_2025_AbCd

Default Username: AdminUser_Jam

Default Password: P@ssw0rd_S3cur3!_2025_XyZ#

Important Note: In a production environment, you should always store the ENCRYPTION_KEY in environment variables and use a strong, unique username and password that are not hardcoded in the script.
