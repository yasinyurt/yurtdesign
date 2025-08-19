<?php

// ÇOK KRİTİK: session_start() her zaman ob_start()'tan ÖNCE GELMELİDİR
session_start(); // Tüm admin paneli sayfaları için oturumu başlatıyoruz (EN BAŞTA OLMALI)

ob_start(); // Çıktı tamponlamayı başlat (session_start()tan HEMEN SONRA)

// Hata raporlamayı aç (geliştirme aşamasında iyidir)
ini_set('display_errors', 1); // Geliştirme için hataları göstermeyi aç
ini_set('display_startup_errors', 1); // Başlangıç hatalarını göstermeyi aç
error_reporting(E_ALL); // Tüm hataları raporla

// Ana sitenin config dosyasını dahil edelim. BASE_URL ve ROOT_PATH için gerekli.
// PANEL İÇİNDE GEÇİCİ KÖK YOL TANIMI (Ana ROOT_PATH yüklenmeden önce lazım)
// public_html/panel/index.php'den proje kök dizinine (src'nin bulunduğu yer) ulaşmak için 2 seviye yukarı çıkmalıyız.
define('PANEL_BASE_ROOT_PATH', dirname(dirname(__DIR__)));
require_once PANEL_BASE_ROOT_PATH . '/src/config.php'; // Ana config'i dahil et

// Admin paneline özel yapılandırmayı dahil edelim (src/panel/config.php)
require_once ROOT_PATH . '/src/panel/config.php';

// 1. URL'yi alalım.
$request_uri = isset($_GET['url']) ? $_GET['url'] : 'dashboard';
$request_uri = filter_var(rtrim($request_uri, '/'), FILTER_SANITIZE_URL);

// Hangi isteklerin JSON yanıtı döndürmesi gerektiğini belirleyelim
$json_requests = ['login_process', 'logout', 'image/upload', 'upload_thumbnail', 'link_data', 'blog/save', 'blog/check_featured', 'category/save', 'upload_category_image'];
// 'blog/update/{id}', 'blog/delete/{id}', 'category/update/{id}' ve 'category/delete/{id}' de JSON isteği olacak
if (strpos($request_uri, 'blog/update/') === 0 || strpos($request_uri, 'blog/delete/') === 0 || strpos($request_uri, 'category/update/') === 0 || strpos($request_uri, 'category/delete/') === 0) {
    $json_requests[] = $request_uri;
}


// 2. Oturum Kontrolü: Kullanıcı giriş yapmamışsa ve istenen sayfa login veya login_process değilse, login sayfasına yönlendir.
$allowed_unauthenticated_pages = ['login', 'login_process'];
$is_authenticated = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;


if (!$is_authenticated) {
    // Kullanıcı giriş yapmamış
    if (!in_array($request_uri, $allowed_unauthenticated_pages)) {
        // İstenen sayfa giriş veya giriş işleme sayfası değilse, login sayfasına yönlendir
        $_SESSION['redirect_to'] = $request_uri; // Giriş yapıldıktan sonra yönlendirilecek sayfayı kaydet
        header('Location: ' . BASE_URL . 'panel/login');
        exit(); // header() sonrası exit() önemlidir
    }
} else {
    // Kullanıcı giriş yapmış
    if (in_array($request_uri, $allowed_unauthenticated_pages)) {
        // Giriş yapmış bir kullanıcı, login veya login_process sayfasına gitmeye çalışıyorsa dashboard'a yönlendir.
        header('Location: ' . BASE_URL . 'panel/dashboard');
        exit(); // header() sonrası exit() önemlidir
    }
}

