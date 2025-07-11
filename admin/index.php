<?php
session_start();
require_once '../src/config.php';
require_once '../src/includes/database.php';
require_once '../src/includes/auth.php';

// Giriş kontrolü
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$db = Database::getInstance();

// Dashboard istatistikleri
$stats = [
    'total_visitors' => $db->query("SELECT COUNT(DISTINCT ip_address) as count FROM analytics WHERE DATE(created_at) = CURDATE()")->fetch()['count'] ?? 0,
    'page_views' => $db->query("SELECT COUNT(*) as count FROM analytics WHERE DATE(created_at) = CURDATE()")->fetch()['count'] ?? 0,
    'blog_posts' => $db->query("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'")->fetch()['count'] ?? 0,
    'projects' => $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'active'")->fetch()['count'] ?? 0
];

// Son ziyaretçiler
$recent_visitors = $db->query("
    SELECT country, city, COUNT(*) as visits 
    FROM analytics 
    WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY country, city 
    ORDER BY visits DESC 
    LIMIT 10
")->fetchAll();

// Popüler sayfalar
$popular_pages = $db->query("
    SELECT page_url, COUNT(*) as views 
    FROM analytics 
    WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY page_url 
    ORDER BY views DESC 
    LIMIT 10
")->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Bugün</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Bu Hafta</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Bu Ay</button>
                    </div>
                </div>
            </div>

            <!-- İstatistik Kartları -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Bugünkü Ziyaretçiler</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_visitors']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Sayfa Görüntülemeleri</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['page_views']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-eye fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Blog Yazıları</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['blog_posts']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-blog fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Projeler</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['projects']; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-project-diagram fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafikler ve Tablolar -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Son 7 Gün Ziyaretçi Ülkeleri</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Ülke</th>
                                            <th>Şehir</th>
                                            <th>Ziyaret</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_visitors as $visitor): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($visitor['country'] ?? 'Bilinmiyor'); ?></td>
                                            <td><?php echo htmlspecialchars($visitor['city'] ?? 'Bilinmiyor'); ?></td>
                                            <td><?php echo $visitor['visits']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Popüler Sayfalar</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Sayfa</th>
                                            <th>Görüntülenme</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($popular_pages as $page): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($page['page_url']); ?></td>
                                            <td><?php echo $page['views']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>