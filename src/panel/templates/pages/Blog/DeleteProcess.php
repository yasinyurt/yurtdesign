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

// GET isteği ile mi gelindiğini kontrol edelim (Silme genellikle GET ile gelir)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $post_id = null;
    if (isset($_GET['id'])) {
        $post_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    }

    if (!is_numeric($post_id) || $post_id <= 0) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Geçersiz blog yazısı ID\'si veya ID belirtilmedi.']);
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
        // Önce yazının içeriğini (JSON) ve thumbnail URL'sini alalım ki görselleri kaldırabilelim
        $stmt_get_post_data = $pdo->prepare("SELECT content, image, is_featured FROM blog_posts WHERE id = :id LIMIT 1");
        $stmt_get_post_data->bindParam(':id', $post_id, PDO::PARAM_INT);
        $stmt_get_post_data->execute();
        $post_data = $stmt_get_post_data->fetch(PDO::FETCH_ASSOC);

        if (!$post_data) {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Silinecek blog yazısı bulunamadı.']);
            exit();
        }

        // --- Görsel Silme İşlemleri ---
        // Thumbnail görselini silme
        if (!empty($post_data['image'])) {
            $thumbnail_path_on_server = str_replace(BASE_URL, $base_upload_path, $post_data['image']);
            if (file_exists($thumbnail_path_on_server) && is_file($thumbnail_path_on_server)) {
                if (!unlink($thumbnail_path_on_server)) {
                    error_log("DELETE_PROCESS: Thumbnail silinemedi: " . $thumbnail_path_on_server);
                }
            }
        }

        // Editor.js içindeki görselleri silme
        $editorjs_content_json = json_decode($post_data['content'], true);
        if (json_last_error() === JSON_ERROR_NONE && isset($editorjs_content_json['blocks'])) {
            foreach ($editorjs_content_json['blocks'] as $block) {
                if ($block['type'] === 'image' && isset($block['data']['file']['url'])) {
                    $image_url = $block['data']['file']['url'];
                    $image_path_on_server = str_replace(BASE_URL, $base_upload_path, $image_url);
                    if (file_exists($image_path_on_server) && is_file($image_path_on_server)) {
                        if (!unlink($image_path_on_server)) {
                            error_log("DELETE_PROCESS: Editor.js resmi silinemedi: " . $image_path_on_server);
                        }
                    }
                }
            }
        }

        // --- Blog yazısını veritabanından silme ---
        $sql = "DELETE FROM blog_posts WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Sadece 1 tane öne çıkarılan blog yazısı olması mantığını burada uygulayalım
            // Eğer silinen yazı öne çıkarılan yazıyorsa ve başka öne çıkarılan kalmayacaksa
            if ($post_data['is_featured'] == 1) {
                $stmt_check_any_featured = $pdo->query("SELECT COUNT(id) FROM blog_posts WHERE is_featured = 1");
                $remaining_featured_count = $stmt_check_any_featured->fetchColumn();

                if ($remaining_featured_count == 0) { // Eğer öne çıkarılan hiç yazı kalmadıysa
                    // En son eklenen yazıyı bul ve öne çıkar
                    $stmt_set_latest_featured = $pdo->prepare("UPDATE blog_posts SET is_featured = 1 WHERE id = (SELECT id FROM (SELECT id FROM blog_posts ORDER BY created_at DESC LIMIT 1) AS temp_table)");
                    $stmt_set_latest_featured->execute();
                }
            }

            ob_end_clean();
            echo json_encode(['success' => 1, 'message' => 'Blog yazısı ve ilişkili görseller başarıyla silindi.']);
            exit();
        } else {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Belirtilen ID\'ye sahip blog yazısı bulunamadı veya silme işlemi başarısız.']);
            exit();
        }

    } catch (PDOException $e) {
        error_log("DELETE_PROCESS: PDO İstisnası: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Blog yazısı silinemedi. Lütfen daha sonra tekrar deneyin.']);
        exit();
    } catch (Exception $e) {
        error_log("DELETE_PROCESS: Genel İstisna: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Görseller silinirken bir sorun oluştu.']);
        exit();
    }

} else {
    ob_end_clean();
    echo json_encode(['success' => 0, 'message' => 'Geçersiz istek. Blog yazısı silmek için GET ve ID gereklidir.']);
    exit();
}