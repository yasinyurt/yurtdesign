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

    // Güncellenecek yazının ID'si
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);

    if (!is_numeric($post_id) || $post_id <= 0) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Geçersiz blog yazısı ID\'si. (Formdan gelmedi veya boş).']);
        exit();
    }

    // Yükleme dizini (FİZİKSEL YOL)
    $base_upload_dir = ROOT_PATH . '/yurtdesign/';
    $thumbnail_upload_dir = $base_upload_dir . 'uploads/blog/thumbnails/';
    $editorjs_upload_dir = $base_upload_dir . 'uploads/blog/';

    // Gelen verileri filtreleyelim ve temizleyelim
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? '';
    $status = $_POST['status'] ?? '';
    $existing_thumbnail_url_from_form = $_POST['image'] ?? null;
    $thumbnail_removed_flag = ($_POST['thumbnail_removed'] ?? 'false') === 'true';

    // Öne çıkarılmış mı? checkbox'tan gelen değer
    $is_featured = (isset($_POST['is_featured']) && $_POST['is_featured'] == '1') ? 1 : 0; // BU KISIM AYNI KALACAK

    // JavaScript'ten gelen onay bayrağı
    $confirmed_featured_override = (isset($_POST['confirmed_featured_override']) && $_POST['confirmed_featured_override'] === 'true'); // YENİ EKLENDİ

    // Zorunlu alanların boş olup olmadığını kontrol edelim
    if (empty($title) || empty($content) || empty($category) || empty($status)) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Lütfen başlık, içerik ve kategori gibi zorunlu alanları doldurun.']);
        exit();
    }

    // Slug oluşturma
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));

    // Veritabanı bağlantısı ($pdo objesi)
    if (!isset($pdo)) {
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Veritabanı bağlantısı yok.']);
        exit();
    }

    // Mevcut blog yazısının eski verilerini çekelim
    $old_post_data = null;
    try {
        $stmt_old_data = $pdo->prepare("SELECT content, image, is_featured FROM blog_posts WHERE id = :id LIMIT 1");
        $stmt_old_data->bindParam(':id', $post_id, PDO::PARAM_INT);
        $stmt_old_data->execute();
        $old_post_data = $stmt_old_data->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("UpdateProcess: Eski post verileri çekilirken hata: " . $e->getMessage());
    }

    $final_image_url_to_db = $old_post_data['image'] ?? NULL;

    // --- Thumbnail Görsel Yükleme ve İşleme ---
    if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK && $_FILES['thumbnail_file']['size'] > 0) {
        $file = $_FILES['thumbnail_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($fileExt, $allowed) || $fileSize > 5 * 1024 * 1024 || !is_dir($thumbnail_upload_dir) || !is_writable($thumbnail_upload_dir)) {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Thumbnail yükleme hatası: Geçersiz dosya veya dizin.']);
            exit();
        }

        $fileNewName = uniqid('', true) . '.' . $fileExt;
        $fileDestination = $thumbnail_upload_dir . $fileNewName;

        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $final_image_url_to_db = BASE_URL . 'uploads/blog/thumbnails/' . $fileNewName;
            if (!empty($old_post_data['image'])) {
                $old_thumbnail_path = str_replace(BASE_URL, $base_upload_dir, $old_post_data['image']);
                if (file_exists($old_thumbnail_path) && is_file($old_thumbnail_path)) {
                    if (!unlink($old_thumbnail_path)) { error_log("UpdateProcess: Eski thumbnail silinemedi: " . $old_thumbnail_path); }
                }
            }
        } else {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Yeni thumbnail kaydedilirken beklenmeyen bir hata oluştu.']);
            exit();
        }
    } elseif ($thumbnail_removed_flag) {
        $final_image_url_to_db = NULL;
        if (!empty($old_post_data['image'])) {
            $old_thumbnail_path = str_replace(BASE_URL, $base_upload_dir, $old_post_data['image']);
            if (file_exists($old_thumbnail_path) && is_file($old_thumbnail_path)) {
                if (!unlink($old_thumbnail_path)) { error_log("UpdateProcess: Kaldırılan thumbnail silinemedi: " . $old_thumbnail_path); }
            }
        }
    }


    // --- Editor.js İçindeki Değişen Görselleri Silme ---
    $old_editorjs_content = json_decode($old_post_data['content'] ?? '{"blocks":[]}', true);
    $new_editorjs_content = json_decode($content, true);

    $old_image_urls = [];
    if (json_last_error() === JSON_ERROR_NONE && isset($old_editorjs_content['blocks'])) {
        foreach ($old_editorjs_content['blocks'] as $block) {
            if ($block['type'] === 'image' && isset($block['data']['file']['url'])) { $old_image_urls[] = $block['data']['file']['url']; }
        }
    }

    $new_image_urls = [];
    if (json_last_error() === JSON_ERROR_NONE && isset($new_editorjs_content['blocks'])) {
        foreach ($new_editorjs_content['blocks'] as $block) {
            if ($block['type'] === 'image' && isset($block['data']['file']['url'])) { $new_image_urls[] = $block['data']['file']['url']; }
        }
    }

    $images_to_delete = array_diff($old_image_urls, $new_image_urls);

    foreach ($images_to_delete as $image_url_to_delete) {
        $image_path_on_server = str_replace(BASE_URL, $base_upload_dir, $image_url_to_delete);
        if (strpos($image_path_on_server, $base_upload_dir . 'uploads/blog/') === 0 && file_exists($image_path_on_server) && is_file($image_path_on_server)) {
            if (!unlink($image_path_on_server)) { error_log("UpdateProcess: Editor.js eski resmi silinemedi: " . $image_path_on_server); }
        }
    }

    // --- Blog Yazısını Veritabanında Güncelleme ---
    try {
        // SADECE BİR TANE ÖNE ÇIKARILMIŞ BLOG YAZISI OLMASI MANTIĞI
        // 1. Durum: Bu yazı öne çıkarıldıysa (veya öyle bırakıldıysa)
        if ($is_featured == 1) {
            // Mevcut tüm öne çıkarılmış yazıları (bu yazı hariç) öne çıkarılmamış yap
            $stmt_reset_featured = $pdo->prepare("UPDATE blog_posts SET is_featured = 0 WHERE is_featured = 1 AND id != :current_post_id");
            $stmt_reset_featured->bindParam(':current_post_id', $post_id, PDO::PARAM_INT);
            $stmt_reset_featured->execute();
        }
        // 2. Durum: Bu yazı öne çıkarılmaktan kaldırıldıysa VEYA zaten öne çıkarılmamışsa ama tümden featured yoksa
        elseif ($is_featured == 0 && ($old_post_data['is_featured'] == 1 || !$confirmed_featured_override) ) { // YENİ MANTIK BURADA
            // Eğer bu yazı öne çıkarılmaktan kaldırıldıysa VEYA zaten öne çıkarılmamışken kaydedildiyse
            // ve şu anda hiç öne çıkarılmış başka yazı yoksa (ya da bu kaldırıldıktan sonra kalmayacaksa)
            // O zaman en son eklenen (ID'si en büyük olan) yazıyı öne çıkar.
            // Bu kontrolü, bu yazı kaldırıldıktan sonra hiç öne çıkarılmış kalıp kalmadığını kontrol ederek yapalım.
            $stmt_check_any_featured = $pdo->prepare("SELECT COUNT(id) FROM blog_posts WHERE is_featured = 1 AND id != :current_post_id");
            $stmt_check_any_featured->bindParam(':current_post_id', $post_id, PDO::PARAM_INT);
            $stmt_check_any_featured->execute();
            $remaining_featured_count = $stmt_check_any_featured->fetchColumn();

            if ($remaining_featured_count == 0) { // Eğer bu yazı kaldırıldıktan sonra hiç öne çıkarılan kalmayacaksa
                // En son eklenen blog yazısını bul ve öne çıkar
                $stmt_set_latest_featured = $pdo->prepare("UPDATE blog_posts SET is_featured = 1 WHERE id = (SELECT id FROM (SELECT id FROM blog_posts ORDER BY created_at DESC LIMIT 1) AS temp_table)");
                $stmt_set_latest_featured->execute();
            }
        }


        // Slug'ın benzersizliğini kontrol et (kendisi hariç)
        $stmt_check_slug = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = :slug AND id != :id LIMIT 1");
        $stmt_check_slug->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt_check_slug->bindParam(':id', $post_id, PDO::PARAM_INT);
        $stmt_check_slug->execute();
        if ($stmt_check_slug->fetch()) {
            ob_end_clean();
            echo json_encode(['success' => 0, 'message' => 'Aynı başlığa sahip başka bir blog yazısı zaten mevcut. Lütfen farklı bir başlık deneyin.']);
            exit();
        }

        $sql = "UPDATE blog_posts SET title = :title, slug = :slug, content = :content, image = :image, category = :category, status = :status, is_featured = :is_featured WHERE id = :id"; // is_featured EKLENDİ
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':image', $final_image_url_to_db, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':is_featured', $is_featured, PDO::PARAM_INT); // is_featured EKLENDİ
        $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);

        $stmt->execute();

        ob_end_clean();
        echo json_encode(['success' => 1, 'message' => 'Blog yazısı başarıyla güncellendi.']);
        exit();

    } catch (PDOException $e) {
        error_log("Blog yazısı güncellenirken veritabanı hatası: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['success' => 0, 'message' => 'Sistem hatası: Blog yazısı güncellenemedi. Lütfen daha sonra tekrar deneyin.']);
        exit();
    }

} else {
    ob_end_clean();
    header('Location: ' . BASE_URL . 'panel/blog');
    exit();
}