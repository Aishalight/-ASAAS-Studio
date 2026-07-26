<?php
require_once __DIR__ . '/../config/functions.php';
$db = Database::getInstance()->getConnection();

echo "=== ASAAS Studio Content Seed ===\n";

// --- 1. Categories ---
echo "\n[1/5] Seeding categories...\n";
$categories = [
    ['Web Design', 'web-design', 'blog', '#E8632A'],
    ['Web Development', 'web-development', 'blog', '#2196F3'],
    ['Branding', 'branding', 'blog', '#9C27B0'],
    ['UI/UX Design', 'ui-ux-design', 'blog', '#00BCD4'],
    ['Marketing', 'marketing', 'blog', '#4CAF50'],
    ['Web Design', 'web-design-portfolio', 'portfolio', '#E8632A'],
    ['Web Development', 'web-development-portfolio', 'portfolio', '#2196F3'],
    ['Branding', 'branding-portfolio', 'portfolio', '#9C27B0'],
    ['Mobile', 'mobile-portfolio', 'portfolio', '#FF9800'],
];
$catIds = [];
foreach ($categories as $c) {
    $existing = $db->prepare("SELECT id FROM categories WHERE slug = ?");
    $existing->execute([$c[1]]);
    if ($row = $existing->fetch()) {
        $catIds[$c[1]] = $row['id'];
        echo "  - Category '{$c[0]}' already exists (ID: {$row['id']})\n";
    } else {
        $stmt = $db->prepare("INSERT INTO categories (name, slug, type, color, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$c[0], $c[1], $c[2], $c[3]]);
        $catIds[$c[1]] = $db->lastInsertId();
        echo "  + Created category '{$c[0]}' (ID: {$catIds[$c[1]]})\n";
    }
}

// --- 2. Admin user ID ---
$adminId = 1;
$adminCheck = $db->query("SELECT id FROM users WHERE role IN ('admin','superadmin') ORDER BY id ASC LIMIT 1")->fetch();
if ($adminCheck) $adminId = $adminCheck['id'];

// --- 3. Blog Posts ---
echo "\n[2/5] Seeding blog posts...\n";
$posts = [
    [
        'The Future of Web Design: Trends to Watch in 2026',
        'future-web-design-trends-2026',
        '<p>The web design landscape is evolving faster than ever. As we move through 2026, new technologies, user expectations, and design philosophies are reshaping how we create digital experiences.</p>

<h3>1. AI-Powered Design Tools</h3>
<p>Artificial intelligence is revolutionizing web design. From automated layout generation to intelligent content suggestions, AI tools are making designers more productive and enabling new creative possibilities.</p>

<h3>2. Immersive 3D Experiences</h3>
<p>With browser capabilities advancing rapidly, 3D web experiences are becoming more accessible. Using WebGL, Three.js, and new CSS features, designers can create immersive, interactive 3D elements that captivate users.</p>

<h3>3. Advanced Micro-interactions</h3>
<p>Micro-interactions are becoming more sophisticated. From subtle hover effects to complex gesture-based interactions, these small details significantly impact user experience and engagement.</p>

<h3>4. Sustainable Web Design</h3>
<p>Environmental consciousness is influencing web design decisions. Optimized performance, reduced data transfer, and dark mode options are becoming standard practices.</p>',
        'Explore the cutting-edge trends shaping the future of web design and how they can transform your digital presence.',
        $catIds['web-design'] ?? null, 'published', 'web-design',
        'https://images.unsplash.com/photo-1487017159836-4e23ece2e4cf?w=800&h=500&fit=crop',
        'web design, trends, 2026, ai, 3d', 1, 245
    ],
    [
        'Building Scalable Web Applications: A Complete Guide',
        'building-scalable-web-applications-guide',
        '<p>Scalability is the backbone of modern web applications. Whether you are building a startup MVP or an enterprise platform, understanding scalability principles is critical.</p>

<h3>Choosing the Right Architecture</h3>
<p>Microservices vs monolithic — each has its place. For most startups, a well-structured monolith is the right starting point. Scale into microservices when the pain points become real.</p>

<h3>Database Optimization</h3>
<p>Proper indexing, query optimization, and caching strategies can dramatically improve performance. Redis, Memcached, and CDN caching are essential tools in your arsenal.</p>

<h3>Load Balancing</h3>
<p>Distributing traffic across multiple servers ensures high availability. Horizontal scaling with load balancers like Nginx or cloud-native solutions like AWS ALB are industry standards.</p>

<h3>Monitoring and Observability</h3>
<p>You cannot improve what you cannot measure. Implement logging, tracing, and alerting from day one. Tools like Datadog, New Relic, and Grafana provide deep insights.</p>',
        'Learn the principles and practices for building web applications that scale from hundreds to millions of users.',
        $catIds['web-development'] ?? null, 'published', 'web-development',
        'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&h=500&fit=crop',
        'web development, scalability, architecture, backend', 0, 189
    ],
    [
        'Brand Identity in the Digital Age',
        'brand-identity-digital-age',
        '<p>Your brand identity is more than a logo. In the digital age, it is a comprehensive system of visual and verbal elements that communicate your values, personality, and promise.</p>

<h3>Consistency Across Touchpoints</h3>
<p>From your website to social media to email signatures, every touchpoint should reinforce your brand. A well-documented brand guide ensures consistency at scale.</p>

<h3>Emotional Design</h3>
<p>Great brands evoke emotion. Color psychology, typography choices, and imagery all contribute to how people feel when they interact with your brand.</p>

<h3>Brand Voice and Messaging</h3>
<p>Your tone of voice defines how you communicate. Whether professional, playful, or authoritative, consistency in messaging builds trust and recognition.</p>

<h3>Digital Brand Experiences</h3>
<p>Your website is often the first impression. Investing in a cohesive digital brand experience pays dividends in trust, conversion, and loyalty.</p>',
        'How to create a brand identity that resonates across digital channels and builds lasting connections.',
        $catIds['branding'] ?? null, 'published', 'branding',
        'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&h=500&fit=crop',
        'branding, identity, design, digital', 0, 132
    ],
    [
        'UI/UX Principles That Drive Conversions',
        'uiux-principles-drive-conversions',
        '<p>Good design is not just about aesthetics. The best user interfaces are invisible — they guide users effortlessly toward their goals while driving business outcomes.</p>

<h3>Visual Hierarchy</h3>
<p>Guide the eye with size, color, contrast, and spacing. The most important elements should be the most prominent. Every pixel should earn its place.</p>

<h3>Reducing Cognitive Load</h3>
<p>Simplicity is the ultimate sophistication. Break complex tasks into manageable steps. Use progressive disclosure to show information only when needed.</p>

<h3>Trust Signals</h3>
<p>Social proof, testimonials, security badges, and transparent pricing reduce friction and build confidence. Users need to feel safe before they convert.</p>

<h3>Mobile-First Design</h3>
<p>Over 60% of web traffic is mobile. Designing for the smallest screen first ensures your experience works everywhere. Responsive is not optional — it is foundational.</p>',
        'Discover the user experience principles that can significantly improve your website conversion rates.',
        $catIds['ui-ux-design'] ?? null, 'published', 'ui-ux-design',
        'https://images.unsplash.com/photo-1586717791821-3f44a563fa4c?w=800&h=500&fit=crop',
        'uiux, design, conversions, ux', 1, 203
    ],
    [
        'SEO Strategies for 2026: What Actually Works',
        'seo-strategies-2026-what-works',
        '<p>SEO is not dead. It has evolved. In 2026, search engines are smarter than ever, and the strategies that work are fundamentally different from what worked five years ago.</p>

<h3>Quality Over Quantity</h3>
<p>One exceptional article outperforms ten mediocre ones. Google rewards depth, expertise, and genuine value. Write for humans first, search engines second.</p>

<h3>Technical SEO Fundamentals</h3>
<p>Core Web Vitals, structured data, and proper indexing are non-negotiable. A technically sound site gives your content the best chance to rank.</p>

<h3>Entity-Based SEO</h3>
<p>Search engines understand entities, not just keywords. Build topical authority by creating comprehensive content clusters around your core expertise.</p>

<h3>User Experience as SEO</h3>
<p>Engagement metrics matter. If users click back to search results, your ranking suffers. Create content that keeps people reading, clicking, and sharing.</p>',
        'A practical guide to SEO strategies that deliver real results in today competitive digital landscape.',
        $catIds['marketing'] ?? null, 'published', 'marketing',
        'https://images.unsplash.com/photo-1432889821006-3149409b1c1f?w=800&h=500&fit=crop',
        'seo, marketing, search, strategy', 0, 167
    ],
];

foreach ($posts as $p) {
    $existing = $db->prepare("SELECT id FROM posts WHERE slug = ?");
    $existing->execute([$p[1]]);
    if ($existing->fetch()) {
        echo "  - Post '{$p[0]}' already exists\n";
        continue;
    }
    $stmt = $db->prepare("INSERT INTO posts (title, slug, content, excerpt, category_id, author_id, status, featured_image, tags, featured, views, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL FLOOR(RAND()*90) DAY))");
    $stmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $adminId, $p[5], $p[7], $p[8], $p[9], $p[10]]);
    echo "  + Created post: {$p[0]}\n";
}

