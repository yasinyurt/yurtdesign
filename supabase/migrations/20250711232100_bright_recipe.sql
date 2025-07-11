-- YurtDesign CMS Veritabanı Şeması

-- Kullanıcılar tablosu
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'editor',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blog yazıları tablosu
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    category VARCHAR(100),
    author_id INT,
    featured_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Hizmetler tablosu
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    content TEXT,
    icon VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Projeler tablosu
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    url VARCHAR(255),
    category VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- İletişim mesajları tablosu
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Analitik tablosu
CREATE TABLE analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    page_url VARCHAR(500) NOT NULL,
    user_agent TEXT,
    referrer VARCHAR(500),
    country VARCHAR(100),
    city VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_date (ip_address, created_at),
    INDEX idx_page_date (page_url, created_at),
    INDEX idx_country (country),
    INDEX idx_created_at (created_at)
);

-- Link tıklama takibi tablosu
CREATE TABLE link_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    link_url VARCHAR(500) NOT NULL,
    page_url VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_link_date (link_url, created_at),
    INDEX idx_created_at (created_at)
);

-- Sayfa süresi takibi tablosu
CREATE TABLE page_time (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    page_url VARCHAR(500) NOT NULL,
    time_spent INT NOT NULL, -- saniye cinsinden
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page_date (page_url, created_at),
    INDEX idx_created_at (created_at)
);

-- Site ayarları tablosu
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'number', 'boolean', 'json') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Varsayılan admin kullanıcısı (şifre: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@yurtdesign.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Varsayılan ayarlar
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('site_title', 'YurtDesign - Çorlu Web Tasarım', 'text'),
('site_description', 'Çorlu\'da profesyonel web tasarım hizmetleri', 'textarea'),
('contact_email', 'info@yurtdesign.com', 'text'),
('contact_phone', '+90 XXX XXX XX XX', 'text'),
('contact_address', 'Çorlu, Tekirdağ', 'text'),
('analytics_enabled', '1', 'boolean');

-- Örnek hizmetler
INSERT INTO services (title, description, icon, sort_order) VALUES
('Web Tasarım', 'Profesyonel ve modern web siteleri', 'bi-laptop', 1),
('Web Yazılım', 'Özel yazılım çözümleri', 'bi-code-slash', 2),
('ERP Sistemleri', 'İşletme kaynak planlaması', 'bi-gear', 3),
('CRM Çözümleri', 'Müşteri ilişkileri yönetimi', 'bi-people', 4),
('Sosyal Medya Yönetimi', 'Sosyal medya hesap yönetimi', 'bi-share', 5);

-- Örnek projeler
INSERT INTO projects (title, description, category, sort_order) VALUES
('Kurumsal Web Sitesi', 'Modern ve responsive kurumsal web sitesi', 'Web Tasarım', 1),
('E-Ticaret Platformu', 'Kapsamlı e-ticaret çözümü', 'Web Yazılım', 2),
('Restoran Yönetim Sistemi', 'Restoran için özel ERP sistemi', 'ERP', 3),
('Müşteri Takip Sistemi', 'CRM çözümü', 'CRM', 4);