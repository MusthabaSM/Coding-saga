<?php
// config.php — Web Sprint (Razorpay only)

// ==========================
// DATABASE CONFIG
// ==========================
define('DB_HOST','127.0.0.1');
define('DB_NAME','websprint_db');
define('DB_USER','root');
define('DB_PASS','');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ==========================
// APP CONFIG
// ==========================

// Base URL of your site
define('BASE_URL','http://localhost/websprint');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// ==========================
// RAZORPAY CONFIG (INR ONLY)
// ==========================
define('RZP_KEY_ID',     'rzp_test_RqGL7soLtQ3KmC');
define('RZP_KEY_SECRET', 'fo5VvhESKvpD1snewZk6yxFf');

// ==========================
// OPTIONAL DIRECTORIES
// ==========================
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('INVOICE_DIR', UPLOAD_DIR . '/invoices');

if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

if (!file_exists(INVOICE_DIR)) {
    mkdir(INVOICE_DIR, 0755, true);
}
// ==========================
// SIMPLE ADMIN LOGIN
// ==========================
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123'); // change this to something better

