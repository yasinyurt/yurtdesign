<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli Girişi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            width: 100%;
            max-width: 400px;
            border-radius: 0.75rem;
        }
        .card-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="card shadow-lg">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Yönetim Paneli Girişi</h3>

            <?php
            // Oturum başlatılmamışsa burada başlat (public_html/panel/index.php zaten başlatıyor olmalı)
            // if (session_status() == PHP_SESSION_NONE) { session_start(); }

            // Giriş hatası varsa burada gösterelim
            if (isset($_SESSION['login_error'])) {
                echo '<div class="alert alert-danger text-center" role="alert">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                unset($_SESSION['login_error']); // Hata mesajını gösterdikten sonra temizle
            }
            // Çıkış mesajı varsa
            if (isset($_SESSION['logout_message'])) {
                echo '<div class="alert alert-info text-center" role="alert">' . htmlspecialchars($_SESSION['logout_message']) . '</div>';
                unset($_SESSION['logout_message']);
            }
            ?>

            <form action="<?php echo BASE_URL; ?>panel/login_process" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Kullanıcı Adı</label>
                    <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Şifre</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Giriş Yap</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>