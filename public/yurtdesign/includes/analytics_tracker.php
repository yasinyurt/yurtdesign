<?php
// Bu dosya her sayfada include edilecek
require_once '../../src/includes/analytics.php';

$analytics = new Analytics();

// Sayfa görüntüleme takibi
$page_url = $_SERVER['REQUEST_URI'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referrer = $_SERVER['HTTP_REFERER'] ?? '';

$analytics->trackPageView($page_url, $user_agent, $referrer);
?>

<script>
// Sayfa süresi takibi
let startTime = Date.now();
let isActive = true;

// Sayfa terk edildiğinde süreyi kaydet
window.addEventListener('beforeunload', function() {
    if (isActive) {
        let timeSpent = Math.round((Date.now() - startTime) / 1000);
        
        // Beacon API ile veri gönder (sayfa kapanırken bile çalışır)
        if (navigator.sendBeacon) {
            let formData = new FormData();
            formData.append('page_url', window.location.pathname);
            formData.append('time_spent', timeSpent);
            navigator.sendBeacon('/admin/ajax/track_time.php', formData);
        }
    }
});

// Sayfa görünürlük değişikliklerini takip et
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        isActive = false;
    } else {
        startTime = Date.now();
        isActive = true;
    }
});

// Link tıklama takibi
document.addEventListener('click', function(e) {
    let link = e.target.closest('a');
    if (link && link.href) {
        // External linkler için takip
        if (link.hostname !== window.location.hostname) {
            fetch('/admin/ajax/track_link.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    link_url: link.href,
                    page_url: window.location.pathname
                })
            });
        }
    }
});
</script>