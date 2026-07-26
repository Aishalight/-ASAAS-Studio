-- ============================================================
-- ASAAS STUDIO - Complete Database Schema
-- Production-Grade Digital Agency Platform
-- ============================================================

CREATE DATABASE IF NOT EXISTS sas_studio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sas_studio;

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) DEFAULT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    company VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    role ENUM('user', 'admin', 'superadmin') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    email_verified_at DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    last_activity DATETIME DEFAULT NULL,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- PASSWORD RESETS TABLE
-- ============================================================
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================================
-- SESSIONS TABLE
-- ============================================================
CREATE TABLE sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (session_token),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================================
-- CATEGORIES TABLE
-- ============================================================
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    type ENUM('blog', 'portfolio', 'service') DEFAULT 'blog',
    icon VARCHAR(50) DEFAULT NULL,
    color VARCHAR(7) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_slug (slug)
) ENGINE=InnoDB;

-- ============================================================
-- POSTS TABLE (Blog)
-- ============================================================
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    excerpt TEXT DEFAULT NULL,
    category_id INT DEFAULT NULL,
    author_id INT NOT NULL,
    featured_image VARCHAR(500) DEFAULT NULL,
    tags VARCHAR(500) DEFAULT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    featured TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_category (category_id),
    INDEX idx_author (author_id),
    INDEX idx_published (published_at),
    FULLTEXT idx_search (title, content, excerpt)
) ENGINE=InnoDB;

-- ============================================================
-- PORTFOLIO PROJECTS TABLE
-- ============================================================
CREATE TABLE portfolio_projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    content LONGTEXT DEFAULT NULL,
    category_id INT DEFAULT NULL,
    client VARCHAR(255) DEFAULT NULL,
    project_date DATE DEFAULT NULL,
    project_url VARCHAR(500) DEFAULT NULL,
    github_url VARCHAR(500) DEFAULT NULL,
    featured_image VARCHAR(500) DEFAULT NULL,
    gallery_images JSON DEFAULT NULL,
    technologies VARCHAR(500) DEFAULT NULL,
    testimonial TEXT DEFAULT NULL,
    testimonial_author VARCHAR(255) DEFAULT NULL,
    testimonial_position VARCHAR(255) DEFAULT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_category (category_id)
) ENGINE=InnoDB;

-- ============================================================
-- SERVICES TABLE
-- ============================================================
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    content LONGTEXT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    price VARCHAR(50) DEFAULT NULL,
    features JSON DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- MESSAGES TABLE
-- ============================================================
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT NOT NULL,
    thread_id INT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at DATETIME DEFAULT NULL,
    parent_id INT DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES messages(id) ON DELETE SET NULL,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_thread (thread_id),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- RATINGS TABLE
-- ============================================================
CREATE TABLE ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    item_type ENUM('project', 'service', 'post', 'business') NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT DEFAULT NULL,
    is_approved TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_item (item_id, item_type),
    INDEX idx_rating (rating),
    UNIQUE KEY unique_rating (user_id, item_id, item_type)
) ENGINE=InnoDB;

-- ============================================================
-- NOTIFICATIONS TABLE
-- ============================================================
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT DEFAULT NULL,
    link VARCHAR(500) DEFAULT NULL,
    icon VARCHAR(50) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- ACTIVITY LOGS TABLE (SIEM-Style)
-- ============================================================
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    details JSON DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    request_method VARCHAR(10) DEFAULT NULL,
    request_url VARCHAR(500) DEFAULT NULL,
    severity ENUM('low','medium','high','critical') DEFAULT 'low',
    status ENUM('normal','suspicious','blocked','malicious','ignored') DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_severity (severity),
    INDEX idx_created (created_at),
    INDEX idx_ip (ip_address)
) ENGINE=InnoDB;

-- ============================================================
-- ALERTS TABLE (SIEM Alerting)
-- ============================================================
CREATE TABLE alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_id INT DEFAULT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'suspicious',
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    severity ENUM('low','medium','high','critical') DEFAULT 'high',
    ip_address VARCHAR(45) DEFAULT NULL,
    user_id INT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    status ENUM('new','acknowledged','resolved','reopened') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (log_id) REFERENCES activity_logs(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_log (log_id),
    INDEX idx_type (type),
    INDEX idx_severity (severity),
    INDEX idx_read (is_read)
) ENGINE=InnoDB;

