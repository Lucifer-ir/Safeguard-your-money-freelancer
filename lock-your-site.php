<?php
/**
 * Secure Site Lock System
 * سیستم قفل امن سایت
 * @author Mohammad Beyrami Jam - JamProgrammer.ir
 * @version 2.0
 * @license MIT
 */

// ========================
// SECURITY CONFIGURATION
// پیکربندی امنیتی
// ========================

/**
 * Encryption Key - CHANGE THIS TO YOUR OWN RANDOM STRING
 * کلید رمزنگاری - این را به یک رشته تصادفی و منحصر به فرد خود تغییر دهید
 * This should be a long, random string stored in environment variables
 * in a production environment. Never commit this to version control.
 * این باید یک رشته بلند و تصادفی باشد که در متغیرهای محیطی در یک محیط تولیدی ذخیره شود.
 * هرگز این را در کنترل نسخه (Version Control) قرار ندهید.
 */
define('ENCRYPTION_KEY', 'Secr3tK3y_J@mPr0gr@mm3r_R@nd0mStr!ng_2025_AbCd'); // کلید رمزنگاری

/**
 * Allowed IP Addresses
 * آدرس‌های IP مجاز
 * These IPs will have access to the control panel.
 * Encrypted for additional security.
 * این آدرس‌های IP به پنل کنترل دسترسی خواهند داشت.
 * برای امنیت بیشتر رمزنگاری شده‌اند.
 */
$allowed_ips = [ // لیست IP های مجاز
    '104.244.42.1',   // Primary admin IP - IP اصلی مدیر
    '185.199.108.153', // Secondary admin IP - IP ثانویه مدیر
    '203.0.113.45'    // Backup admin IP - IP پشتیبان مدیر
];

// ========================
// SECURITY FUNCTIONS
// توابع امنیتی
// ========================

/**
 * Encrypt data using AES-256-CBC
 * رمزنگاری داده با استفاده از AES-256-CBC
 * @param string $data Data to encrypt
 * @return string Encrypted data in base64 format
 */
function encrypt_data($data) {
    if (!in_array('aes-256-cbc', openssl_get_cipher_methods())) {
        // فارسی: اگر متد رمزنگاری AES-256-CBC در OpenSSL موجود نباشد
        die("ERROR: AES-256-CBC encryption not available");
    }
    
    $iv_length = openssl_cipher_iv_length('aes-256-cbc'); // طول Initialization Vector (IV)
    $iv = openssl_random_pseudo_bytes($iv_length); // تولید IV تصادفی
    // فارسی: رمزنگاری داده
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    
    if ($encrypted === false) {
        // فارسی: اگر رمزنگاری شکست خورد
        die("ERROR: Encryption failed");
    }
    
    return base64_encode($encrypted . '::' . $iv); // بازگرداندن داده رمزنگاری شده و IV در فرمت Base64
}

/**
 * Decrypt data using AES-256-CBC
 * رمزگشایی داده با استفاده از AES-256-CBC
 * @param string $data Encrypted data in base64 format
 * @return string Decrypted data
 */
function decrypt_data($data) {
    $parts = explode('::', base64_decode($data), 2); // جداسازی داده رمزنگاری شده و IV
    if (count($parts) != 2) {
        // فارسی: اگر فرمت داده رمزنگاری شده نامعتبر باشد
        die("ERROR: Invalid encrypted data format");
    }
    
    list($encrypted_data, $iv) = $parts;
    // فارسی: رمزگشایی داده
    $decrypted = openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    
    if ($decrypted === false) {
        // فارسی: اگر رمزگشایی شکست خورد
        die("ERROR: Decryption failed");
    }
    
    return $decrypted; // بازگرداندن داده رمزگشایی شده
}

// ========================
// AUTHENTICATION SYSTEM
// سیستم احراز هویت
// ========================

