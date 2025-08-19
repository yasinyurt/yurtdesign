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
require_once ROOT_PATH . '/src/panel/config.php';

// POST isteği ile mi gelindiğini kontrol edelim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Güncellenecek kategorinin ID'si
    $category_id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT); // add.php'den gelen id

    if (!is_numeric($category_id) || $category_id <= 0) {
        ob_end_clean(); // Tamponu temizlemeden önce çıkış yap
        echo json_encode(['success' => 0, 'message' => 'Geçersiz kategori ID\'si. (Formdan gelmedi veya boş).']); // Mesajı netleştirdik
        exit();
    }

    // Yükleme dizini (FİZİKSEL YOL) - Kategori görselleri buraya gidecek
    $base_upload_dir = ROOT_PATH . '/yurtdesign/';
    $uploadDir = $base_upload_dir . 'uploads/categories/thumbnails/';

    // Gelen verileri filtreleyelim ve temizleyelim
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? '';
    $parent_id = $_POST['parent_id'] ?? null;
    $description = $_POST['description'] ?? '';
    $existing_image_url = $_POST['existing_image_url'] ?? null;
    $thumbnail_removed_flag = ($_POST['thumbnail_removed'] ?? 'false') === 'true';

    // Boş bırakılamayacak alanların kontrolü
    if (empty($name) || empty($type)) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Lütfen kategori adı ve türü gibi zorunlu alanları doldurun.']);
        exit();
    }

    // Kategori türünün geçerliliğini kontrol et
    $allowed_types = ['blog', 'project', 'service'];
    if (!in_array($type, $allowed_types)) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Geçersiz kategori türü belirtildi.']);
        exit();
    }

    // Slug oluşturma
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

    // parent_id'nin geçerliliğini ve numeric olup olmadığını kontrol et
    if (!empty($parent_id) && (!is_numeric($parent_id) || $parent_id <= 0)) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Geçersiz üst kategori ID\'si.']);
        exit();
    }
    if (empty($parent_id)) {
        $parent_id = NULL;
    }

    // Veritabanı bağlantısı ($pdo objesi)
    if (!isset($pdo)) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Veritabanı bağlantısı yok.']);
        exit();
    }

    // Eski kategori verilerini çekelim (özellikle eski görsel URL'si için)
    $old_category_data = null;
    try {
        $stmt_old_data = $pdo->prepare("SELECT image FROM categories WHERE id = :id LIMIT 1");
        $stmt_old_data->bindParam(':id', $category_id, PDO::PARAM_INT);
        $stmt_old_data->execute();
        $old_category_data = $stmt_old_data->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("UpdateProcess: Eski kategori verileri çekilirken hata: " . $e->getMessage());
        // Hatayı logla ama işlemi devam ettir.
    }

    $final_image_url_to_db = $old_category_data['image'] ?? NULL; // Varsayılan olarak veritabanındaki mevcut görseli kullan

    // --- Kategori Görsel Yükleme ve İşleme ---
    // Sadece yeni bir dosya yüklendiyse işlemi yap
    if (isset($_FILES['category_image_file']) && $_FILES['category_image_file']['error'] === UPLOAD_ERR_OK && $_FILES['category_image_file']['size'] > 0) {
        $file = $_FILES['category_image_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($fileExt, $allowed) || $fileSize > 5 * 1024 * 1024 || !is_dir($uploadDir) || !is_writable($uploadDir)) {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Kategori görseli yükleme hatası: Geçersiz dosya veya dizin.']);
            exit();
        }

        $fileNewName = uniqid('', true) . '.' . $fileExt;
        $fileDestination = $uploadDir . $fileNewName;

        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $final_image_url_to_db = BASE_URL . 'uploads/categories/thumbnails/' . $fileNewName;

            // Eski görseli sil (YALNIZCA yeni bir dosya başarıyla yüklendiyse)
            if (!empty($old_category_data['image'])) {
                $old_image_path = str_replace(BASE_URL, $base_upload_dir, $old_category_data['image']);
                if (file_exists($old_image_path) && is_file($old_image_path)) {
                    if (!unlink($old_image_path)) {
                        error_log("UpdateProcess: Eski kategori görseli silinemedi: " . $old_image_path);
                    }
                }
            }
        } else {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Yeni kategori görseli kaydedilirken hata oluştu.']);
            exit();
        }
    }
    // Eğer görsel kaldırıldıysa (removeCategoryImage butonu ile)
    elseif ($thumbnail_removed_flag) {
        $final_image_url_to_db = NULL; // Veritabanında görsel URL'sini NULL yap

        // Eski görseli sil
        if (!empty($old_category_data['image'])) {
            $old_image_path = str_replace(BASE_URL, $base_upload_dir, $old_category_data['image']);
            if (file_exists($old_image_path) && is_file($old_image_path)) {
                if (!unlink($old_image_path)) {
                    error_log("UpdateProcess: Kaldırılan kategori görseli silinemedi: " . $old_image_path);
                }
            }
        }
    }
    // Eğer yeni dosya yüklenmediyse ve kaldırılmadıysa, mevcut URL'yi koru.


    try {
        // Slug ve tür kombinasyonunun benzersizliğini kontrol et (kendisi hariç)
        $stmt_check_slug = $pdo->prepare("SELECT id FROM categories WHERE slug = :slug AND type = :type AND id != :id LIMIT 1");
        $stmt_check_slug->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt_check_slug->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt_check_slug->bindParam(':id', $category_id, PDO::PARAM_INT);
        $stmt_check_slug->execute();
        if ($stmt_check_slug->fetch()) {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Bu türde aynı ada sahip başka bir kategori zaten mevcut. Lütfen farklı bir isim deneyin.']);
            exit();
        }

        $sql = "UPDATE categories SET name = :name, slug = :slug, type = :type, parent_id = :parent_id, description = :description, image = :image WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':parent_id', $parent_id, (is_null($parent_id) ? PDO::PARAM_NULL : PDO::PARAM_INT));
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':image', $final_image_url_to_db, PDO::PARAM_STR);
        $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);

        $stmt->execute();

        ob_end_clean();
        echo json_encode(['success' => 1, 'message' => 'Kategori başarıyla güncellendi.']);
        exit();

    } catch (PDOException $e) {
        error_log("Kategori güncellenirken veritabanı hatası: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Kategori güncellenemedi. Lütfen daha sonra tekrar deneyin.', 'error_detail' => $e->getMessage()]);
        exit();
    }

} else {
    ob_end_clean();
    header('Location: ' . BASE_URL . 'panel/category');
    exit();
}