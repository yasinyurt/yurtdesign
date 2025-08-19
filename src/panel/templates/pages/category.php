<?php
// ROOT_PATH ve $pdo nesnesi public_html/panel/index.php üzerinden dahil edilen src/panel/config.php'den gelir.
if (!isset($pdo)) {
    require_once ROOT_PATH . '/src/panel/config.php';
}

$categories = [];
try {
    // Kategorileri çekelim, parent kategorinin adını da alalım
    $stmt = $pdo->query("
        SELECT
            c.id,
            c.name,
            c.slug,
            c.type,
            c.image,
            c.description,
            pc.name AS parent_category_name,
            c.created_at
        FROM
            categories c
        LEFT JOIN
            categories pc ON c.parent_id = pc.id
        ORDER BY
            c.type ASC, c.name ASC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Kategori listelenirken veritabanı hatası: " . $e->getMessage());
    $_SESSION['message'] = 'Sistem hatası: Kategoriler yüklenirken bir hata oluştu.';
    $_SESSION['message_type'] = 'danger';
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kategori Yönetimi</h2>
        <a href="<?php echo BASE_URL; ?>panel/category/add" class="btn btn-primary">Yeni Kategori Ekle</a>
    </div>

    <?php
    // Mesajlar (başarı veya hata) burada gösterilecek
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message_type']) . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }

    if (count($categories) > 0) {
        ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Görsel</th>
                                <th>Ad</th>
                                <th>Tür</th>
                                <th>Üst Kategori</th>
                                <th>Açıklama</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr style="vertical-align: middle;">
                                    <td>
                                        <?php if (!empty($category['image'])): ?>
                                            <img src="<?php echo htmlspecialchars($category['image']); ?>"
                                                alt="<?php echo htmlspecialchars($category['name']); ?>"
                                                style="width: 50px; height: 50px; border-radius: 5px; object-fit: cover; overflow: hidden;">
                                        <?php else: ?>
                                            <i class="bi bi-image-fill text-muted" style="font-size: 2rem;"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><span
                                            class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($category['type'])); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($category['parent_category_name'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($category['description'] ?? '', 0, 50, '...')); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="<?php echo BASE_URL; ?>panel/category/edit/<?php echo $category['id']; ?>"
                                                class="action-button edit-action-button">Düzenle</a>
                                            <button class="action-button delete-action-button"
                                                onclick="deleteCategory(<?php echo $category['id']; ?>);">Sil</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-info" role="alert">Henüz hiç kategori bulunmamaktadır. İlk kategorinizi ekleyin!</div>';
    }
    ?>

</div>

<script>
    function deleteCategory(categoryId) {
        if (confirm('Bu kategoriyi kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
            fetch('<?php echo BASE_URL; ?>panel/category/delete/' + categoryId, {
                method: 'GET'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload(); // Sayfayı yenilemek için
                    } else {
                        alert('Hata: ' + data.message);
                        console.error('Silme hatası:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Silme işlemi sırasında ağ hatası oluştu:', error);
                    alert('Silme işlemi sırasında bir hata oluştu. Lütfen konsolu kontrol edin.');
                });
        }
    }
</script>