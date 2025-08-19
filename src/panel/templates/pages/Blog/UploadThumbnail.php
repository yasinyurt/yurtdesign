<?php
// Bu dosya doğrudan erişime açık DEĞİLDİR, sadece public_html/panel/index.php üzerinden çağrılır.

// AJAX isteği için JSON yanıtı döndüreceğiz
header('Content-Type: application/json');

// Hata raporlamayı aç (geliştirme aşamasında iyidir)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ROOT_PATH'i tanımla
// UploadThumbnail.php'nin yolu: src/panel/templates/pages/Blog/UploadThumbnail.php
// Proje kök dizinine (yurtdesign) ulaşmak için 4 seviye yukarı çıkmalıyız.
// __DIR__ -> src/panel/templates/pages/Blog
// dirname(__DIR__) -> src/panel/templates/pages
// dirname(dirname(__DIR__)) -> src/panel/templates
// dirname(dirname(dirname(____DIR__))) -> src/panel
// dirname(dirname(dirname(dirname(__DIR__)))) -> proje-kök-dizini (yurtdesign)
define('ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))));

// Admin panel config dosyasını dahil et (veritabanı bağlantısı için)
// ROOT_PATH artık proje köküdür. config.php'nin yolu: src/panel/config.php
require_once ROOT_PATH . '/src/panel/config.php';

// Kullanıcının giriş yapıp yapmadığını kontrol edelim
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => 0, 'message' => 'Yetkisiz erişim! Lütfen giriş yapın.']);
    exit();
}

// Yükleme dizini (FİZİKSEL SUNUCU YOLU OLMALI!)
// Sizde public_html klasörü yerine proje kökünde 'yurtdesign' adlı klasörün olduğunu belirtmiştiniz.
// Bu durumda, yüklenecek resimlerin yolu:
// proje-kök-dizini/yurtdesign/uploads/blog/thumbnails/
$uploadDir = ROOT_PATH . '/yurtdesign/uploads/blog/thumbnails/';

// Yükleme dizini yoksa veya yazılabilir değilse hata ver
if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
    echo json_encode(['success' => 0, 'message' => 'Yükleme dizini mevcut değil veya yazılabilir değil: ' . $uploadDir]);
    exit();
}

// Dosya yüklendi mi kontrol edelim (input name="thumbnail_file" olduğu için)
if (!isset($_FILES['thumbnail_file']) || $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => 0, 'message' => 'Dosya yüklenirken bir hata oluştu veya dosya seçilmedi.']);
    exit();
}

$file = $_FILES['thumbnail_file'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];

// Güvenli dosya adı oluşturma ve uzantı kontrolü
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif']; // Webp'ye dönüştüreceğiz, bu yüzden başlangıç formatları

if (!in_array($fileExt, $allowed)) {
    echo json_encode(['success' => 0, 'message' => 'Geçersiz dosya türü. Sadece JPG, JPEG, PNG, GIF dosyalarına izin verilir.']);
    exit();
}

// Dosya boyutunu kontrol et (örn. 5MB)
if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
    echo json_encode(['success' => 0, 'message' => 'Dosya boyutu çok büyük. Maksimum 5MB.']);
    exit();
}

// Benzersiz dosya adı oluştur ve .webp uzantısı ver
$fileNewName = uniqid('', true) . '.webp';
$fileDestination = $uploadDir . $fileNewName;

// --- Görseli WEBP formatına dönüştürme ve kaydetme ---
try {
    if (!extension_loaded('gd') && !function_exists('gd_info')) {
        echo json_encode(['success' => 0, 'message' => 'GD kütüphanesi yüklü değil. Resim dönüştürme mümkün değil.']);
        exit();
    }

    $image = null;
    switch ($fileExt) {
        case 'jpeg':
        case 'jpg':
            $image = imagecreatefromjpeg($fileTmpName);
            break;
        case 'png':
            $image = imagecreatefrompng($fileTmpName);
            break;
        case 'gif':
            $image = imagecreatefromgif($fileTmpName);
            break;
        default:
            echo json_encode(['success' => 0, 'message' => 'Desteklenmeyen orijinal resim formatı.']);
            exit();
    }

    if ($image === false) {
        echo json_encode(['success' => 0, 'message' => 'Görsel yüklenemedi veya dönüştürülemedi (imagecreatefrom hatası).']);
        exit();
    }

    // PNG'deki saydamlığı korumak için
    if ($fileExt == 'png') {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    // Kaliteyi belirle (0-100 arası, 80-90 genellikle iyi bir denge)
    $quality = 80;

    // Görseli WEBP olarak kaydet
    if (imagewebp($image, $fileDestination, $quality)) {
        imagedestroy($image); // Belleği temizle
        // Başarılı yükleme, URL'yi döndür
        echo json_encode([
            'success' => 1,
            'file_url' => BASE_URL . 'uploads/blog/thumbnails/' . $fileNewName, // Public erişimli URL
        ]);
        exit();
    } else {
        imagedestroy($image);
        echo json_encode(['success' => 0, 'message' => 'Görsel WEBP formatına dönüştürülüp kaydedilemedi (imagewebp hatası).']);
        exit();
    }

} catch (Exception $e) {
    error_log("Thumbnail yükleme/dönüştürme hatası: " . $e->getMessage());
    echo json_encode(['success' => 0, 'message' => 'Görsel işlenirken beklenmeyen bir hata oluştu: ' . $e->getMessage()]);
    exit();
}