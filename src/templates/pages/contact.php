<!-- Breadcrumb Section -->
<section class="d-flex align-items-center justify-content-center" style="height: 350px; background-color: #339999;">
    <div class="text-center text-white">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-3" style="font-size: 1.1rem;">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>" class="text-white text-decoration-none">Ana Sayfa</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">İletişim</li>
            </ol>
        </nav>
        <h1 class="display-4 fw-bold title-font">İletişim</h1>
        <p class="lead text-font">Çorlu'da dijital çözümler için bize ulaşın</p>
    </div>
</section>

<!-- Contact Info Cards -->
<section class="py-5" style="margin-top: -100px; position: relative; z-index: 10;">
    <div class="container-fluid general-pad">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-lg text-center p-4 h-100" style="border-radius: 15px;">
                    <div class="mb-3">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: linear-gradient(135deg, #339999 0%, #287878 100%);">
                            <i class="bi bi-geo-alt text-white" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold title-font mb-3">Adresimiz</h4>
                    <p class="text-font mb-0">Çorlu, Tekirdağ<br>Türkiye</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-lg text-center p-4 h-100" style="border-radius: 15px;">
                    <div class="mb-3">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: linear-gradient(135deg, #339999 0%, #287878 100%);">
                            <i class="bi bi-envelope text-white" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold title-font mb-3">E-posta</h4>
                    <p class="text-font mb-0">info@yurtdesign.com<br>destek@yurtdesign.com</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-lg text-center p-4 h-100" style="border-radius: 15px;">
                    <div class="mb-3">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; background: linear-gradient(135deg, #339999 0%, #287878 100%);">
                            <i class="bi bi-clock text-white" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold title-font mb-3">Çalışma Saatleri</h4>
                    <p class="text-font mb-0">Pazartesi - Cuma<br>09:00 - 18:00</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="" style="background-color: #f8f9fa; padding: 100px 0 100px 0;">
    <div class="container-fluid general-pad">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h2 class="h1 fw-bold title-font mb-3">Çorlu Web Tasarım Hizmetleri İçin Bize Ulaşın</h2>
                    <p class="lead text-font">Web tabanlı çözümlerimiz hakkında bilgi almak ve ücretsiz danışmanlık için formu doldurun</p>
                </div>
                
                <div class="card border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="card-body p-5">
                        <form>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label text-font fw-semibold">Ad *</label>
                                    <input type="text" class="form-control form-control-lg" id="firstName" required style="border-radius: 10px;">
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label text-font fw-semibold">Soyad *</label>
                                    <input type="text" class="form-control form-control-lg" id="lastName" required style="border-radius: 10px;">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label text-font fw-semibold">E-posta *</label>
                                    <input type="email" class="form-control form-control-lg" id="email" required style="border-radius: 10px;">
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label text-font fw-semibold">Telefon</label>
                                    <input type="tel" class="form-control form-control-lg" id="phone" style="border-radius: 10px;">
                                </div>
                                <div class="col-12">
                                    <label for="service" class="form-label text-font fw-semibold">İlgilendiğiniz Hizmet *</label>
                                    <select class="form-select form-select-lg" id="service" required style="border-radius: 10px;">
                                        <option value="">Seçiniz...</option>
                                        <option value="web-tasarim">Çorlu Web Tasarım</option>
                                        <option value="web-yazilim">Çorlu Web Yazılım</option>
                                        <option value="erp">Çorlu ERP Çözümleri</option>
                                        <option value="crm">Çorlu CRM Sistemleri</option>
                                        <option value="sosyal-medya">Çorlu Sosyal Medya Yönetimi</option>
                                        <option value="diger">Diğer</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label text-font fw-semibold">Mesajınız *</label>
                                    <textarea class="form-control form-control-lg" id="message" rows="6" placeholder="Projeniz hakkında detayları paylaşın. Web tabanlı çözümlerimiz tamamen tarayıcı üzerinden çalışır, kurulum gerektirmez." required style="border-radius: 10px;"></textarea>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 py-3 text-font fw-semibold" style="border-radius: 50px;">
                                        <i class="bi bi-send me-2"></i>Mesajı Gönder
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, #339999 0%, #287878 100%);">
    <div class="container-fluid general-pad text-center text-white">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="h1 fw-bold title-font mb-4">Hemen Başlayalım!</h2>
                <p class="lead text-font mb-4">
                    Çorlu'da web tabanlı dijital çözümler için tek adresiniz. Ücretsiz danışmanlık ve proje analizi için hemen iletişime geçin.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="tel:+905XXXXXXXXX" class="btn btn-light btn-lg px-4 py-3 text-font fw-semibold">
                        <i class="bi bi-telephone me-2"></i>Hemen Arayın
                    </a>
                    <a href="mailto:info@yurtdesign.com" class="btn btn-outline-light btn-lg px-4 py-3 text-font fw-semibold">
                        <i class="bi bi-envelope me-2"></i>E-posta Gönderin
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="" style="padding: 100px 0 100px 0;">
    <div class="container-fluid general-pad">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="badge mb-3 px-3 py-2" style="background-color: #339999; font-size: 0.9rem;">Neden Biz?</span>
                <h2 class="h1 fw-bold title-font mb-4">
                    Çorlu'da <span style="color: #339999;">Web Tabanlı</span> Çözümlerin Lideri
                </h2>
                <p class="text-font mb-4" style="font-size: 1.1rem; line-height: 1.7;">
                    Tüm hizmetlerimiz %100 web tarayıcısı üzerinden çalışır. Hiçbir indirme veya kurulum gerektirmez. Müşterilerimiz hizmetleri doğrudan mevcut web sitelerine entegre edebilir ve tek hesap ile her şeye erişebilir.
                </p>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-check-circle-fill text-primary me-3 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <h5 class="fw-bold text-font mb-1">Kurulum Gerektirmez</h5>
                                <p class="text-font mb-0 text-muted">Tüm sistemler web tarayıcısı üzerinden çalışır</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-check-circle-fill text-primary me-3 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <h5 class="fw-bold text-font mb-1">Tek Hesap, Tüm Hizmetler</h5>
                                <p class="text-font mb-0 text-muted">Bir link ile tüm dijital çözümlere erişim</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-check-circle-fill text-primary me-3 mt-1" style="font-size: 1.2rem;"></i>
                            <div>
                                <h5 class="fw-bold text-font mb-1">Çorlu'da Yerel Destek</h5>
                                <p class="text-font mb-0 text-muted">Aynı şehirde, hızlı ve güvenilir hizmet</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="assets/images/contact-benefits.png" alt="Çorlu Web Hizmetleri Avantajları" class="img-fluid">
            </div>
        </div>
    </div>
</section>
