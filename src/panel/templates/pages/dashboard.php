<?php
// ROOT_PATH ve $pdo nesnesi public_html/panel/index.php üzerinden dahil edilen src/panel/config.php'den gelir.
if (!isset($pdo)) { // $pdo nesnesinin tanımlı olup olmadığını kontrol et
    require_once ROOT_PATH . '/src/panel/config.php';
}

// Gerçek Verileri Çekme
$total_blog_posts = 0;
$total_categories = 0; // Kategori tablosu olunca çekilecek
$total_page_visits = 0; // Ziyaretçi tablosu olunca çekilecek

try {
    // Toplam Blog Yazısı Sayısı
    $stmt_blog_posts = $pdo->query("SELECT COUNT(id) AS total_posts FROM blog_posts");
    $blog_data = $stmt_blog_posts->fetch(PDO::FETCH_ASSOC);
    $total_blog_posts = $blog_data['total_posts'];

    // Toplam Kategori Sayısı (categories tablosundan çekelim)
    $stmt_categories = $pdo->query("SELECT COUNT(id) AS total_categories FROM categories");
    $category_data = $stmt_categories->fetch(PDO::FETCH_ASSOC);
    $total_categories = $category_data['total_categories'];

    // Toplam Sayfa Ziyareti Sayısı (Şimdilik statik, ileride ziyaretçi tablosundan çekilecek)
    $total_page_visits = 122; // Placeholder

} catch (PDOException $e) {
    error_log("Dashboard veri çekme hatası: " . $e->getMessage());
    // Hata durumunda statik değerler veya 0 kalacak
}

// Grafik Verileri (Şimdilik statik)
$chart_labels = json_encode(['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz']);
$chart_data = json_encode([30, 45, 20, 60, 40, 55, 25]); // Örnek veri
?>

<div class="container-fluid mt-4">
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6 col-sm-12">
            <div class="stat-card">
                <div class="icon-wrapper bg-blue-light">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="text-content">
                    <p class="card-label">Toplam Blog Yazısı</p>
                    <h4 class="card-value"><?php echo $total_blog_posts; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 col-sm-12">
            <div class="stat-card">
                <div class="icon-wrapper bg-green-light">
                    <i class="bi bi-boxes"></i>
                </div>
                <div class="text-content">
                    <p class="card-label">Toplam Kategori</p>
                    <h4 class="card-value"><?php echo $total_categories; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 col-sm-12">
            <div class="stat-card">
                <div class="icon-wrapper bg-orange-light">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="text-content">
                    <p class="card-label">Toplam Ziyaret</p>
                    <h4 class="card-value"><?php echo $total_page_visits; ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Ziyaretçi İstatistikleri</h5>
                    <i class="bi bi-three-dots"></i>
                </div>
                <p class="card-subtitle mb-4">Web sitesi ziyaretçi istatistiklerini gösterir.</p>
                <div class="chart-area" style="height: 300px;">
                    <canvas id="visitorChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Ajanda</h5>
                    <i class="bi bi-three-dots"></i>
                </div>
                <p class="card-subtitle mb-4">Kısa Vadeli Ajanda Oluştur</p>

                <form>
                    <div class="mb-3">
                        <label for="namaAgenda" class="form-label">Ajanda Adı</label>
                        <input type="text" class="form-control" id="namaAgenda" placeholder="Ajanda Adı">
                    </div>
                    <div class="mb-3">
                        <label for="waktuAgenda" class="form-label">Zamanı</label>
                        <input type="date" class="form-control" id="waktuAgenda">
                    </div>
                    <div class="mb-4">
                        <label for="deskripsiAgenda" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="deskripsiAgenda" rows="3"
                            placeholder="Ajanda Açıklaması"></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('visitorChart').getContext('2d');
        const visitorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $chart_labels; ?>, // PHP'den gelen etiketler
                datasets: [{
                    label: 'Ziyaretçi Sayısı',
                    data: <?php echo $chart_data; ?>, // PHP'den gelen veriler
                    backgroundColor: 'rgba(90, 125, 255, 0.2)', // Açık mavi
                    borderColor: '#5a7dff', // Koyu mavi
                    borderWidth: 2,
                    tension: 0.4, // Yumuşak eğri
                    fill: true, // Alanı doldur
                    pointBackgroundColor: '#5a7dff', // Nokta rengi
                    pointBorderColor: '#fff', // Nokta kenar rengi
                    pointBorderWidth: 1,
                    pointRadius: 4, // Nokta boyutu
                    pointHoverRadius: 6, // Hover'da nokta boyutu
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Yüksekliği kontrol etmek için
                plugins: {
                    legend: {
                        display: false // Efsaneyi gizle
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false // X ekseni gridlerini gizle
                        },
                        ticks: {
                            color: '#666' // Etiket rengi
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#eee' // Y ekseni grid rengi
                        },
                        ticks: {
                            color: '#666' // Etiket rengi
                        }
                    }
                }
            }
        });
    });
</script>