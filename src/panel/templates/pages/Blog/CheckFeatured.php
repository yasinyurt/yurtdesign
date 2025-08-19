<?php
ob_start(); // Çıktı tamponlamayı başlat

// Bu dosya doğrudan erişime açık DEĞİLDİR, sadece AJAX isteği ile çağrılır.
header('Content-Type: application/json');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Kullanıcının giriş yapıp yapmadığını kontrol edelim
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ob_end_clean();
    echo json_encode(['success' => 0, 'message' => 'Yetkisiz erişim.', 'is_featured_exists' => false]);
    exit();
}

// ROOT_PATH tanımı public_html/panel/index.php'den geliyor.
require_once ROOT_PATH . '/src/panel/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // is_featured = 1 olan herhangi bir blog yazısı var mı kontrol et
        $stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE is_featured = 1 LIMIT 1");
        $count = $stmt->fetchColumn();

        ob_end_clean();
        echo json_encode(['success' => 1, 'is_featured_exists' => ($count > 0)]);
        exit();

    } catch (PDOException $e) {
        error_log("Öne çıkarılan blog kontrol edilirken veritabanı hatası: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Veritabanı hatası.', 'is_featured_exists' => false]);
        exit();
    }
} else {
    ob_end_clean();
    echo json_encode(['success' => 0, 'message' => 'Geçersiz istek metodu.', 'is_featured_exists' => false]);
    exit();
}
?>