// 3. Admin paneli sayfa dosyasının yolunu belirleyelim.
// Yönlendirme mantığı, en spesifik URL'den en genel olana doğru olmalıdır.
if (strpos($request_uri, 'blog/edit/') === 0 && count(explode('/', $request_uri)) == 3) {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/blog/edit.php';
    $_GET['id'] = explode('/', $request_uri)[2];
} elseif (strpos($request_uri, 'blog/update/') === 0 && count(explode('/', $request_uri)) == 3) {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/Blog/UpdateProcess.php';
} elseif (strpos($request_uri, 'blog/delete/') === 0 && count(explode('/', $request_uri)) == 3) {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/Blog/DeleteProcess.php';
    $_GET['id'] = explode('/', $request_uri)[2];
} elseif (strpos($request_uri, 'category/edit/') === 0 && count(explode('/', $request_uri)) == 3) {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/category/edit.php';
    $_GET['id'] = explode('/', $request_uri)[2];
} elseif (strpos($request_uri, 'category/update/') === 0 && count(explode('/', $request_uri)) == 3) {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/category/UpdateProcess.php';
} elseif (strpos($request_uri, 'category/delete/') === 0 && count(explode('/', $request_uri)) == 3) {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/category/DeleteProcess.php';
    $_GET['id'] = explode('/', $request_uri)[2];
} elseif ($request_uri === 'login_process') {
    $page_path = ROOT_PATH . '/src/panel/Auth/LoginProcess.php';
} elseif ($request_uri === 'logout') {
    $page_path = ROOT_PATH . '/src/panel/Auth/Logout.php';
} elseif ($request_uri === 'dashboard') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/dashboard.php';
} elseif ($request_uri === 'settings') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/settings.php';
} elseif ($request_uri === 'blog') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/blog.php';
} elseif ($request_uri === 'blog/add') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/blog/add.php';
} elseif ($request_uri === 'blog/save') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/Blog/SaveProcess.php';
} elseif ($request_uri === 'image/upload') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/Blog/ImageUpload.php';
} elseif ($request_uri === 'upload_thumbnail') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/Blog/UploadThumbnail.php';
} elseif ($request_uri === 'blog/check_featured') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/Blog/CheckFeatured.php';
} elseif ($request_uri === 'category') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/category.php';
} elseif ($request_uri === 'category/add') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/category/add.php';
} elseif ($request_uri === 'category/save') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/category/SaveProcess.php';
} elseif ($request_uri === 'upload_category_image') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/category/UploadCategoryImage.php';
} elseif ($request_uri === 'login') {
    $page_path = ROOT_PATH . '/src/panel/templates/pages/login.php';
} else {
    // Diğer normal sayfalar veya 404
    $page_path = ROOT_PATH . '/src/panel/templates/pages/' . $request_uri . '.php';
}


// 4. Admin paneli layout dosyalarını dahil edelim
if (!in_array($request_uri, $json_requests) && $request_uri !== 'login') {
    require_once ROOT_PATH . '/src/panel/templates/layouts/header.php';
}


// 5. İstenen sayfa gerçekten var mı diye kontrol edelim ve dahil edelim.
if (file_exists($page_path)) {
    require_once $page_path;
} else {
    if ($is_authenticated) {
        echo "<div style='text-align:center; padding: 50px;'><h1 style='color: red;'>Hata: Admin paneli sayfası bulunamadı: " . htmlspecialchars($request_uri) . "</h1><p>Aradığınız yönetici sayfası mevcut değil.</p><a href='" . BASE_URL . 'panel/dashboard' . "' class='btn btn-primary'>Dashboard'a Dön</a></div>";
    } else {
        header('Location: ' . BASE_URL . 'panel/login');
        exit();
    }
}

// 6. Admin paneli footer'ını dahil edelim
if (!in_array($request_uri, $json_requests) && $request_uri !== 'login') {
    require_once ROOT_PATH . '/src/panel/templates/layouts/footer.php';
}

$output = ob_get_clean();
if (in_array($request_uri, $json_requests)) {
    $output = preg_replace('/<!DOCTYPE html>.*?(<html.*?>.*?<\/html>)?|[\s\S]*?(<head.*?>.*?<\/head>)?[\s\S]*?(<body.*?>.*?<\/body>)?/is', '', $output);
    $output = trim($output);
    $output = preg_replace('/\s+/', ' ', $output);
    $output = str_replace(["\r", "\n", "\t"], '', $output);

    if (substr($output, 0, 1) !== '{' && substr($output, 0, 1) !== '[') {
        error_log("Beklenmeyen çıktı formatı. Yanıt: " . $output);
        $output_debug = substr($output, 0, 500);
        $output = json_encode(['success' => 0, 'message' => 'Sunucudan geçersiz yanıt alındı. Detaylar konsolda veya loglarda.', 'debug' => $output_debug]);
    }
}

echo $output;

?>