<?php

// Hata raporlamayı aç (geliştirme aşamasında iyidir)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost'); // Genellikle localhost'tur
define('DB_NAME', 'yurtdesign'); // phpMyAdmin'de oluşturduğunuz veritabanı adı
define('DB_USER', 'root'); // MySQL kullanıcı adınız (XAMPP varsayılanı 'root'tur)
define('DB_PASS', ''); // MySQL şifreniz (XAMPP varsayılanı boş şifredir)

// PDO (PHP Data Objects) ile veritabanı bağlantısı.
// Bağlantı başarısız olursa hata fırlatır.
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // Hata modunu istisna olarak ayarlamak, hataları yakalamamızı sağlar.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Sonuç setindeki sayısal değerleri string olarak değil, doğru PHP türleri olarak getirmesini sağlar.
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    // Bağlantı hatası durumunda mesajı göster ve scripti sonlandır.
    // Geliştirme aşamasında bu hata mesajını gösterebiliriz, üretimde daha jenerik bir mesaj olmalı.
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

?>