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
    ob_end_clean();
    echo json_encode(['success' => 0, 'message' => 'Bu işlemi yapmak için giriş yapmalısınız.']);
    exit();
}

// ROOT_PATH tanımı public_html/panel/index.php'den geliyor.
require_once ROOT_PATH . '/src/panel/config.php';

// GET isteği ile mi gelindiğini kontrol edelim
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Silinecek kategorinin ID'si
    $category_id = null;
    if (isset($_GET['id'])) {
        $category_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    }

    if (!is_numeric($category_id) || $category_id <= 0) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Geçersiz kategori ID\'si veya ID belirtilmedi.']);
        exit();
    }

    if (!isset($pdo)) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Veritabanı bağlantısı yok.']);
        exit();
    }

    // Fiziksel dosya yolları için temel dizin (public_html eşdeğeri, sizin durumunuzda 'yurtdesign')
    $base_upload_path = ROOT_PATH . '/yurtdesign/';

    try {
        // Önce kategorinin görsel URL'sini alalım ki silmeden önce resmi kaldırabilelim
        $stmt_get_image = $pdo->prepare("SELECT image FROM categories WHERE id = :id LIMIT 1");
        $stmt_get_image->bindParam(':id', $category_id, PDO::PARAM_INT);
        $stmt_get_image->execute();
        $category_image_data = $stmt_get_image->fetch(PDO::FETCH_ASSOC);

        // Kategoriyi veritabanından sil
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Kategori başarıyla silindiyse, ilişkili görseli de sunucudan sil
            if ($category_image_data && !empty($category_image_data['image'])) {
                $image_path_on_server = str_replace(BASE_URL, $base_upload_path, $category_image_data['image']);
                if (file_exists($image_path_on_server) && is_file($image_path_on_server)) {
                    if (!unlink($image_path_on_server)) {
                        error_log("Kategori görseli silinemedi: " . $image_path_on_server);
                    }
                }
            }
            ob_end_clean();
            echo json_encode(['success' => 1, 'message' => 'Kategori başarıyla silindi.']);
            exit();
        } else {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Belirtilen ID\'ye sahip kategori bulunamadı veya silme işlemi başarısız.']);
            exit();
        }

    } catch (PDOException $e) {
        error_log("Kategori silinirken veritabanı hatası: " . $e->getMessage());
        ob_end_clean();
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => 0, 'message' => 'Bu kategoriye bağlı öğeler (örneğin blog yazıları) olduğu için silinemez. Önce ilişkili öğeleri silmelisiniz.']);
        } else {
            echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Kategori silinemedi. Lütfen daha sonra tekrar deneyin.']);
        }
        exit();
    } catch (Exception $e) {
        error_log("Kategori silinirken genel hata: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Görsel silinirken bir sorun oluştu.']);
        exit();
    }

} else {
    ob_end_clean();
    echo json_encode(['success' => 0, 'message' => 'Geçersiz istek. Kategori silmek için GET ve ID gereklidir.']);
    exit();
}