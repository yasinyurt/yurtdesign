<?php
session_start();
require_once '../../src/config.php';
require_once '../../src/includes/database.php';
require_once '../../src/includes/auth.php';
require_once '../../src/includes/analytics.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$analytics = new Analytics();
$days = (int)($_GET['days'] ?? 30);
$stats = $analytics->getDashboardStats($days);
$linkStats = $analytics->getLinkClickStats($days);

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-graph-up me-2"></i>Analitik Raporlar
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="?days=7" class="btn btn-sm <?php echo $days == 7 ? 'btn-primary' : 'btn-outline-secondary'; ?>">7 Gün</a>
                        <a href="?days=30" class="btn btn-sm <?php echo $days == 30 ? 'btn-primary' : 'btn-outline-secondary'; ?>">30 Gün</a>
                        <a href="?days=90" class="btn btn-sm <?php echo $days == 90 ? 'btn-primary' : 'btn-outline-secondary'; ?>">90 Gün</a>
                    </div>
                </div>
            </div>

            <!-- Detaylı İstatistikler -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Toplam Ziyaretçi</div>
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
                                        Sayfa/Ziyaret</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $stats['unique_visitors'] > 0 ? round($stats['page_views'] / $stats['unique_visitors'], 2) : 0; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-graph-up text-warning" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Günlük Trend Grafiği -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-graph-up me-2"></i>Günlük Ziyaretçi Trendi (Son <?php echo $days; ?> Gün)
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="visitorsChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Ülke Dağılımı -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-globe me-2"></i>Ülke Dağılımı
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($stats['visitors_by_country'] as $index => $country): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="fw-bold"><?php echo htmlspecialchars($country['country']); ?></span>
                                    <div class="progress" style="height: 5px; width: 150px;">
                                        <div class="progress-bar" style="width: <?php echo ($country['visitors'] / $stats['visitors_by_country'][0]['visitors']) * 100; ?>%"></div>
                                    </div>
                                </div>
                                <span class="badge bg-primary"><?php echo $country['visitors']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Popüler Sayfalar -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="bi bi-file-text me-2"></i>En Popüler Sayfalar
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Sayfa</th>
                                            <th>Görüntülenme</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats['popular_pages'] as $page): ?>
                                        <tr>
                                            <td>
                                                <small><?php echo htmlspecialchars($page['page_url']); ?></small>
                                            </td>
                                            <td><span class="badge bg-success"><?php echo $page['views']; ?></span></td>
                                            <td>
                                                <small><?php echo round(($page['views'] / $stats['page_views']) * 100, 1); ?>%</small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Link Tıklama İstatistikleri -->
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
                                        <?php foreach ($linkStats as $link): ?>
                                        <tr>
                                            <td>
                                                <small><?php echo htmlspecialchars($link['link_url']); ?></small>
                                            </td>
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
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>