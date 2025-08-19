<?php
// ROOT_PATH ve $pdo nesnesi public_html/panel/index.php üzerinden dahil edilen src/panel/config.php'den gelir.
if (!isset($pdo)) {
    require_once ROOT_PATH . '/src/panel/config.php';
}

$posts = [];
try {
    // Blog yazılarını çekelim
    // is_featured sütununu da seçtiğimizden emin olun
    $stmt = $pdo->query("SELECT bp.id, bp.image, bp.title, bp.slug, bp.status, bp.created_at, au.username as author_username, bp.is_featured FROM blog_posts bp JOIN admin_users au ON bp.author_id = au.id ORDER BY bp.created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Blog listelenirken veritabanı hatası: " . $e->getMessage());
    $_SESSION['message'] = 'Sistem hatası: Blog yazılarını yüklerken bir hata oluştu.';
    $_SESSION['message_type'] = 'danger';
}
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Blog Yönetimi</h2>
        <a href="<?php echo BASE_URL; ?>panel/blog/add" class="btn btn-primary">Yeni Blog Yazısı Ekle</a>
    </div>

    <?php
    // Mesajlar (başarı veya hata) burada gösterilecek
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message_type']) . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }

    if (count($posts) > 0) {
        ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Küçük Resim</th>
                                <th>Başlık</th>
                                <th>Durum</th>
                                <th>Öne Çıkarılmış</th>
                                <th>Yazar</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr class="<?php echo ($post['is_featured'] == 1 ? 'featured-row' : ''); ?>" style="vertical-align: middle;">
                                    <td><img src="<?php echo htmlspecialchars($post['image']); ?>" style="width: 50px; height: 50px; object-fit: cover; overflow: hidden;"></td>
                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($post['status']) {
                                            case 'published':
                                                $status_class = 'badge bg-success';
                                                break;
                                            case 'draft':
                                                $status_class = 'badge bg-warning text-dark';
                                                break;
                                            case 'archived':
                                                $status_class = 'badge bg-secondary';
                                                break;
                                        }
                                        echo '<span class="' . $status_class . '">' . htmlspecialchars(ucfirst($post['status'])) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($post['is_featured'] == 1): ?>
                                            <i class="bi bi-star-fill text-warning" title="Öne Çıkarılmış"></i> <?php else: ?>
                                            <i class="bi bi-star text-muted" title="Öne Çıkarılmamış"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($post['author_username']); ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="<?php echo BASE_URL; ?>panel/blog/edit/<?php echo htmlspecialchars($post['id']); ?>"
                                                class="action-button edit-action-button">Düzenle</a>
                                            <button class="action-button delete-action-button"
                                                onclick="deleteBlogPost(<?php echo htmlspecialchars($post['id']); ?>);">Sil</button>
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
        echo '<div class="alert alert-info" role="alert">Henüz hiç blog yazısı bulunmamaktadır. İlk yazınızı ekleyin!</div>';
    }
    ?>

</div>

<script>
    function deleteBlogPost(postId) {
        if (confirm('Bu blog yazısını kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
            fetch('<?php echo BASE_URL; ?>panel/blog/delete/' + postId, {
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