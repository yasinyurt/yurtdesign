<?php

ob_start(); // Çıktı tamponlamayı başlat

// Bu dosya doğrudan erişime açık DEĞİLDİR, sadece public_html/panel/index.php üzerinden çağrılır.
header('Content-Type: application/json'); // Bu dosyadan gelen her şey JSON olarak işaretlensin

// Hata raporlamayı kapat (JSON yanıtı veren dosyalar için çok önemlidir)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Kullanıcının giriş yapıp yapmadığını kontrol edelim
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ob_end_clean(); // Tamponu temizlemeden önce çıkış yap
    echo json_encode(['success' => 0, 'message' => 'Bu işlemi yapmak için giriş yapmalısınız.']);
    exit();
}

// ROOT_PATH tanımı public_html/panel/index.php'den geliyor.
// Admin panel config dosyasını dahil et (veritabanı bağlantısı için)
require_once ROOT_PATH . '/src/panel/config.php';

// POST isteği ile mi gelindiğini kontrol edelim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Yükleme dizini (FİZİKSEL YOL) - Thumbnails buraya gidecek
    $uploadDir = ROOT_PATH . '/yurtdesign/uploads/blog/thumbnails/'; // Thumbnails klasörüne kaydolacak

    // Gelen verileri filtreleyelim ve temizleyelim
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? ''; // Editor.js JSON içeriği
    $category = $_POST['category'] ?? ''; // Blog Category
    $status = $_POST['status'] ?? '';

    // Öne çıkarılmış mı? checkbox'tan gelen değer
    $is_featured = (isset($_POST['is_featured']) && $_POST['is_featured'] == '1') ? 1 : 0;

    // Boş bırakılamayacak alanların kontrolü
    if (empty($title) || empty($content) || empty($category) || empty($status)) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Lütfen başlık, içerik ve kategori gibi zorunlu alanları doldurun.']);
        exit();
    }

    // Slug oluşturma (Başlık için)
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

    // Yazar ID'si (Giriş yapan admin kullanıcısı)
    $author_id = $_SESSION['admin_user_id'];

    // Veritabanı bağlantısı ($pdo objesi)
    if (!isset($pdo)) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Veritabanı bağlantısı yok.']);
        exit();
    }

    $thumbnail_image_url_to_save = NULL; // Veritabanına kaydedilecek thumbnail URL'si

    // --- Thumbnail Görsel Yükleme İşlemi (WEBP dönüşümü olmadan) ---
    if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK && $_FILES['thumbnail_file']['size'] > 0) {
        $file = $_FILES['thumbnail_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($fileExt, $allowed) || $fileSize > 5 * 1024 * 1024 || !is_dir($uploadDir) || !is_writable($uploadDir)) {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Thumbnail yükleme hatası: Geçersiz dosya veya dizin.']);
            exit();
        }

        $fileNewName = uniqid('', true) . '.' . $fileExt;
        $fileDestination = $uploadDir . $fileNewName;

        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $thumbnail_image_url_to_save = BASE_URL . 'uploads/blog/thumbnails/' . $fileNewName;
        } else {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Thumbnail kaydedilirken beklenmeyen bir hata oluştu (move_uploaded_file).']);
            exit();
        }
    }


    try {
        // Slug'ın benzersizliğini kontrol edelim (aynı slug'da başka yazı var mı?)
        $stmt_check_slug = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = :slug LIMIT 1");
        $stmt_check_slug->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt_check_slug->execute();
        if ($stmt_check_slug->fetch()) {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Aynı başlığa sahip bir blog yazısı zaten mevcut. Lütfen farklı bir başlık deneyin.']);
            exit();
        }

        // SADECE BİR TANE ÖNE ÇIKARILMIŞ BLOG YAZISI OLMASI MANTIĞI (ADD.PHP İÇİN)
        // confirmed_featured_override buraya gelmez, çünkü bu add.php
        // add.php'de bu onay dialog'u varsa (JS'te var) o zaman onu da göndermeliydik.
        // Ama siz add.php'de confirmed_featured_override'ı göndermemişsiniz.

        // Eğer is_featured seçildiyse, diğerlerini sıfırla
        if ($is_featured == 1) {
            $stmt_reset_featured = $pdo->prepare("UPDATE blog_posts SET is_featured = 0 WHERE is_featured = 1");
            $stmt_reset_featured->execute();
        }
        // "Mevcut durumda bir öne çıkarılan yoksa daima son eklenen satır öne çıkarılan olacak."
        // Bu mantık, SaveProcess'te bu yeni yazı eklenmeden yapılamaz.
        // Bu, ya post ekledikten sonra çalışan bir ayrı kontrol olur ya da UpdateProcess'e bırakılır.
        // Şimdilik sadece, eğer bu yeni yazı öne çıkarılacaksa diğerlerini sıfırlıyoruz.

        // Blog yazısını veritabanına ekleme sorgusu
        // Sütunların ve placeholder'ların blog_posts tablosuna tam uyduğundan emin olun
        $sql = "INSERT INTO blog_posts (title, slug, content, image, category, author_id, status, is_featured) VALUES (:title, :slug, :content, :image, :category, :author_id, :status, :is_featured)"; // is_featured EKLENDİ
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':image', $thumbnail_image_url_to_save, PDO::PARAM_STR); // Thumbnail URL'sini bağla (boşsa NULL olur)
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':author_id', $author_id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT); // is_featured EKLENDİ

        $stmt->execute();

        ob_end_clean();
        echo json_encode(['success' => 1, 'message' => 'Blog yazısı başarıyla kaydedildi.']);
        exit();

    } catch (PDOException $e) {
        error_log("Blog yazısı kaydedilirken veritabanı hatası: " . $e->getMessage()); // Bu logu kontrol edin
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Blog yazısı kaydedilemedi. Lütfen daha sonra tekrar deneyin.', 'error_detail' => $e->getMessage()]); // Debug için ekledim
        exit();
    }

} else {
    // POST isteği değilse, formu gösteren sayfaya geri yönlendir
    ob_end_clean();
    header('Location: ' . BASE_URL . 'panel/blog/add');
    exit();
}