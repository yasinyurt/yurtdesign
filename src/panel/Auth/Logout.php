<?php

// Bu dosya doğrudan erişime açık DEĞİLDİR, sadece public_html/panel/index.php üzerinden çağrılır.
// session_start() zaten public_html/panel/index.php'de başlatılıyor.

// Oturum değişkenlerini temizle
$_SESSION = array();

// Oturum çerezini (cookie) yok et (eğer kullanılıyorsa)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Oturumu tamamen sonlandır
session_destroy();

// Çıkış mesajı ayarla
$_SESSION['logout_message'] = 'Başarıyla çıkış yaptınız.';

// Kullanıcıyı giriş sayfasına yönlendir
header('Location: ' . BASE_URL . 'panel/login');
exit();

?>