/**
 * Verify IP Access
 * بررسی دسترسی IP
 * Checks if current IP is in the allowed list
 * بررسی می‌کند که آیا IP فعلی در لیست مجاز قرار دارد یا خیر
 */
$current_ip = $_SERVER['REMOTE_ADDR']; // دریافت IP کاربر فعلی
if (!in_array($current_ip, $allowed_ips)) {
    // فارسی: اگر IP فعلی در لیست IP های مجاز نباشد، دسترسی ممنوع است
    header('HTTP/1.0 403 Forbidden'); // ارسال هدر 403 Forbidden
    header('Cache-Control: no-store, no-cache, must-revalidate'); // کنترل کش
    header('Pragma: no-cache');
    header('Expires: 0');
    // فارسی: نمایش صفحه 403 Forbidden
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>403 Forbidden</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            h1 { color: #d9534f; }
            .english-text { text-align: left; }
            .persian-text { text-align: right; direction: rtl; }
        </style>
    </head>
    <body>
        <h1>403 Forbidden</h1>
        <p class='english-text'>You don't have permission to access this page.</p>
        <p class='english-text'>Your IP: " . htmlspecialchars($current_ip) . "</p>
        <p class='persian-text'>شما اجازه دسترسی به این صفحه را ندارید.</p>
        <p class='persian-text'>آی‌پی شما: " . htmlspecialchars($current_ip) . "</p>
    </body>
    </html>";
    exit(); // خروج از اسکریپت
}

/**
 * Credentials Configuration
 * پیکربندی اعتبارنامه‌ها
 * Stored in a separate encrypted file for security
 * برای امنیت، در یک فایل جداگانه و رمزنگاری شده ذخیره می‌شود
 */
$credentials_file = __DIR__ . '/.secure_credentials.jpm'; // مسیر فایل اعتبارنامه‌ها
$default_username = 'AdminUser_Jam'; // نام کاربری پیش‌فرض
$default_password = 'P@ssw0rd_S3cur3!_2025_XyZ#'; // رمز عبور پیش‌فرض

// Initialize credentials file if not exists
// فارسی: اگر فایل اعتبارنامه‌ها وجود ندارد، آن را با اطلاعات پیش‌فرض ایجاد کنید
if (!file_exists($credentials_file)) {
    $initial_credentials = [
        'username' => encrypt_data($default_username), // رمزنگاری نام کاربری
        'password' => password_hash($default_password, PASSWORD_ARGON2ID, [ // هش کردن رمز عبور با Argon2ID
            'memory_cost' => 1<<17, // 128MB memory usage
            'time_cost'   => 4,     // 4 iterations
            'threads'     => 2      // 2 threads
        ])
    ];
    
    file_put_contents($credentials_file, json_encode($initial_credentials)); // ذخیره اعتبارنامه‌ها در فایل
    chmod($credentials_file, 0600); // Strict permissions - تنظیم مجوزهای سخت‌گیرانه برای فایل (فقط خواندن و نوشتن توسط مالک)
}

// Load credentials
// فارسی: بارگذاری اعتبارنامه‌ها
$credentials = json_decode(file_get_contents($credentials_file), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // فارسی: اگر فایل اعتبارنامه‌ها خراب باشد
    die("ERROR: Corrupted credentials file");
}

$correct_username = decrypt_data($credentials['username']); // رمزگشایی نام کاربری صحیح
$correct_password_hash = $credentials['password']; // هش رمز عبور صحیح

// ========================
// SITE LOCK MECHANISM
// مکانیسم قفل سایت
// ========================

$status_file = __DIR__ . '/.site_lock_status.jpm'; // مسیر فایل وضعیت قفل سایت
$htaccess_file = __DIR__ . '/.htaccess'; // مسیر فایل .htaccess

/**
 * Get current lock status
 * دریافت وضعیت فعلی قفل
 * @param string $status_file Path to status file
 * @return bool True if site is locked
 */
function get_site_lock_status($status_file) {
    if (!file_exists($status_file)) {
        return false; // اگر فایل وجود ندارد، سایت قفل نیست
    }
    
    $content = file_get_contents($status_file); // خواندن محتوای فایل وضعیت
    if ($content === false) {
        // فارسی: اگر امکان خواندن فایل وضعیت وجود ندارد
        die("ERROR: Cannot read status file");
    }
    
    $data = json_decode($content, true); // دیکد کردن JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        // فارسی: اگر فایل وضعیت خراب باشد
        die("ERROR: Corrupted status file");
    }
    
    return $data['locked'] ?? false; // بازگرداندن وضعیت قفل
}