// --- 4. Portfolio Projects ---
echo "\n[3/5] Seeding portfolio projects...\n";
$projects = [
    [
        'TechVolve Platform', 'techvolve-platform',
        'A comprehensive SaaS platform that transformed how TechVolve manages their operations.',
        '<h3>The Challenge</h3><p>TechVolve was struggling with outdated systems that hindered their growth. They needed a modern, scalable platform.</p><h3>Our Approach</h3><p>User-centered design with extensive research and usability testing ensured the platform met all stakeholder needs.</p><h3>The Solution</h3><p>A full-featured SaaS platform with real-time collaboration, advanced analytics, and seamless third-party integrations.</p>',
        $catIds['web-development-portfolio'] ?? null, 'TechVolve Inc.', '2026-03-15', 'https://techvolve.example.com',
        'React, Node.js, TypeScript, PostgreSQL, Redis, Docker', 'This platform completely transformed our operations. The ASAAS team delivered beyond our expectations.',
        'Sarah Johnson', 'CTO, TechVolve Inc.', 'published', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&h=500&fit=crop',
        1, 3
    ],
    [
        'GreenLeaf Brand Identity', 'greenleaf-brand-identity',
        'Complete brand identity redesign for an organic food company entering the premium market.',
        '<h3>The Brief</h3><p>GreenLeaf Organics needed a brand identity that communicated premium quality while staying true to their organic roots.</p><h3>Our Process</h3><p>We developed a visual identity system that combines natural elements with modern sophistication.</p><h3>The Result</h3><p>A cohesive brand system spanning packaging, digital presence, and retail materials that increased brand recognition by 40%.</p>',
        $catIds['branding-portfolio'] ?? null, 'GreenLeaf Organics', '2025-11-20', null,
        'Branding, Packaging, Visual Identity', 'The rebrand was exactly what we needed. Sales increased 35% in the first quarter after launch.',
        'Michael Green', 'Founder, GreenLeaf Organics', 'published', 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=800&h=500&fit=crop',
        0, 5
    ],
    [
        'Pulse Fitness App', 'pulse-fitness-app',
        'A cross-platform fitness tracking app with social features and personalized coaching.',
        '<h3>The Vision</h3><p>Pulse Fitness wanted to create a fitness app that combined tracking, social motivation, and AI-powered coaching.</p><h3>Development</h3><p>We built a React Native app with a Node.js backend, integrating real-time sync, push notifications, and machine learning for personalized recommendations.</p><h3>Impact</h3><p>50,000+ downloads in the first month, 4.8-star rating on both app stores.</p>',
        $catIds['mobile-portfolio'] ?? null, 'Pulse Fitness', '2026-01-10', 'https://pulsefitness.example.com',
        'React Native, Node.js, ML, Firebase', 'ASAAS built an app that our users love. The attention to UX detail made all the difference.',
        'Lisa Chen', 'CEO, Pulse Fitness', 'published', 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=800&h=500&fit=crop',
        1, 1
    ],
    [
        'FinFlow Dashboard', 'finflow-dashboard',
        'Real-time financial analytics dashboard with interactive charts and custom reporting.',
        '<h3>Requirements</h3><p>FinFlow needed a dashboard that could visualize complex financial data in real-time with intuitive interactions.</p><h3>Implementation</h3><p>Built with React and D3.js for custom visualizations, backed by a high-performance data pipeline processing millions of transactions daily.</p><h3>Results</h3><p>Reduced reporting time by 60% and improved decision-making speed across the organization.</p>',
        $catIds['web-design-portfolio'] ?? null, 'FinFlow Corp', '2025-09-01', null,
        'React, D3.js, WebSocket, Python', 'The dashboard changed how we make decisions. Real-time data at our fingertips.',
        'David Kim', 'VP Analytics, FinFlow Corp', 'published', 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&h=500&fit=crop',
        0, 7
    ],
    [
        'Bloom E-Commerce', 'bloom-ecommerce',
        'A premium e-commerce platform for a cosmetics brand with AR try-on features.',
        '<h3>The Challenge</h3><p>Bloom Cosmetics needed an e-commerce platform that matched the premium quality of their products.</p><h3>Innovation</h3><p>We integrated AR try-on technology, personalized product recommendations, and a seamless checkout experience.</p><h3>Results</h3><p>45% increase in conversion rate, 30% increase in average order value, 60% reduction in returns.</p>',
        $catIds['web-design-portfolio'] ?? null, 'Bloom Cosmetics', '2025-12-05', 'https://bloomcosmetics.example.com',
        'Next.js, Shopify, AR.js, Stripe', 'Our online sales doubled after the relaunch. The AR feature is a game changer.',
        'Emma Wilson', 'Digital Director, Bloom Cosmetics', 'published', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&h=500&fit=crop',
        0, 4
    ],
    [
        'CloudBase SaaS', 'cloudbase-saas',
        'Multi-tenant cloud infrastructure management platform with automated deployments.',
        '<h3>Objective</h3><p>CloudBase needed a platform that simplified cloud infrastructure management for non-technical teams.</p><h3>Solution</h3><p>An intuitive dashboard with drag-and-drop deployment pipelines, cost monitoring, and automated scaling.</p><h3>Outcome</h3><p>Onboarded 200+ enterprise clients in the first 6 months. 99.99% uptime maintained.</p>',
        $catIds['web-development-portfolio'] ?? null, 'CloudBase Inc.', '2026-02-28', 'https://cloudbase.example.com',
        'Vue.js, Go, Kubernetes, Terraform', 'They turned our complex infrastructure into something anyone on our team can manage.',
        'James Rodriguez', 'CTO, CloudBase Inc.', 'published', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&h=500&fit=crop',
        1, 2
    ],
];

