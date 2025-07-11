<?php
require_once 'database.php';

function login($username, $password) {
    $db = Database::getInstance();
    
    $stmt = $db->query(
        "SELECT * FROM users WHERE username = ? AND status = 'active'",
        [$username]
    );
    
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        
        // Son giriş zamanını güncelle
        $db->query(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']]
        );
        
        return $user;
    }
    
    return false;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

function isLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Session timeout kontrolü (30 dakika)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        logout();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance();
    $stmt = $db->query(
        "SELECT * FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
    
    return $stmt->fetch();
}

function hasPermission($permission) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    // Admin her şeyi yapabilir
    if ($user['role'] === 'admin') {
        return true;
    }
    
    // Editör sadece içerik yönetimi yapabilir
    if ($user['role'] === 'editor') {
        $editorPermissions = [
            'view_dashboard',
            'manage_blog',
            'manage_services',
            'manage_projects',
            'manage_contact'
        ];
        return in_array($permission, $editorPermissions);
    }
    
    return false;
}

function requirePermission($permission) {
    if (!hasPermission($permission)) {
        header('HTTP/1.0 403 Forbidden');
        die('Bu işlem için yetkiniz bulunmamaktadır.');
    }
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}