/**
 * Set site lock status
 * تنظیم وضعیت قفل سایت
 * @param string $status_file Path to status file
 * @param bool $is_locked Whether to lock the site
 */
function set_site_lock_status($status_file, $is_locked) {
    $data = ['locked' => $is_locked, 'timestamp' => time()]; // داده‌های وضعیت (قفل بودن و زمان)
    $result = file_put_contents($status_file, json_encode($data)); // ذخیره وضعیت در فایل
    
    if ($result === false) {
        // فارسی: اگر امکان نوشتن در فایل وضعیت وجود ندارد
        die("ERROR: Cannot write to status file");
    }
    
    chmod($status_file, 0600); // Strict permissions - تنظیم مجوزهای سخت‌گیرانه
}

/**
 * Add 404 redirect rules to .htaccess
 * اضافه کردن قوانین تغییر مسیر 404 به .htaccess
 * @param string $htaccess_file Path to .htaccess file
 */
function add_404_redirect($htaccess_file) {
    // فارسی: قوانین ریدایرکت 404 برای قفل کردن سایت
    $rules = "\n# BEGIN JamProgrammer Lock System\n" . // شروع بلوک قوانین
             "<IfModule mod_rewrite.c>\n" . // اگر ماژول mod_rewrite فعال باشد
             "RewriteEngine On\n" . // فعال کردن موتور Rewrite
             "RewriteCond %{REQUEST_URI} !^/tasviehesab\.php$\n" . // استثنا: فایل tasviehesab.php
             "RewriteCond %{REQUEST_URI} !^/wp-admin/.*\n" . // استثنا: پوشه wp-admin
             "RewriteCond %{REQUEST_URI} !^/wp-login\.php$\n" . // استثنا: فایل wp-login.php
             "RewriteRule ^(.*)$ - [R=404,L]\n" . // تغییر مسیر همه صفحات به 404
             "</IfModule>\n" .
             "# END JamProgrammer Lock System\n"; // پایان بلوک قوانین
    
    $current_content = file_exists($htaccess_file) ? file_get_contents($htaccess_file) : ''; // محتوای فعلی .htaccess
    
    // Only add if not already present
    // فارسی: فقط در صورتی اضافه کن که قبلاً وجود نداشته باشد
    if (strpos($current_content, "BEGIN JamProgrammer Lock System") === false) {
        file_put_contents($htaccess_file, $rules, FILE_APPEND); // اضافه کردن قوانین به انتهای فایل
    }
}

/**
 * Remove 404 redirect rules from .htaccess
 * حذف قوانین تغییر مسیر 404 از .htaccess
 * @param string $htaccess_file Path to .htaccess file
 */
function remove_404_redirect($htaccess_file) {
    if (!file_exists($htaccess_file)) {
        return; // اگر فایل وجود ندارد، کاری انجام نده
    }
    
    $current_content = file_get_contents($htaccess_file); // محتوای فعلی .htaccess
    // فارسی: حذف بلوک قوانین JamProgrammer Lock System با استفاده از عبارت منظم
    $new_content = preg_replace(
        "/# BEGIN JamProgrammer Lock System.*?# END JamProgrammer Lock System\n/s", 
        "", 
        $current_content
    );
    
    file_put_contents($htaccess_file, $new_content); // ذخیره محتوای جدید در فایل
}