foreach ($projects as $p) {
    $existing = $db->prepare("SELECT id FROM portfolio_projects WHERE slug = ?");
    $existing->execute([$p[1]]);
    if ($existing->fetch()) {
        echo "  - Project '{$p[0]}' already exists\n";
        continue;
    }
    $stmt = $db->prepare("INSERT INTO portfolio_projects (title, slug, description, content, category_id, client, project_date, project_url, technologies, testimonial, testimonial_author, testimonial_position, status, featured_image, featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9], $p[10], $p[11], $p[12], $p[13], $p[14], $p[15]]);
    echo "  + Created project: {$p[0]}\n";
}

// --- 5. Services ---
echo "\n[4/5] Seeding services...\n";
$services = [
    ['Web Design', 'web-design', 'We create stunning, user-centered designs that elevate your brand and drive engagement.', 'palette', '$5,000+', 1],
    ['Web Development', 'web-development', 'Full-stack development with modern technologies. From MVPs to enterprise platforms.', 'code', '$10,000+', 2],
    ['Branding & Identity', 'branding-identity', 'Complete brand systems that tell your story and connect with your audience.', 'award', '$3,000+', 3],
    ['UI/UX Design', 'ui-ux-design', 'Research-driven design that creates intuitive, delightful user experiences.', 'layers', '$7,000+', 4],
    ['Digital Marketing', 'digital-marketing', 'Data-driven marketing strategies that deliver measurable results.', 'trending-up', '$2,000+', 5],
    ['Mobile Development', 'mobile-development', 'Cross-platform mobile apps that users love. iOS and Android.', 'smartphone', '$15,000+', 6],
];
foreach ($services as $s) {
    $existing = $db->prepare("SELECT id FROM services WHERE slug = ?");
    $existing->execute([$s[1]]);
    if ($existing->fetch()) {
        echo "  - Service '{$s[0]}' already exists\n";
        continue;
    }
    $stmt = $db->prepare("INSERT INTO services (title, slug, description, icon, price, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([$s[0], $s[1], $s[2], $s[3], $s[4], $s[5]]);
    echo "  + Created service: {$s[0]}\n";
}

// --- 6. Ratings/Reviews ---
echo "\n[5/5] Seeding ratings...\n";
$ratings = [
    [2, 1, 'project', 5, 'Amazing work on the TechVolve platform. The team was professional and delivered exactly what we needed.'],
    [2, 3, 'project', 5, 'The Pulse Fitness app exceeded our expectations. Great attention to detail and UX.'],
    [1, 1, 'business', 5, 'ASAAS Studio is the best digital agency we have worked with. Highly recommend!'],
    [1, 2, 'business', 4, 'Great team, solid work on our branding project. Would work with them again.'],
    [2, 4, 'project', 5, 'The FinFlow dashboard is incredible. Real-time data visualization done right.'],
    [1, 6, 'project', 4, 'CloudBase platform was delivered on time and within budget. Professional service.'],
    [2, 5, 'project', 5, 'Bloom ecommerce redesign was a huge success. Our conversion rate jumped 45%.'],
    [1, 3, 'business', 5, 'Excellent UI/UX work. They truly understand user-centered design.'],
];
foreach ($ratings as $r) {
    $existing = $db->prepare("SELECT id FROM ratings WHERE user_id = ? AND item_id = ? AND item_type = ?");
    $existing->execute([$r[0], $r[1], $r[2]]);
    if ($existing->fetch()) {
        echo "  - Rating by user {$r[0]} for item {$r[1]} already exists\n";
        continue;
    }
    $stmt = $db->prepare("INSERT INTO ratings (user_id, item_id, item_type, rating, review, is_approved, created_at) VALUES (?, ?, ?, ?, ?, 1, DATE_SUB(NOW(), INTERVAL FLOOR(RAND()*60) DAY))");
    $stmt->execute([$r[0], $r[1], $r[2], $r[3], $r[4]]);
    echo "  + Created rating: {$r[3]} stars by user {$r[0]}\n";
}

echo "\n=== Seed complete! ===\n";
echo "Blog posts: " . $db->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn() . " published\n";
echo "Portfolio projects: " . $db->query("SELECT COUNT(*) FROM portfolio_projects WHERE status='published'")->fetchColumn() . " published\n";
echo "Services: " . $db->query("SELECT COUNT(*) FROM services")->fetchColumn() . " total\n";
echo "Ratings: " . $db->query("SELECT COUNT(*) FROM ratings")->fetchColumn() . " total\n";
echo "Categories: " . $db->query("SELECT COUNT(*) FROM categories")->fetchColumn() . " total\n";
echo "\nVisit the site:\n  Blog:     http://localhost/sas_studio/blog\n  Portfolio: http://localhost/sas_studio/portfolio\n  Home:     http://localhost/sas_studio/home\n";
