<?php

// Hata raporlamayı aç (geliştirme aşamasında iyidir)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', '/yurtdesign/'); // Ana domain için
define('ADMIN_URL', '/yurtdesign/admin/'); // Admin paneli için

// Projenin sunucudaki kök dizin yolu.
define('ROOT_PATH', dirname(__DIR__) . '/yurtdesign'); 

// Veritabanı ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'yurtdesign_cms');
define('DB_USER', 'root'); // Hosting'de değiştirin
define('DB_PASS', ''); // Hosting'de değiştirin

// Güvenlik ayarları
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('UPLOAD_URL', '/yurtdesign/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Session ayarları
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Local için false
ini_set('session.use_strict_mode', 1);