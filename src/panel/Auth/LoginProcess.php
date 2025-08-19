<?php

// Bu dosya doğrudan erişime açık DEĞİLDİR, sadece public_html/panel/index.php üzerinden çağrılır.
// Bu yüzden burada session_start() tekrar etmeye gerek yok, index.php zaten başlatıyor.

// POST isteği ile mi gelindiğini kontrol edelim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF Koruması (ileride eklenecek, şimdilik basit tutuyoruz)
    // Token kontrolü yapılacak

    // Gelen verileri filtreleyelim ve temizleyelim
    // FILTER_SANITIZE_STRING kullanımdan kaldırıldı, FILTER_UNSAFE_RAW daha uygun
    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

    // Kullanıcı adı veya şifre boş mu kontrol edelim
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Kullanıcı adı ve şifre boş bırakılamaz.';
        header('Location: ' . BASE_URL . 'panel/login');
        exit();
    }

    // Veritabanı bağlantısı config/panel.php'den gelir ($pdo objesi)
    // Eğer $pdo objesi burada yoksa, config.php'nin doğru dahil edildiğinden emin olun.
    if (!isset($pdo)) {
        // Bu durum normalde oluşmamalı, ama bir güvenlik önlemi.
        $_SESSION['login_error'] = 'Sistem hatası: Veritabanı bağlantısı yok.';
        header('Location: ' . BASE_URL . 'panel/login');
        exit();
    }

    try {
        // Kullanıcıyı veritabanından sorgulayalım
        // SQL Injection'ı önlemek için hazır ifade (prepared statement) kullanıyoruz.
        $stmt = $pdo->prepare("SELECT id, username, password FROM admin_users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kullanıcı bulundu mu ve şifre doğru mu?
        if ($user && password_verify($password, $user['password'])) {
            // Şifre doğru, oturum başlat
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['last_activity'] = time(); // Oturum zaman aşımı için

            // Daha önce yönlendirilmesi istenen bir sayfa var mıydı?
            $redirect_to = isset($_SESSION['redirect_to']) ? $_SESSION['redirect_to'] : 'dashboard';
            unset($_SESSION['redirect_to']); // Yönlendirme hedefini temizle

            header('Location: ' . BASE_URL . 'panel/' . $redirect_to);
            exit();
        } else {
            // Kullanıcı adı veya şifre hatalı
            $_SESSION['login_error'] = 'Kullanıcı adı veya şifre hatalı.';
            header('Location: ' . BASE_URL . 'panel/login');
            exit();
        }

    } catch (PDOException $e) {
        // Veritabanı hatası
        error_log("Giriş işlemi sırasında veritabanı hatası: " . $e->getMessage()); // Hatayı logla
        $_SESSION['login_error'] = 'Sistem hatası: Lütfen daha sonra tekrar deneyin.';
        header('Location: ' . BASE_URL . 'panel/login');
        exit();
    }

} else {
    // Form POST edilmediyse, login sayfasına yönlendir
    header('Location: ' . BASE_URL . 'panel/login');
    exit();
}

?>