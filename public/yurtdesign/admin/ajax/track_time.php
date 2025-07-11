<?php
require_once '../../../src/config.php';
require_once '../../../src/includes/analytics.php';

if ($_POST && isset($_POST['page_url']) && isset($_POST['time_spent'])) {
    $analytics = new Analytics();
    $page_url = $_POST['page_url'];
    $time_spent = (int)$_POST['time_spent'];
    
    // Minimum 5 saniye, maksimum 30 dakika
    if ($time_spent >= 5 && $time_spent <= 1800) {
        $analytics->trackPageTime($page_url, $time_spent);
    }
}
?>