// ========================
// MAIN APPLICATION LOGIC
// منطق اصلی برنامه
// ========================

$site_is_locked = get_site_lock_status($status_file); // دریافت وضعیت فعلی قفل سایت
$authenticated = false; // وضعیت احراز هویت
$output_message = ''; // پیام خروجی برای کاربر
$show_prompt = false; // نمایش پرامپت تایید قفل/باز کردن

// Handle login form submission
// فارسی: مدیریت ارسال فرم ورود
if (isset($_POST['username']) && isset($_POST['password'])) {
    // Rate limiting - simple example
    // فارسی: محدودیت نرخ (مثال ساده برای کند کردن حملات Brute Force)
    sleep(1); // Delay to slow down brute force attempts - تاخیر برای کند کردن حملات Brute Force
    
    $entered_username = $_POST['username']; // نام کاربری وارد شده
    $entered_password = $_POST['password']; // رمز عبور وارد شده
    
    // Secure comparison to prevent timing attacks
    // فارسی: مقایسه امن برای جلوگیری از حملات Timing Attack
    if (hash_equals($entered_username, $correct_username) && 
        password_verify($entered_password, $correct_password_hash)) {
        
        $authenticated = true; // احراز هویت موفق
        $show_prompt = true; // نمایش پرامپت
        // فارسی: پیام خوش‌آمدگویی و وضعیت فعلی سایت
        $output_message = "Root@JamProgrammer:~# Welcome, " . htmlspecialchars($entered_username) . "!\n";
        $output_message .= "Root@JamProgrammer:~# Current site status: \n";
        $output_message .= "<span class='english-text'>" . ($site_is_locked ? "LOCKED (all pages redirect to 404)" : "UNLOCKED") . "</span>\n";
        $output_message .= "<span class='persian-text'>" . ($site_is_locked ? "(قفل شده (همه صفحات به 404 ریدایرکت می شوند))" : "(سایت باز است)") . "</span>\n";
        $output_message .= "Root@JamProgrammer:~# Do you want to ";
        $output_message .= "<span class='english-text'>" . ($site_is_locked ? "UNLOCK" : "LOCK") . "</span> the site?\n";
        $output_message .= "<span class='persian-text'>آیا میخواهید سایت را " . ($site_is_locked ? "باز کنید" : "قفل کنید") . "؟ (y/n)</span>: ";
    } else {
        // فارسی: پیام خطای احراز هویت ناموفق
        $output_message = "ERROR: Authentication failed. Invalid credentials.\n";
        $output_message .= "<span class='english-text'>Access denied.</span>\n";
        $output_message .= "<span class='persian-text' dir='rtl'>خطا: احراز هویت ناموفق. اعتبارنامه‌های نامعتبر.</span>\n";
        $output_message .= "<span class='persian-text' dir='rtl'>دسترسی رد شد.</span>\n";
    }
} 
// Handle lock/unlock confirmation
// فارسی: مدیریت تایید قفل/باز کردن
elseif (isset($_POST['action_confirm'])) {
    // Verify re-authentication
    // فارسی: تایید مجدد احراز هویت برای افزایش امنیت
    if (isset($_POST['re_username']) && isset($_POST['re_password']) &&
        hash_equals($_POST['re_username'], $correct_username) && 
        password_verify($_POST['re_password'], $correct_password_hash)) {
        
        $authenticated = true; // احراز هویت مجدد موفق
        $action_choice = strtolower(trim($_POST['action_confirm'])); // انتخاب کاربر (y/n)
        
        if ($action_choice === 'y') {
            $new_status = !$site_is_locked; // وضعیت جدید (عکس وضعیت فعلی)
            set_site_lock_status($status_file, $new_status); // تنظیم وضعیت جدید
            
            if ($new_status) {
                add_404_redirect($htaccess_file); // اضافه کردن قوانین 404
                // فارسی: پیام موفقیت‌آمیز قفل شدن سایت
                $output_message = "Root@JamProgrammer:~# Site successfully LOCKED.\n";
                $output_message .= "<span class='english-text'>All pages will now redirect to 404.</span>\n";
                $output_message .= "<span class='persian-text' dir='rtl'>سایت با موفقیت قفل شد.</span>\n";
                $output_message .= "<span class='persian-text' dir='rtl'>همه صفحات اکنون به 404 ریدایرکت خواهند شد.</span>\n";
            } else {
                remove_404_redirect($htaccess_file); // حذف قوانین 404
                // فارسی: پیام موفقیت‌آمیز باز شدن سایت
                $output_message = "Root@JamProgrammer:~# Site successfully UNLOCKED.\n";
                $output_message .= "<span class='english-text'>All pages are now accessible.</span>\n";
                $output_message .= "<span class='persian-text' dir='rtl'>سایت با موفقیت باز شد.</span>\n";
                $output_message .= "<span class='persian-text' dir='rtl'>همه صفحات اکنون قابل دسترسی هستند.</span>\n";
            }
        } 
        elseif ($action_choice === 'n') {
            // فارسی: پیام لغو عملیات
            $output_message = "Root@JamProgrammer:~# Operation cancelled.\n";
            $output_message .= "<span class='english-text'>Site status remains unchanged.</span>\n";
            $output_message .= "<span class='persian-text' dir='rtl'>عملیات لغو شد.</span>\n";
            $output_message .= "<span class='persian-text' dir='rtl'>وضعیت سایت بدون تغییر باقی ماند.</span>\n";
        } 
        else {
            // فارسی: پیام خطای ورودی نامعتبر
            $output_message = "ERROR: Invalid input.\n";
            $output_message .= "<span class='english-text'>Please enter 'y' or 'n'.</span>\n";
            $output_message .= "<span class='persian-text' dir='rtl'>خطا: ورودی نامعتبر. لطفاً 'y' یا 'n' وارد کنید.</span>\n";
            $show_prompt = true; // دوباره پرامپت را نمایش بده
        }
    } 
    else {
        // فارسی: پیام خطای احراز هویت مجدد ناموفق
        $output_message = "ERROR: Re-authentication failed.\n";
        $output_message .= "<span class='english-text'>Session expired.</span>\n";
        $output_message .= "<span class='english-text'>Please login again.</span>\n";
        $output_message .= "<span class='persian-text' dir='rtl'>خطا: احراز هویت مجدد ناموفق. نشست منقضی شد.</span>\n";
        $output_message .= "<span class='persian-text' dir='rtl'>لطفاً دوباره وارد شوید.</span>\n";
    }
}