-- ============================================================
-- ACTION LOGS TABLE (Admin Action Tracking)
-- ============================================================
CREATE TABLE action_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_id INT DEFAULT NULL,
    alert_id INT DEFAULT NULL,
    action_type VARCHAR(50) NOT NULL,
    action_details JSON DEFAULT NULL,
    performed_by INT DEFAULT NULL,
    target_user_id INT DEFAULT NULL,
    target_ip VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (log_id) REFERENCES activity_logs(id) ON DELETE SET NULL,
    FOREIGN KEY (alert_id) REFERENCES alerts(id) ON DELETE SET NULL,
    INDEX idx_log (log_id),
    INDEX idx_alert (alert_id),
    INDEX idx_action_type (action_type),
    INDEX idx_performed_by (performed_by),
    INDEX idx_target_user (target_user_id),
    INDEX idx_target_ip (target_ip),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- BLOCKED IPS TABLE (Persistent IP Blocking)
-- ============================================================
CREATE TABLE blocked_ips (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    reason VARCHAR(500) DEFAULT NULL,
    blocked_by INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unblocked_at DATETIME DEFAULT NULL,
    FOREIGN KEY (blocked_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ip (ip_address),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- FOLDERS TABLE (File Manager)
-- ============================================================
CREATE TABLE folders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_parent (parent_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================================
-- MEDIA TABLE
-- ============================================================
CREATE TABLE media (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    folder_id INT DEFAULT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    thumbnail_path VARCHAR(500) DEFAULT NULL,
    type VARCHAR(50) NOT NULL,
    mime_type VARCHAR(100) DEFAULT NULL,
    size INT NOT NULL DEFAULT 0,
    width INT DEFAULT NULL,
    height INT DEFAULT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    caption TEXT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_folder (folder_id),
    INDEX idx_type (type),
    INDEX idx_mime (mime_type)
) ENGINE=InnoDB;

-- ============================================================
-- CONTACT MESSAGES TABLE
-- ============================================================
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at DATETIME DEFAULT NULL,
    replied_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- TESTIMONIALS TABLE
-- ============================================================
CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100) DEFAULT NULL,
    company VARCHAR(255) DEFAULT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    content TEXT NOT NULL,
    rating TINYINT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- FAQS TABLE
-- ============================================================
CREATE TABLE faqs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- SETTINGS TABLE
-- ============================================================
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value LONGTEXT DEFAULT NULL,
    setting_group VARCHAR(50) DEFAULT 'general',
    type VARCHAR(20) DEFAULT 'text',
    is_public TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key),
    INDEX idx_group (setting_group)
) ENGINE=InnoDB;

-- ============================================================
-- NEWSLETTER SUBSCRIBERS TABLE
-- ============================================================
CREATE TABLE subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(100) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at DATETIME DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- PAGE VISITS TABLE (Analytics)
-- ============================================================
CREATE TABLE page_visits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    page_url VARCHAR(500) NOT NULL,
    visitor_ip VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    device_type ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop',
    browser VARCHAR(50) DEFAULT NULL,
    browser_version VARCHAR(20) DEFAULT NULL,
    os VARCHAR(50) DEFAULT NULL,
    os_version VARCHAR(20) DEFAULT NULL,
    referrer VARCHAR(500) DEFAULT NULL,
    visit_date DATE DEFAULT (CURDATE()),
    visit_time TIME DEFAULT (CURRENT_TIME),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page (page_url),
    INDEX idx_device (device_type),
    INDEX idx_browser (browser),
    INDEX idx_os (os),
    INDEX idx_date (visit_date),
    INDEX idx_ip (visitor_ip)
) ENGINE=InnoDB;

