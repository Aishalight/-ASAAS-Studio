-- ============================================================
-- ASAAS STUDIO - Complete Database Schema
-- Small Digital Studio Platform
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
    project_type ENUM('client','internal','concept','student') DEFAULT 'client',
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

-- ============================================================
-- DEFAULT DATA
-- ============================================================

-- Default Admin User (password: 4saas@2020$$)
INSERT INTO users (name, username, email, password, role, status, email_verified_at) VALUES
('AsaasTeams', 'AsaasTeams', 'admin@asaas-studio.tech', '$2y$10$igfefJRegA5G.9PO1ZEwD.buSetY84CmiQa9YFpvZIn/yFif33yH2', 'superadmin', 'active', NOW()),

('Demo User', 'DemoUser', 'user@asaas-studio.tech', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', NOW());
-- Default Settings
INSERT INTO settings (setting_key, setting_value, setting_group, type, is_public) VALUES
('site_name', 'ASAAS STUDIO', 'general', 'text', 1),
('site_description', 'ASAAS STUDIO is a digital studio based in Mogadishu, Somalia, designing and building websites, custom web systems, and digital experiences for businesses and organizations.', 'general', 'text', 1),
('site_email', 'info@asaas-studio.tech', 'general', 'email', 1),
('site_phone', '', 'general', 'text', 1),
('site_address', 'Mogadishu, Somalia', 'general', 'text', 1),
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
INSERT INTO services (title, slug, description, icon, price, features, sort_order) VALUES
('Starter Website', 'starter-website', 'A clean, fast single-page website to get you online.', 'layout', '$99', '["Single Page","Contact Form","Mobile Responsive","Basic SEO"]', 1),
('Business Website', 'business-website', 'A multi-page business website for a full professional presence.', 'globe', '$299', '["Up to 5 Pages","Contact Form","Mobile Responsive","SEO Basics","WhatsApp Integration"]', 2),
('Custom Web System', 'custom-web-system', 'Bespoke web systems built around how your work actually happens.', 'code', '$999', '["Custom Requirements","Database Design","Admin Panel","Ongoing Support"]', 3),
('SEO Service', 'seo-service', 'Monthly search engine optimisation to help people find you.', 'trending-up', '$99/mo', '["Keyword Research","On-Page SEO","Monthly Reports","Local SEO"]', 4),
('Social Media Management', 'social-media-management', 'Monthly management of your social media presence.', 'share-2', '$149/mo', '["Content Creation","Scheduling","Community Replies","Monthly Reports"]', 5),
('Website Maintenance', 'website-maintenance', 'Monthly updates, backups and security for your site.', 'shield-check', '$49/mo', '["Updates","Backups","Security","Small Changes"]', 6),
('UI/UX Design', 'ui-ux-design', 'User interface and experience design, quoted per project.', 'layers', 'Custom', '["User Research","Wireframes","UI Design","Prototypes"]', 7);

-- Default FAQs
INSERT INTO faqs (question, answer, category, sort_order) VALUES
('How much does a website cost?', 'Starter websites start at $99, business websites at $299, and custom web systems at $999. Every project is different, so we give you a clear quote based on your specific requirements before any work starts.', 'pricing', 1),
('What is the typical timeline for a project?', 'It depends on the scope. A simple website can take about a week, while a custom web system takes longer. We agree on a timeline before we start and keep you updated as we go.', 'process', 2),
('Do you offer ongoing maintenance and support?', 'Yes. Our maintenance plan starts at $49/month and covers updates, security, backups, and small changes, so your website stays secure and running smoothly.', 'support', 3),
('What technologies do you use?', 'We choose tools that fit the project. We build with PHP, JavaScript, and standard web technologies, and we keep things simple and maintainable.', 'technical', 4),
('How do we get started?', 'Reach out through our contact form or book a call. Tell us what you want to build and we will reply with next steps and a quote.', 'process', 5),
('Do you work with clients outside Mogadishu?', 'Yes. We work remotely and can serve clients anywhere with an internet connection.', 'general', 6),
('What information do you need to provide a quote?', 'A short description of what you want to build is enough to start. Examples of sites you like and details about your users help us give a more accurate quote.', 'services', 7),
('What is your revision policy?', 'Our agreements include a defined number of revision rounds for each phase. Additional revisions beyond the agreed scope are quoted separately.', 'process', 8),
('Do you offer refunds?', 'Because our work is custom, we do not offer full refunds. If you are not satisfied with a phase of the project, we will work with you to make it right. Terms are outlined in each service agreement.', 'support', 9);
