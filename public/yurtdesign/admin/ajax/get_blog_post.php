<?php
session_start();
require_once '../../../../src/config.php';
require_once '../../../../src/includes/database.php';
require_once '../../../../src/includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasPermission('manage_blog')) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz ID']);
    exit;
}

$db = Database::getInstance();
$post = $db->query("SELECT * FROM blog_posts WHERE id = ?", [$id])->fetch();

if ($post) {
    echo json_encode(['success' => true, 'post' => $post]);
} else {
    echo json_encode(['success' => false, 'message' => 'Blog yazısı bulunamadı']);
}
?>