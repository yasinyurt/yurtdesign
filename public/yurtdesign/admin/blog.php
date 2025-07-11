<?php
session_start();
require_once '../../../src/config.php';
require_once '../../../src/includes/database.php';
require_once '../../../src/includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

requirePermission('manage_blog');

$db = Database::getInstance();
$message = '';
$error = '';

// Blog yazısı ekleme/düzenleme
if ($_POST && isset($_POST['action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $error = 'Güvenlik hatası.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $category = trim($_POST['category'] ?? '');
        
        if (empty($title) || empty($content)) {
            $error = 'Başlık ve içerik gereklidir.';
        } else {
            if ($_POST['action'] === 'add') {
                $db->query(
                    "INSERT INTO blog_posts (title, content, excerpt, status, category, author_id, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW())",
                    [$title, $content, $excerpt, $status, $category, $_SESSION['user_id']]
                );
                $message = 'Blog yazısı başarıyla eklendi.';
            } elseif ($_POST['action'] === 'edit') {
                $id = (int)$_POST['id'];
                $db->query(
                    "UPDATE blog_posts SET title = ?, content = ?, excerpt = ?, status = ?, category = ?, updated_at = NOW() 
                     WHERE id = ?",
                    [$title, $content, $excerpt, $status, $category, $id]
                );
                $message = 'Blog yazısı başarıyla güncellendi.';
            }
        }
    }
}

// Blog yazısı silme
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM blog_posts WHERE id = ?", [$id]);
    $message = 'Blog yazısı silindi.';
}

// Blog yazılarını listele
$posts = $db->query(
    "SELECT bp.*, u.username as author_name 
     FROM blog_posts bp 
     LEFT JOIN users u ON bp.author_id = u.id 
     ORDER BY bp.created_at DESC"
)->fetchAll();

// Düzenleme için blog yazısı getir
$editPost = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editPost = $db->query("SELECT * FROM blog_posts WHERE id = ?", [$id])->fetch();
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-journal-text me-2"></i>Blog Yönetimi
                </h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#blogModal">
                    <i class="bi bi-plus-circle me-2"></i>Yeni Yazı
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Blog Yazıları Listesi -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Blog Yazıları</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Başlık</th>
                                    <th>Kategori</th>
                                    <th>Durum</th>
                                    <th>Yazar</th>
                                    <th>Tarih</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                        <?php if ($post['excerpt']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($post['excerpt'], 0, 100)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($post['category']): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($post['category']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($post['status'] === 'published'): ?>
                                        <span class="badge bg-success">Yayında</span>
                                        <?php else: ?>
                                        <span class="badge bg-warning">Taslak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editPost(<?php echo $post['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="?delete=<?php echo $post['id']; ?>" class="btn btn-outline-danger btn-delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Blog Modal -->
<div class="modal fade" id="blogModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Blog Yazısı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add" id="formAction">
                    <input type="hidden" name="id" value="" id="postId">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Başlık *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Kategori</label>
                                <input type="text" class="form-control" id="category" name="category">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Durum</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="draft">Taslak</option>
                                    <option value="published">Yayınla</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Özet</label>
                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">İçerik *</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editPost(id) {
    // AJAX ile blog yazısını getir ve formu doldur
    fetch(`ajax/get_blog_post.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('formAction').value = 'edit';
                document.getElementById('postId').value = data.post.id;
                document.getElementById('title').value = data.post.title;
                document.getElementById('category').value = data.post.category || '';
                document.getElementById('status').value = data.post.status;
                document.getElementById('excerpt').value = data.post.excerpt || '';
                document.getElementById('content').value = data.post.content;
                
                new bootstrap.Modal(document.getElementById('blogModal')).show();
            }
        });
}

// Modal kapandığında formu temizle
document.getElementById('blogModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('postId').value = '';
    document.querySelector('#blogModal form').reset();
});
</script>

<?php include 'includes/footer.php'; ?>