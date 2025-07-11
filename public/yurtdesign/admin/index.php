<?php
session_start();
require_once '../../../src/config.php';
require_once '../../../src/includes/database.php';
require_once '../../../src/includes/auth.php';
require_once '../../../src/includes/analytics.php';

// Giriş kontrolü
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

requirePermission('view_dashboard');

$user = getCurrentUser();
$analytics = new Analytics();
$stats = $analytics->getDashboardStats(30);

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary active">30 Gün</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">7 Gün</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Bugün</button>
                    </div>
                </div>
            </div>

            <!-- İstatistik Kartları -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Benzersiz Ziyaretçi</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['unique_visitors']); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Sayfa Görüntüleme</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['page_views']); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-eye text-success" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Ort. Sayfa Süresi</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['avg_time_on_page']; ?>s</div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-clock text-info" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Bounce Rate</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $stats['page_views'] > 0 ? round(($stats['unique_visitors'] / $stats['page_views']) * 100, 1) : 0; ?>%
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-graph-down text-warning" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafikler ve Tablolar -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-graph-up me-2"></i>Günlük Ziyaretçi Trendi
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="visitorsChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-globe me-2"></i>Ülke Bazında Ziyaretçiler
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($stats['visitors_by_country'] as $country): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><?php echo htmlspecialchars($country['country']); ?></span>
                                <span class="badge bg-primary"><?php echo $country['visitors']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-file-text me-2"></i>Popüler Sayfalar
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Sayfa</th>
                                            <th>Görüntülenme</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats['popular_pages'] as $page): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($page['page_url']); ?></td>
                                            <td><span class="badge bg-success"><?php echo $page['views']; ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-link-45deg me-2"></i>En Çok Tıklanan Linkler
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Link</th>
                                            <th>Tıklanma</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $linkStats = $analytics->getLinkClickStats(30);
                                        foreach ($linkStats as $link): 
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($link['link_url']); ?></td>
                                            <td><span class="badge bg-info"><?php echo $link['clicks']; ?></span></td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Ziyaretçi trendi grafiği
const ctx = document.getElementById('visitorsChart').getContext('2d');
const visitorsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [<?php echo "'" . implode("','", array_column($stats['daily_visitors'], 'date')) . "'"; ?>],
        datasets: [{
            label: 'Günlük Ziyaretçiler',
            data: [<?php echo implode(',', array_column($stats['daily_visitors'], 'visitors')); ?>],
            borderColor: '#339999',
            backgroundColor: 'rgba(51, 153, 153, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>