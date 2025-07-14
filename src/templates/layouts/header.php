<!-- ÜST BAR -->
    <div class="bg-dark text-light py-3 topbar">
        <div class="container-fluid d-flex justify-content-between align-items-center small general-pad">
            <div>
                <i class="bi bi-geo-alt"></i> Çorlu, Tekirdağ
                <i class="bi bi-envelope ms-3"></i> info@yurtdesign.com
            </div>
            <div>
                <!--<a href="#" class="text-light me-2"><i class="bi bi-facebook"></i></a>-->
                <a href="https://www.instagram.com/yurtwebdesign?igsh=MXM4MHYybG80d3J2Yw%3D%3D&utm_source=qr " class="text-light" target="_blank"><i class="bi bi-instagram"></i></a>
            </div>
        </div>
    </div>


    <!-- STICKY -->
    <nav id="mainNavbar" class="navbar navbar-expand-lg banner-bg py-4">
        <div class="container-fluid flex-column flex-lg-row align-items-center general-pad">
            <img src="assets/images/full-size-logo.png" width="220" class="mb-3 mb-lg-0">

            <button class="navbar-toggler d-lg-none border-0 order-3 order-sm-3 order-md-2 order-lg-1 mt-2 mt-sm-0"
                type="button" data-bs-toggle="offcanvas" data-bs-target="#mobilMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse d-none d-lg-flex justify-content-end align-items-center">
                <ul class="navbar-nav gap-lg-3 letter-spaced mr-2">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>">Ana Sayfa</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL . 'about-us'; ?>">Hakkımızda</a></li>
                    <li class="nav-item dropdown position-relative">
                        <a class="nav-link dropdown-toggle-no-icon pe-none" id="nelerDropdown">
                            Neler Sunuyoruz?
                        </a>
                        <div class="dropdown-menu custom-dropdown" aria-labelledby="nelerDropdown">
                            <a class="dropdown-item" href="<?php echo BASE_URL . 'web-design'; ?>">Web Tasarımı</a>
                            <a class="dropdown-item" href="<?php echo BASE_URL . 'web-software'; ?>">Web Yazılımı</a>
                            <a class="dropdown-item" href="<?php echo BASE_URL . 'erp'; ?>">ERP</a>
                            <a class="dropdown-item" href="<?php echo BASE_URL . 'crm'; ?>">CRM</a>
                            <a class="dropdown-item" href="<?php echo BASE_URL . 'social-media'; ?>">Sosyal Medya Yönetimi</a>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link pe-none" href="">Projelerimiz</a></li>
                    <li class="nav-item"><a class="nav-link pe-none" href="<?php echo BASE_URL . 'blog'; ?>">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL . 'contact'; ?>">İletişim</a></li>
                </ul>
                <a href="#" class="btn btn-primary px-4 ms-4">Teklif Al!</a>
            </div>
        </div>
    </nav>
    <div id="navbarPlaceholder" style="height: 0;"></div>

    <!-- OFFCANVAS (Mobil Menü) -->
    <div class="offcanvas offcanvas-end d-flex align-items-center" tabindex="-1" id="mobilMenu">
        <div class="offcanvas-header">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex text-center flex-column align-items-center">
            <img src="assets/images/min-size-logo.png" width="100">
            <ul class="navbar-nav mt-3">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>">Ana sayfa</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL . 'about-us'; ?>">Hakkımızda</a></li>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
                        href="#mobilHizmetler" role="button" aria-expanded="false" aria-controls="mobilHizmetler">
                        Neler Sunuyoruz?
                        <span class="ms-2">&#x25BC;</span>
                    </a>
                    <div class="collapse ps-3" id="mobilHizmetler">
                        <a class="nav-link" href="<?php echo BASE_URL . 'web-design'; ?>">Web Tasarımı</a>
                        <a class="nav-link" href="<?php echo BASE_URL . 'web-software'; ?>">Web Yazılımı</a>
                        <a class="nav-link" href="<?php echo BASE_URL . 'erp'; ?>">ERP</a>
                        <a class="nav-link" href="<?php echo BASE_URL . 'crm'; ?>">CRM</a>
                        <a class="nav-link" href="<?php echo BASE_URL . 'social-media'; ?>">Sosyal Medya Yönetimi</a>
                    </div>
                </li>
                <li class="nav-item"><a class="nav-link" href="#">Projelerimiz</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL . 'blog'; ?>">Blog</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL . 'contact'; ?>">İletişim</a></li>
            </ul>
            <a href="#" class="btn btn-primary px-3 mt-3">Teklif Al!</a>
        </div>
    </div>