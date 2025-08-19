<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli - <?php echo ucfirst($request_uri); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <style>
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background-color:#f0f2f5;margin:0}
        .wrapper{display:flex;min-height:100vh;width:100%}
        .sidebar{width:250px;min-width:250px;background-color:#fff;box-shadow:2px 0 5px rgba(0,0,0,0.05);position:fixed;height:100vh;display:flex;flex-direction:column;overflow:hidden;z-index:1030;transition:width 0.25s cubic-bezier(0.4,0,0.2,1),min-width 0.25s cubic-bezier(0.4,0,0.2,1)}
        .sidebar.collapsed{width:80px;min-width:80px}
        .sidebar-header{padding:20px 15px;text-align:center;border-bottom:1px solid #eee;height:100px;display:flex;align-items:center;justify-content:center;overflow:hidden}
        .logo-container{position:relative;width:100%;height:60px;display:flex;align-items:center;justify-content:center}
        .full-logo{max-width:180px;height:auto;opacity:1;transition:opacity 0.2s ease 0.05s;display:block}
        .min-logo{position:absolute;width:40px;height:auto;opacity:0;transition:opacity 0.2s ease 0.05s;display:block}
        .sidebar.collapsed .full-logo{opacity:0;transition:opacity 0.15s ease}
        .sidebar.collapsed .min-logo{opacity:1;transition:opacity 0.2s ease 0.1s}
        .sidebar-menu{flex-grow:1;padding:15px 0;overflow-y:auto}
        .sidebar-menu ul{list-style:none;padding:0;margin:0}
        .sidebar-menu ul li{position:relative;margin-bottom:5px}
        .sidebar-menu ul li a{display:flex;align-items:center;padding:12px 15px;color:#555;text-decoration:none;font-size:1rem;border-radius:8px;margin:0 10px;position:relative;transition:all 0.2s ease;overflow:hidden}
        .sidebar.collapsed .sidebar-menu ul li a{justify-content:center;padding:12px 5px;margin:0 5px}
        .sidebar-menu ul li a .menu-icon{font-size:1.2rem;margin-right:15px;width:20px;text-align:center;color:#888;flex-shrink:0;transition:margin-right 0.25s ease}
        .sidebar.collapsed .sidebar-menu ul li a .menu-icon{margin-right:0}
        .sidebar-menu ul li a .menu-text{white-space:nowrap;opacity:1;transition:opacity 0.2s ease;overflow:hidden}
        .sidebar.collapsed .sidebar-menu ul li a .menu-text{opacity:0;width:0}
        .sidebar-menu ul li a.active{background-color:#e0e7ff;color:#339999;font-weight:600}
        .sidebar-menu ul li a:hover{background-color:#f0f2f5;color:#339999}
        .sidebar-menu ul li a::after{content:'';position:absolute;right:0;top:50%;transform:translateY(-50%) scaleX(0);width:5px;height:80%;background-color:#339999;border-radius:5px 0 0 5px;transition:transform 0.15s ease}
        .sidebar-menu ul li a:hover::after,.sidebar-menu ul li a.active::after{transform:translateY(-50%) scaleX(1)}
        .content-wrapper{margin-left:250px;flex-grow:1;display:flex;flex-direction:column;width:calc(100% - 250px);transition:margin-left 0.25s cubic-bezier(0.4,0,0.2,1),width 0.25s cubic-bezier(0.4,0,0.2,1)}
        .content-wrapper.full-width{margin-left:80px;width:calc(100% - 80px)}
        .navbar-top{background-color:#fff;padding:15px 25px;box-shadow:0 2px 4px rgba(0,0,0,0.05);display:flex;justify-content:space-between;align-items:center;z-index:1020}
        .navbar-top .welcome-text{font-size:1.1rem;color:#333;font-weight:500}
        .profile-dropdown{position:relative;cursor:pointer}
        .profile-dropdown .profile-icon-wrapper{width:40px;height:40px;border-radius:50%;background-color:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#339999;transition:background-color 0.2s ease}
        .profile-dropdown .profile-icon-wrapper:hover{background-color:#d0d7ff}
        .profile-dropdown .dropdown-menu{position:absolute;right:0;top:100%;background-color:#fff;border:1px solid #eee;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.1);min-width:150px;padding:10px 0;z-index:1000;display:none}
        .profile-dropdown .dropdown-menu.show{display:block}
        .profile-dropdown .dropdown-menu a{display:block;padding:10px 15px;color:#555;text-decoration:none;transition:background-color 0.2s ease}
        .profile-dropdown .dropdown-menu a:hover{background-color:#f0f2f5}
        .sidebar-toggle{background:none;border:none;font-size:1.5rem;color:#555;cursor:pointer;padding:0 15px}
        .sidebar-toggle:hover{color:#333}
        .main-content-area{padding:25px;flex-grow:1;overflow-y:auto}
        .alert{margin-bottom:20px;margin-top:10px}
        .stat-card{background-color:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);padding:20px;display:flex;align-items:center;gap:15px;transition:transform 0.2s ease;height:100%}
        .stat-card:hover{transform:translateY(-5px)}
        .stat-card .icon-wrapper{width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0}
        .bg-blue-light{background-color:#e0e7ff;color:#5a7dff}
        .bg-orange-light{background-color:#ffe0b2;color:#ff9800}
        .bg-green-light{background-color:#e8f5e9;color:#4caf50}
        .bg-purple-light{background-color:#ede7f6;color:#8e24aa}
        .bg-pink-light{background-color:#fce4ec;color:#e91e63}
        .bg-red-light{background-color:#ffebee;color:#f44336}
        .stat-card .text-content .card-label{font-size:0.9rem;color:#888;margin-bottom:5px}
        .stat-card .text-content .card-value{font-size:1.8rem;font-weight:700;color:#333;margin:0}
        .card{background-color:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.05);border:none}
        .bi-three-dots{font-size:1.5rem;color:#ccc;cursor:pointer}
        .card-subtitle{font-size:0.9rem;color:#999}
        .btn-primary{background-color:#5a7dff!important;border-color:#5a7dff!important}
        .btn-primary:hover{background-color:#4a6ce0!important;border-color:#4a6ce0!important}
        .action-button{font-family:inherit;display:inline-flex;align-items:center;justify-content:center;width:5em;height:2em;line-height:2em;position:relative;cursor:pointer;overflow:hidden;border:2px solid var(--color);transition:color 0.3s;z-index:1;font-size:14px;border-radius:6px;font-weight:500;color:var(--color);background:transparent;text-decoration:none;box-shadow:none;padding:0}
        .action-button:before{content:"";position:absolute;z-index:-1;background:var(--color);height:150px;width:200px;border-radius:50%;top:100%;left:100%;transition:all 0.3s}
        .action-button:hover{color:#fff}
        .action-button:hover:before{top:-30px;left:-30px}
        .action-button:active:before{background:var(--color-active);transition:background 0s}
        .edit-action-button{--color:#339999;--color-active:#2a7f7f}
        .delete-action-button{--color:#e62222;--color-active:#c41b1b}
        table .featured-row{border-left:4px solid #339999;background-color:#f8f8f8}
        table .featured-row:hover{background-color:#f0f0f0}
        .bi-star-fill{color:#ffc107}
        .bi-star{color:#ccc}

        /* Tooltip sadece collapsed durumda göster */
        .tooltip-overlay{position:fixed;left:85px;background:#339999;color:#fff;padding:8px 12px;border-radius:6px;font-size:0.9rem;z-index:1060;opacity:0;visibility:hidden;transition:opacity 0.2s ease;pointer-events:none;white-space:nowrap;box-shadow:0 4px 8px rgba(0,0,0,0.2)}
        .sidebar.collapsed .tooltip-overlay.show{opacity:1;visibility:visible}
    </style>
</head>
<body>
    <div class="tooltip-overlay" id="menuTooltip"></div>
    <div class="wrapper">
        <aside class="sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="<?php echo BASE_URL; ?>assets/images/full-size-logo.png" alt="YurtDesign Logo" class="full-logo">
                    <img src="<?php echo BASE_URL; ?>assets/images/min-size-logo.png" alt="YurtDesign Min Logo" class="min-logo">
                </div>
            </div>
            <nav class="sidebar-menu">
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>panel/dashboard" class="nav-link-item <?php echo ($request_uri == 'dashboard' ? 'active' : ''); ?>" data-tooltip="Gösterge Paneli">
                            <i class="bi bi-grid-1x2-fill menu-icon"></i>
                            <span class="menu-text">Gösterge Paneli</span>
                        </a></li>
                    <li><a href="<?php echo BASE_URL; ?>panel/blog" class="nav-link-item <?php echo (strpos($request_uri, 'blog') === 0 ? 'active' : ''); ?>" data-tooltip="Blog">
                            <i class="bi bi-pencil-square menu-icon"></i>
                            <span class="menu-text">Blog</span>
                        </a></li>
                    <li><a href="<?php echo BASE_URL; ?>panel/category" class="nav-link-item <?php echo (strpos($request_uri, 'category') === 0 ? 'active' : ''); ?>" data-tooltip="Kategoriler">
                            <i class="bi bi-tags-fill menu-icon"></i>
                            <span class="menu-text">Kategoriler</span>
                        </a></li>
                    <li><a href="<?php echo BASE_URL; ?>panel/settings" class="nav-link-item <?php echo ($request_uri == 'settings' ? 'active' : ''); ?>" data-tooltip="Ayarlar">
                            <i class="bi bi-gear-fill menu-icon"></i>
                            <span class="menu-text">Ayarlar</span>
                        </a></li>
                </ul>
            </nav>
        </aside>
        <div class="content-wrapper" id="contentWrapper">
            <nav class="navbar-top">
                <div class="d-flex align-items-center">
                    <button type="button" id="sidebarToggle" class="sidebar-toggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="welcome-text ms-3">Hoş Geldiniz, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Yönetici'); ?>!</span>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-icon-wrapper">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="dropdown-menu" id="profileDropdownMenu">
                        <a href="<?php echo BASE_URL; ?>panel/settings">Ayarlar</a>
                        <a href="<?php echo BASE_URL; ?>panel/logout">Çıkış Yap</a>
                    </div>
                </div>
            </nav>
            <main class="main-content-area flex-grow-1">