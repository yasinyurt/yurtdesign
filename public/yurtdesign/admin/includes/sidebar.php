<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            
            <?php if (hasPermission('manage_blog')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'blog') !== false ? 'active' : ''; ?>" href="blog.php">
                    <i class="bi bi-journal-text"></i>
                    Blog Yönetimi
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('manage_services')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'services') !== false ? 'active' : ''; ?>" href="services.php">
                    <i class="bi bi-gear"></i>
                    Hizmetler
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('manage_projects')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'projects') !== false ? 'active' : ''; ?>" href="projects.php">
                    <i class="bi bi-folder"></i>
                    Projeler
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('manage_contact')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'contact') !== false ? 'active' : ''; ?>" href="contact.php">
                    <i class="bi bi-envelope"></i>
                    İletişim
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'analytics') !== false ? 'active' : ''; ?>" href="analytics.php">
                    <i class="bi bi-graph-up"></i>
                    Analitik
                </a>
            </li>
            
            <?php if (hasPermission('manage_users')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>" href="users.php">
                    <i class="bi bi-people"></i>
                    Kullanıcılar
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('manage_settings')): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false ? 'active' : ''; ?>" href="settings.php">
                    <i class="bi bi-sliders"></i>
                    Ayarlar
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white-50">
            <span>Sistem</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="../" target="_blank">
                    <i class="bi bi-eye"></i>
                    Siteyi Görüntüle
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">
                    <i class="bi bi-person"></i>
                    Profil
                </a>
            </li>
        </ul>
    </div>
</nav>