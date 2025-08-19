<?php

// Bu dosya doğrudan erişime açık DEĞİLDİR, sadece public_html/panel/index.php üzerinden çağrılır.
header('Content-Type: application/json'); // Bu dosyadan gelen her şey JSON olarak işaretlensin

// Hata raporlamayı kapat (JSON yanıtı veren dosyalar için çok önemlidir)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Kullanıcının giriş yapıp yapmadığını kontrol edelim
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => 0, 'message' => 'Bu işlemi yapmak için giriş yapmalısınız.']);
    exit();
}

// ROOT_PATH tanımı public_html/panel/index.php'den geliyor.
// Admin panel config dosyasını dahil et (veritabanı bağlantısı için)
require_once ROOT_PATH . '/src/panel/config.php';

// POST isteği ile mi gelindiğini kontrol edelim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Yükleme dizini (FİZİKSEL YOL) - Kategori görselleri buraya gidecek
    // Sizde public_html klasörü yerine proje kökünde 'yurtdesign' adlı klasörün olduğunu belirtmiştiniz.
    $uploadDir = ROOT_PATH . '/yurtdesign/uploads/categories/thumbnails/';

    // Gelen verileri filtreleyelim ve temizleyelim
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $parent_id = $_POST['parent_id'] ?? null;
    $description = $_POST['description'] ?? '';

    // Boş bırakılamayacak alanların kontrolü
    if (empty($name) || empty($type)) {
        echo json_encode(['success' => 0, 'message' => 'Lütfen kategori adı ve türü gibi zorunlu alanları doldurun.']);
        exit();
    }

    // Kategori türünün geçerliliğini kontrol et
    $allowed_types = ['blog', 'project', 'service'];
    if (!in_array($type, $allowed_types)) {
        echo json_encode(['success' => 0, 'message' => 'Geçersiz kategori türü belirtildi.']);
        exit();
    }

    // Slug oluşturma
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

    // parent_id'nin geçerliliğini ve numeric olup olmadığını kontrol et
    if (!empty($parent_id) && (!is_numeric($parent_id) || $parent_id <= 0)) {
        echo json_encode(['success' => 0, 'message' => 'Geçersiz üst kategori ID\'si.']);
        exit();
    }
    if (empty($parent_id)) {
        $parent_id = NULL;
    }

    // Veritabanı bağlantısı ($pdo objesi)
    if (!isset($pdo)) {
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Veritabanı bağlantısı yok.']);
        exit();
    }

    $image_url_to_save = NULL; // Veritabanına kaydedilecek görsel URL'si

    // --- Kategori Görsel Yükleme İşlemi (WEBP dönüşümü olmadan) ---
    // 'category_image_file' inputundan gelen dosyayı işle
    if (isset($_FILES['category_image_file']) && $_FILES['category_image_file']['error'] === UPLOAD_ERR_OK && $_FILES['category_image_file']['size'] > 0) {
        $file = $_FILES['category_image_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];

        // Yükleme dizini yoksa veya yazılabilir değilse hata ver
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            echo json_encode(['success' => 0, 'message' => 'Kategori görseli yükleme dizini mevcut değil veya yazılabilir değil: ' . $uploadDir]);
            exit();
        }

        // Güvenli dosya adı oluşturma ve uzantı kontrolü
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // İzin verilen uzantılar

        if (!in_array($fileExt, $allowed)) {
            echo json_encode(['success' => 0, 'message' => 'Geçersiz kategori görseli türü. Sadece JPG, JPEG, PNG, GIF, WEBP dosyalarına izin verilir.']);
            exit();
        }

        // Dosya boyutunu kontrol et (örn. 5MB)
        if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            echo json_encode(['success' => 0, 'message' => 'Kategori görseli boyutu çok büyük. Maksimum 5MB.']);
            exit();
        }

        // Benzersiz dosya adı oluştur (orijinal uzantısını koru)
        $fileNewName = uniqid('', true) . '.' . $fileExt;
        $fileDestination = $uploadDir . $fileNewName;

        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $image_url_to_save = BASE_URL . 'uploads/categories/thumbnails/' . $fileNewName; // URL'yi kaydet
        } else {
            echo json_encode(['success' => 0, 'message' => 'Kategori görseli kaydedilirken beklenmeyen bir hata oluştu (move_uploaded_file).']);
            exit();
        }
    } else {
        // Eğer dosya yüklenmediyse, $_POST['image']'den gelen URL'yi kullan (add.php'de bu boş olur)
        // Bu kısım, düzenleme formundan gelen mevcut URL'yi işlemek için daha önemli olacak
        // add.php'de bu input hep boş/geçici değer taşır, bu durumda $image_url_to_save NULL olarak kalır.
        $image_url_to_save = $_POST['image'] ?? NULL;
    }
    // --- Kategori Görsel Yükleme İşlemi Sonu ---


    try {
        // Slug ve tür kombinasyonunun benzersizliğini kontrol et
        $stmt_check_slug = $pdo->prepare("SELECT id FROM categories WHERE slug = :slug AND type = :type LIMIT 1");
        $stmt_check_slug->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt_check_slug->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt_check_slug->execute();
        if ($stmt_check_slug->fetch()) {
            echo json_encode(['success' => 0, 'message' => 'Bu türde aynı ada sahip bir kategori zaten mevcut. Lütfen farklı bir isim deneyin.']);
            exit();
        }

        // Kategoriyi veritabanına ekleme sorgusu
        $sql = "INSERT INTO categories (name, slug, type, parent_id, description, image) VALUES (:name, :slug, :type, :parent_id, :description, :image)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':parent_id', $parent_id, (is_null($parent_id) ? PDO::PARAM_NULL : PDO::PARAM_INT));
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image_url_to_save, PDO::PARAM_STR); // Görsel URL'sini bağla

        $stmt->execute();

        echo json_encode(['success' => 1, 'message' => 'Kategori başarıyla kaydedildi.']);
        exit();

    } catch (PDOException $e) {
        error_log("Kategori kaydedilirken veritabanı hatası: " . $e->getMessage());
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Kategori kaydedilemedi. Lütfen daha sonra tekrar deneyin.', 'error_detail' => $e->getMessage()]); // Debug için
        exit();
    }

} else {
    // POST isteği değilse, formu gösteren sayfaya geri yönlendir
    header('Location: ' . BASE_URL . 'panel/category/add');
    exit();
}