<?php
require_once 'database.php';

class Analytics {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function trackPageView($page_url, $user_agent = null, $referrer = null) {
        $ip_address = $this->getRealIpAddr();
        $location = $this->getLocationFromIP($ip_address);
        
        $this->db->query(
            "INSERT INTO analytics (
                ip_address, page_url, user_agent, referrer, 
                country, city, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [
                $ip_address,
                $page_url,
                $user_agent ?: $_SERVER['HTTP_USER_AGENT'] ?? '',
                $referrer ?: $_SERVER['HTTP_REFERER'] ?? '',
                $location['country'] ?? '',
                $location['city'] ?? ''
            ]
        );
    }
    
    public function trackLinkClick($link_url, $page_url) {
        $ip_address = $this->getRealIpAddr();
        
        $this->db->query(
            "INSERT INTO link_clicks (
                ip_address, link_url, page_url, created_at
            ) VALUES (?, ?, ?, NOW())",
            [$ip_address, $link_url, $page_url]
        );
    }
    
    public function trackPageTime($page_url, $time_spent) {
        $ip_address = $this->getRealIpAddr();
        
        $this->db->query(
            "INSERT INTO page_time (
                ip_address, page_url, time_spent, created_at
            ) VALUES (?, ?, ?, NOW())",
            [$ip_address, $page_url, $time_spent]
        );
    }
    
    public function getDashboardStats($days = 30) {
        $stats = [];
        
        // Toplam ziyaretçi sayısı
        $stmt = $this->db->query(
            "SELECT COUNT(DISTINCT ip_address) as count 
             FROM analytics 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
        $stats['unique_visitors'] = $stmt->fetch()['count'];
        
        // Toplam sayfa görüntüleme
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count 
             FROM analytics 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
        $stats['page_views'] = $stmt->fetch()['count'];
        
        // Ortalama sayfa süresi
        $stmt = $this->db->query(
            "SELECT AVG(time_spent) as avg_time 
             FROM page_time 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$days]
        );
        $stats['avg_time_on_page'] = round($stmt->fetch()['avg_time'] ?? 0, 2);
        
        // En popüler sayfalar
        $stmt = $this->db->query(
            "SELECT page_url, COUNT(*) as views 
             FROM analytics 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY page_url 
             ORDER BY views DESC 
             LIMIT 10",
            [$days]
        );
        $stats['popular_pages'] = $stmt->fetchAll();
        
        // Ülke bazında ziyaretçiler
        $stmt = $this->db->query(
            "SELECT country, COUNT(DISTINCT ip_address) as visitors 
             FROM analytics 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND country != ''
             GROUP BY country 
             ORDER BY visitors DESC 
             LIMIT 10",
            [$days]
        );
        $stats['visitors_by_country'] = $stmt->fetchAll();
        
        // Günlük ziyaretçi trendi
        $stmt = $this->db->query(
            "SELECT DATE(created_at) as date, COUNT(DISTINCT ip_address) as visitors 
             FROM analytics 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(created_at) 
             ORDER BY date ASC",
            [$days]
        );
        $stats['daily_visitors'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    public function getLinkClickStats($days = 30) {
        $stmt = $this->db->query(
            "SELECT link_url, COUNT(*) as clicks 
             FROM link_clicks 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY link_url 
             ORDER BY clicks DESC 
             LIMIT 20",
            [$days]
        );
        
        return $stmt->fetchAll();
    }
    
    private function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
    
    private function getLocationFromIP($ip) {
        // Basit IP lokasyon servisi (ücretsiz)
        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}");
            if ($response) {
                $data = json_decode($response, true);
                if ($data && $data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? '',
                        'city' => $data['city'] ?? ''
                    ];
                }
            }
        } catch (Exception $e) {
            // Hata durumunda boş döndür
        }
        
        return ['country' => '', 'city' => ''];
    }
}