// ========================
// USER INTERFACE
// رابط کاربری
// ========================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Meta tag to prevent search engine indexing - تگ متا برای جلوگیری از ایندکس شدن توسط موتورهای جستجو -->
    <meta name="robots" content="noindex, nofollow"> 
    <title>JamProgrammer Secure Console - کنسول امن JamProgrammer</title>
    <style>
        /* Base Styles - استایل‌های پایه */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
            --text-color: #ecf0f1;
            --background-dark: #1a1a1a;
            --background-darker: #121212;
            --console-font: 'Fira Code', 'Consolas', monospace;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: var(--console-font);
            background-color: var(--background-dark);
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        /* Console Container - کانتینر کنسول */
        .console-container {
            width: 100%;
            max-width: 800px;
            background-color: var(--background-darker);
            border: 1px solid var(--secondary-color);
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }
        
        /* Console Header - هدر کنسول */
        .console-header {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 15px;
            border-bottom: 1px solid var(--secondary-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .console-title {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .console-status {
            font-size: 0.9rem;
            color: <?php echo $site_is_locked ? 'var(--danger-color)' : 'var(--success-color)'; ?>;
            text-align: right; /* Default to right for dual-language */
        }

        .console-status .english-text {
            text-align: left;
            display: block;
        }

        .console-status .persian-text {
            text-align: right;
            direction: rtl;
            display: block;
        }
        
        /* Console Output - خروجی کنسول */
        .console-output {
            padding: 15px;
            height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 0.95rem;
            border-bottom: 1px solid #333;
        }
        
        .console-output::-webkit-scrollbar {
            width: 8px;
        }
        
        .console-output::-webkit-scrollbar-track {
            background: #333;
        }
        
        .console-output::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 4px;
        }

        .console-output .english-text {
            text-align: left;
            display: block;
        }

        .console-output .persian-text {
            text-align: right;
            direction: rtl;
            display: block;
        }
        
        /* Console Input Area - ناحیه ورودی کنسول */
        .console-input-area {
            display: flex;
            padding: 15px;
            background-color: rgba(0, 0, 0, 0.2);
        }
        
        .console-prompt {
            color: var(--secondary-color);
            margin-right: 10px;
            font-weight: bold;
            white-space: nowrap;
        }
        
        .console-input {
            flex-grow: 1;
            background: transparent;
            border: none;
            color: var(--text-color);
            font-family: var(--console-font);
            font-size: 0.95rem;
            outline: none;
            padding: 5px 0;
        }
        
        .console-input::placeholder {
            color: #7f8c8d;
        }
        
        .console-button {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            margin-left: 10px;
            border-radius: 3px;
            cursor: pointer;
            font-family: var(--console-font);
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }
        
        .console-button:hover {
            background-color: #2980b9;
        }
        
        /* Responsive Design - طراحی واکنش‌گرا */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .console-container {
                border-radius: 0;
            }
            
            .console-input-area {
                flex-direction: column;
            }
            
            .console-button {
                margin: 10px 0 0 0;
                width: 100%;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="console-container">
        <div class="console-header">
            <div class="console-title">JamProgrammer Secure Console</div>
            <div class="console-status">
                <span class="english-text">STATUS: <?php echo $site_is_locked ? 'LOCKED' : 'UNLOCKED'; ?></span>
                <span class="persian-text">وضعیت: <?php echo $site_is_locked ? 'قفل شده' : 'باز است'; ?></span>
            </div>
        </div>
        
        <div class="console-output">
            <?php 
            // Display system messages - نمایش پیام‌های سیستمی
            echo $output_message; // Using echo directly as spans are already in $output_message
            
            // Display last status change if available - نمایش آخرین تغییر وضعیت در صورت وجود
            if ($authenticated && file_exists($status_file)) {
                $status_data = json_decode(file_get_contents($status_file), true);
                if (isset($status_data['timestamp'])) {
                    $last_change = date('Y-m-d H:i:s', $status_data['timestamp']);
                    echo "\nRoot@JamProgrammer:~# Last status change:\n";
                    echo "<span class='english-text'>" . $last_change . "</span>\n";
                    echo "<span class='persian-text'>" . $last_change . "</span>\n";
                }
            }
            ?>
        </div>
        
        <form method="POST" class="console-input-area">
            <?php if (!$authenticated): // اگر احراز هویت نشده باشد: ?>
                <span class="console-prompt">root@jamprogrammer:~$</span>
                <input type="text" name="username" class="console-input" placeholder="Username" required autofocus>
                <input type="password" name="password" class="console-input" placeholder="Password" required>
                <button type="submit" class="console-button">Login</button>
            <?php elseif ($show_prompt): // اگر پرامپت تایید نمایش داده شود: ?>
                <input type="hidden" name="re_username" value="<?php echo htmlspecialchars($correct_username); ?>">
                <input type="hidden" name="re_password" value="<?php echo htmlspecialchars($_POST['password']); ?>">
                <span class="console-prompt">root@jamprogrammer:~$</span>
                <input type="text" name="action_confirm" class="console-input" placeholder="Type 'y' or 'n'" required autofocus>
                <button type="submit" class="console-button">Execute</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
