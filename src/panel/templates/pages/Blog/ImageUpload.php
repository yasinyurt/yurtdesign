<?php

// Bu dosya doğrudan erişime açık DEĞİLDİR, sadece public_html/panel/index.php üzerinden çağrılır.
header('Content-Type: application/json'); // Bu dosyadan gelen her şey JSON olarak işaretlensin

// Hata raporlamayı aç (geliştirme aşamasında iyidir)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ROOT_PATH tanımı buradan kaldırıldı, zaten public_html/panel/index.php'den geliyor.
// Admin panel config dosyasını dahil et (veritabanı bağlantısı için)
// ROOT_PATH artık proje köküdür. config.php'nin yolu: src/panel/config.php
require_once ROOT_PATH . '/src/panel/config.php';

// Kullanıcının giriş yapıp yapmadığını kontrol edelim
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => 0, 'message' => 'Yetkisiz erişim!']);
    exit();
}

// Yükleme dizini (FİZİKSEL SUNUCU YOLU OLMALI!)
// Sizde public_html klasörü yerine proje kökünde 'yurtdesign' adlı klasörün olduğunu belirtmiştiniz.
// Bu durumda, yüklenecek resimlerin yolu:
// proje-kök-dizini/yurtdesign/uploads/blog/
$uploadDir = ROOT_PATH . '/yurtdesign/uploads/blog/';

// Yükleme dizini yoksa veya yazılabilir değilse hata ver
if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
    echo json_encode(['success' => 0, 'message' => 'Yükleme dizini mevcut değil veya yazılabilir değil: ' . $uploadDir]);
    exit();
}

// Editor.js'den gelen dosya yüklendi mi kontrol edelim
// Editor.js, Image Tool kullanıldığında dosyayı 'image' adında gönderir.
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => 0, 'message' => 'Dosya yüklenirken bir hata oluştu.']);
    exit();
}

$file = $_FILES['image'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];
$fileType = $file['type'];

// Güvenli dosya adı oluşturma ve uzantı kontrolü
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // İzin verilen uzantılar

if (!in_array($fileExt, $allowed)) {
    echo json_encode(['success' => 0, 'message' => 'Geçersiz dosya türü. Sadece JPG, JPEG, PNG, GIF, WEBP dosyalarına izin verilir.']);
    exit();
}

// Dosya boyutunu kontrol et (örn. 5MB = 5 * 1024 * 1024 bytes)
if ($fileSize > 5 * 1024 * 1024) {
    echo json_encode(['success' => 0, 'message' => 'Dosya boyutu çok büyük. Maksimum 5MB.']);
    exit();
}

// Benzersiz dosya adı oluştur
$fileNewName = uniqid('', true) . '.' . $fileExt;
$fileDestination = $uploadDir . $fileNewName;

// Dosyayı geçici konumdan kalıcı konuma taşı
if (move_uploaded_file($fileTmpName, $fileDestination)) {
    // Başarılı yükleme, Editor.js'nin beklediği formatta yanıt ver
    echo json_encode([
        'success' => 1,
        'file' => [
            'url' => BASE_URL . 'uploads/blog/' . $fileNewName, // Public erişimli URL
        ]
    ]);
    exit();
} else {
    echo json_encode(['success' => 0, 'message' => 'Dosya yüklenirken beklenmeyen bir hata oluştu.']);
    exit();
}