<?php
// HTTP durum kodunu 404 olarak ayarladığımızdan emin olalım.
// Bu satır index.php'de zaten var ama burada da bulunması zararsızdır.
http_response_code(404);
?>

<div class="container text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <img src="<?php echo BASE_URL; ?>/assets/images/404-icon.svg" alt="Sayfa Bulunamadı" class="img-fluid mb-4" style="max-width: 300px;">

            <h1 class="display-4 fw-bold">Oops! Sayfa Bulunamadı.</h1>
            
            <p class="lead text-muted mt-3">
                Aradığınız sayfa kaldırılmış, adı değiştirilmiş veya hiç var olmamış olabilir.
            </p>

            <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg mt-3">
                <i class="bi bi-house-door-fill"></i> Ana Sayfaya Dön
            </a>
            
        </div>
    </div>
</div>