-- Sample analytics data
INSERT INTO page_visits (page_url, visitor_ip, user_agent, device_type, browser, os, referrer, visit_date) VALUES
('/', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36', 'desktop', 'Chrome', 'Windows 10', 'https://google.com', CURDATE()),
('/services', '192.168.1.2', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148', 'mobile', 'Safari', 'iOS 17.0', 'https://facebook.com', CURDATE()),
('/portfolio', '192.168.1.3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/120.0.0.0 Safari/537.36', 'desktop', 'Edge', 'Windows 10', NULL, CURDATE()),
('/blog', '192.168.1.4', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36', 'desktop', 'Chrome', 'macOS 10.15', 'https://twitter.com', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
('/about', '192.168.1.5', 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36', 'mobile', 'Chrome', 'Android 14', NULL, DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
('/contact', '192.168.1.6', 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148', 'tablet', 'Safari', 'iOS 17.0', 'https://google.com', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
('/services', '192.168.1.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/121.0 Gecko/20100101 Firefox/121.0', 'desktop', 'Firefox', 'Windows 10', NULL, DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
('/', '192.168.1.8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36', 'desktop', 'Chrome', 'Windows 10', 'https://google.com', DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
('/portfolio', '192.168.1.9', 'Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 Chrome/119.0.0.0 Mobile Safari/537.36', 'mobile', 'Chrome', 'Android 13', NULL, DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
('/blog', '192.168.1.10', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', 'desktop', 'Safari', 'macOS 10.15', 'https://linkedin.com', DATE_SUB(CURDATE(), INTERVAL 4 DAY)),
('/', '192.168.1.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/119.0.0.0 Safari/537.36', 'desktop', 'Chrome', 'Windows 10', NULL, DATE_SUB(CURDATE(), INTERVAL 4 DAY)),
('/services', '192.168.1.12', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148', 'mobile', 'Safari', 'iOS 16.6', 'https://google.com', DATE_SUB(CURDATE(), INTERVAL 5 DAY)),
('/', '192.168.1.13', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/119.0.0.0 Safari/537.36', 'desktop', 'Edge', 'Windows 10', NULL, DATE_SUB(CURDATE(), INTERVAL 5 DAY)),
('/contact', '192.168.1.14', 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/120.0.0.0 Mobile Safari/537.36', 'mobile', 'Chrome', 'Android 14', 'https://instagram.com', DATE_SUB(CURDATE(), INTERVAL 6 DAY)),
('/portfolio', '192.168.1.15', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/120.0.0.0 Safari/537.36', 'desktop', 'Chrome', 'macOS 10.15', NULL, DATE_SUB(CURDATE(), INTERVAL 6 DAY)),
('/about', '192.168.1.16', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Firefox/121.0 Gecko/20100101 Firefox/121.0', 'desktop', 'Firefox', 'Windows 10', 'https://google.com', DATE_SUB(CURDATE(), INTERVAL 7 DAY)),
('/', '192.168.1.17', 'Mozilla/5.0 (iPad; CPU OS 16_6 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148', 'tablet', 'Safari', 'iOS 16.6', NULL, DATE_SUB(CURDATE(), INTERVAL 7 DAY)),
('/blog', '192.168.1.18', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36', 'desktop', 'Chrome', 'Windows 10', 'https://twitter.com', DATE_SUB(CURDATE(), INTERVAL 7 DAY));

-- ============================================================
-- DEFAULT DATA
-- ============================================================

-- Default Admin User (password: 4saas@2020$$)
INSERT INTO users (name, username, email, password, role, status, email_verified_at) VALUES
('AsaasTeams', 'AsaasTeams', 'admin@asaasstudio.com', '$2y$10$igfefJRegA5G.9PO1ZEwD.buSetY84CmiQa9YFpvZIn/yFif33yH2', 'superadmin', 'active', NOW()),
('Demo User', 'DemoUser', 'user@asaasstudio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', NOW());

-- Default Settings
INSERT INTO settings (setting_key, setting_value, setting_group, type, is_public) VALUES
('site_name', 'ASAAS STUDIO', 'general', 'text', 1),
('site_description', 'Premium Digital Agency – Branding, Web Development & Creative Design', 'general', 'text', 1),
('site_email', 'hello@asaasstudio.com', 'general', 'email', 1),
('site_phone', '+1 (555) 123-4567', 'general', 'text', 1),
('site_address', '123 Creative Lane, Design District, NY 10001', 'general', 'text', 1),
('social_twitter', 'https://twitter.com/asaasstudio', 'social', 'url', 1),
('social_instagram', 'https://instagram.com/asaasstudio', 'social', 'url', 1),
('social_linkedin', 'https://linkedin.com/company/asaasstudio', 'social', 'url', 1),
('social_github', 'https://github.com/asaasstudio', 'social', 'url', 1),
('max_login_attempts', '5', 'security', 'number', 0),
('lockout_duration', '15', 'security', 'number', 0),
('items_per_page', '12', 'general', 'number', 1),
('maintenance_mode', '0', 'general', 'boolean', 0),
('theme_color', '#E8632A', 'appearance', 'text', 1),
('theme_color_hover', '#d4551f', 'appearance', 'text', 1);

-- Default Categories
INSERT INTO categories (name, slug, description, type, icon, color, sort_order) VALUES
('Web Design', 'web-design', 'Web design projects and articles', 'blog', 'palette', '#E8632A', 1),
('Web Development', 'web-development', 'Web development projects and articles', 'blog', 'code', '#2196F3', 2),
('Branding', 'branding', 'Brand identity projects and articles', 'blog', 'award', '#9C27B0', 3),
('UI/UX Design', 'ui-ux-design', 'UI/UX design projects and articles', 'blog', 'layers', '#00BCD4', 4),
('Marketing', 'marketing', 'Digital marketing projects and articles', 'blog', 'trending-up', '#4CAF50', 5),
('Web Design', 'web-design-portfolio', 'Web design portfolio', 'portfolio', 'palette', '#E8632A', 1),
('Web Development', 'web-development-portfolio', 'Web development portfolio', 'portfolio', 'code', '#2196F3', 2),
('Branding', 'branding-portfolio', 'Branding portfolio', 'portfolio', 'award', '#9C27B0', 3);

-- Default Services
INSERT INTO services (title, slug, description, icon, features, sort_order) VALUES
('Web Design', 'web-design', 'Beautiful, responsive websites that captivate your audience and drive results.', 'palette', '["Custom UI/UX Design","Responsive Design","Wireframing & Prototyping","Animation & Micro-interactions","SEO Optimization","Performance Optimization"]', 1),
('Web Development', 'web-development', 'Powerful web applications built with cutting-edge technologies and best practices.', 'code', '["Full-Stack Development","API Development","Database Design","Cloud Deployment","Security Implementation","Performance Tuning"]', 2),
('Branding', 'branding', 'Strategic brand identity that tells your story and connects with your audience.', 'award', '["Brand Strategy","Logo Design","Visual Identity","Brand Guidelines","Packaging Design","Brand Voice"]', 3),
('UI/UX Design', 'ui-ux-design', 'Intuitive user experiences that delight users and achieve business goals.', 'layers', '["User Research","Information Architecture","Interaction Design","Usability Testing","Design Systems","Prototyping"]', 4),
('Digital Marketing', 'digital-marketing', 'Data-driven marketing strategies that grow your brand and increase revenue.', 'trending-up', '["SEO Strategy","Social Media Marketing","Content Marketing","Email Campaigns","Analytics & Reporting","PPC Advertising"]', 5),
('Mobile Development', 'mobile-development', 'Native and cross-platform mobile apps that deliver exceptional user experiences.', 'smartphone', '["iOS Development","Android Development","Cross-Platform Apps","App Store Optimization","Push Notifications","Analytics Integration"]', 6);

-- Default Testimonials
INSERT INTO testimonials (name, position, company, content, rating, sort_order) VALUES
('Sarah Johnson', 'CEO', 'TechVolve', 'ASAAS Studio transformed our digital presence completely. Our traffic increased by 300% in just 3 months. The team is incredibly talented and professional.', 5, 1),
('Michael Chen', 'Founder', 'GreenLeaf Organics', 'Working with ASAAS was a game-changer for our brand. Their design thinking and execution are simply outstanding. Highly recommended!', 5, 2),
('Emily Rodriguez', 'Marketing Director', 'Pulse Fitness', 'The website ASAAS built for us is absolutely stunning. The attention to detail and user experience is remarkable. Our conversion rate doubled!', 5, 3),
('David Kim', 'CTO', 'FinFlow', 'Exceptional technical expertise and creative vision. ASAAS delivered our platform on time and exceeded all expectations. A true partnership.', 5, 4),
('Lisa Thompson', 'Product Manager', 'Bloom Cosmetics', 'From concept to launch, ASAAS Studio was professional, creative, and responsive. Our new brand identity is exactly what we needed.', 4, 5);

-- Default FAQs
INSERT INTO faqs (question, answer, category, sort_order) VALUES
('What is the typical timeline for a web development project?', 'Timelines vary based on project scope. A typical website takes 4-8 weeks, while complex web applications may take 3-6 months. We provide detailed timelines during the proposal phase.', 'process', 1),
('How much does a website cost?', 'Our projects start from $5,000 for basic websites and go up to $50,000+ for complex web applications. Every project is custom-quoted based on your specific needs and requirements.', 'pricing', 2),
('Do you offer ongoing maintenance and support?', 'Yes! We offer flexible maintenance plans to keep your website secure, updated, and performing at its best. Our support team is available 24/7 for critical issues.', 'support', 3),
('What technologies do you use?', 'We use modern technologies including React, Vue.js, Node.js, PHP, Python, and various CMS platforms. We choose the best tech stack for each project based on requirements.', 'technical', 4),
('How do we get started?', 'Simply reach out through our contact form or schedule a free consultation call. We will discuss your project, goals, and budget, then provide a tailored proposal within 48 hours.', 'process', 5),
('Do you work with startups?', 'Absolutely! We love working with startups. We offer flexible engagement models and can work within startup budgets while delivering enterprise-quality results.', 'general', 6),
('Can you redesign an existing website?', 'Yes, we specialize in website redesigns. We analyze your current site, identify improvement opportunities, and create a modern, high-performing website that meets your goals.', 'services', 7),
('What is your revision process?', 'We include 2-3 rounds of revisions in our standard packages. Our collaborative process ensures you are involved at every stage, with clear milestones and feedback opportunities.', 'process', 8);
