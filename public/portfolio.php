<?php
$seoTitle = 'Portfolio Somalia — Our Recent Work & Case Studies';
$seoDesc = 'Browse ASAAS Studio portfolio of web design, development, branding, and mobile projects in Somalia. See how we deliver measurable results for our clients.';
$seoKeywords = 'digital agency portfolio Somalia, web design portfolio Somalia, web development projects Somalia, branding case studies Somalia, ASAAS studio work';
require __DIR__ . '/../includes/header.php'; ?>
<style>
.rating-modal{position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);opacity:0;visibility:hidden;transition:all .3s ease}
.rating-modal.active{opacity:1;visibility:visible}
.rating-modal-content{background:var(--bg-white);border-radius:var(--radius-lg);padding:32px;max-width:460px;width:90%;box-shadow:var(--shadow-xl);transform:scale(.9);transition:transform .3s ease}
.rating-modal.active .rating-modal-content{transform:scale(1)}
.rating-modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.rating-modal-header h3{font-size:18px;font-weight:600}
.rating-modal-close{background:none;border:none;font-size:24px;cursor:pointer;color:var(--text-muted);padding:4px;line-height:1;border-radius:6px;transition:background .2s}
.rating-modal-close:hover{background:var(--bg-light)}
.rating-item-name{font-size:14px;color:var(--text-muted);margin-bottom:20px}
.rating-star-row{display:flex;gap:6px;justify-content:center;margin-bottom:20px}
.rating-star-row .star-btn{background:none;border:none;cursor:pointer;padding:4px;color:#c0c4cc;transition:transform .15s ease,color .15s ease;line-height:0}
.rating-star-row .star-btn:hover{transform:scale(1.2)}
.rating-star-row .star-btn.active,.rating-star-row .star-btn.hovered{color:#FFC107}
.rating-review-input{width:100%;min-height:80px;padding:12px;border:1px solid var(--border);border-radius:var(--radius-sm);font-family:var(--font-family);font-size:14px;resize:vertical;margin-bottom:16px;transition:border-color .2s}
.rating-review-input:focus{outline:none;border-color:var(--primary)}
.rating-submit-btn{width:100%;padding:12px;background:var(--primary);color:white;border:none;border-radius:var(--radius-sm);font-size:15px;font-weight:600;cursor:pointer;transition:background .2s,transform .15s}
.rating-submit-btn:hover{background:var(--primary-dark);transform:translateY(-1px)}
.rating-submit-btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
.rating-feedback{text-align:center;font-size:14px;margin-top:12px;min-height:20px}
.rating-feedback.success{color:#4CAF50}
.rating-feedback.error{color:#F44336}
.rate-btn{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:var(--bg-gray);border:1px solid var(--border);border-radius:20px;font-size:13px;color:var(--text-secondary);cursor:pointer;transition:all .2s;font-family:var(--font-family)}
.rate-btn:hover{background:var(--primary-alpha);border-color:var(--primary);color:var(--primary)}
.rate-btn.rated{background:#FFF8E1;border-color:#FFC107;color:#F57F17}
.portfolio-rating-row{display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:12px;border-top:1px solid var(--border)}
[data-theme="dark"] .rating-modal-content{background:#1e1e32;border:1px solid #2a2a44}
[data-theme="dark"] .rating-review-input{background:#16162a;border-color:#2a2a44;color:var(--text-white)}
[data-theme="dark"] .rate-btn{background:#2a2a44;border-color:#3a3a54;color:var(--text-light)}
[data-theme="dark"] .rate-btn:hover{background:rgba(232,99,42,0.15);border-color:var(--primary)}
[data-theme="dark"] .portfolio-rating-row{border-top-color:#2a2a44}
</style>
<main class="page-transition">
    <section style="padding-top:calc(var(--header-height) + 60px);padding-bottom:60px;background:var(--bg-light)">
        <div class="container">
            <div class="section-header fade-in-up">
                <div class="section-tag"><i data-lucide="briefcase" size="16"></i>Our Portfolio</div>
                <h1 class="section-title">Projects We're <span class="gradient-text">Proud Of</span></h1>
                <p class="section-desc">Explore our latest work across web development, design, branding, and more.</p>
            </div>
            <div class="flex-center" style="gap:8px;flex-wrap:wrap;margin-bottom:40px">
                <button class="tab active" data-filter="all">All Projects</button>
                <button class="tab" data-filter="web-design">Web Design</button>
                <button class="tab" data-filter="web-development">Web Development</button>
                <button class="tab" data-filter="branding">Branding</button>
                <button class="tab" data-filter="mobile">Mobile</button>
            </div>
        </div>
    </section>

    <section class="section" style="padding-top:40px">
        <div class="container">
            <div class="grid grid-3 stagger-children" id="portfolio-grid">
                <?php
                $db = Database::getInstance()->getConnection();
                $projects = $db->query("SELECT p.*, c.name as category_name FROM portfolio_projects p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'published' ORDER BY p.featured DESC, p.created_at DESC LIMIT 12")->fetchAll();
                $colorMap = ['#2196F3','#4CAF50','#E8632A','#9C27B0','#FF9800','#00BCD4','#E91E63','#607D8B','#3F51B5'];
                if (empty($projects)) {
                    $projects = [
                        ['id' => 1, 'title' => 'TechVolve Platform', 'category_name' => 'Web Development', 'featured_image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&h=400&fit=crop', 'client' => 'TechVolve Inc.', 'slug' => 'techvolve-platform'],
                        ['id' => 2, 'title' => 'GreenLeaf Brand Identity', 'category_name' => 'Branding', 'featured_image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=600&h=400&fit=crop', 'client' => 'GreenLeaf Organics', 'slug' => 'greenleaf-brand'],
                        ['id' => 3, 'title' => 'Pulse Fitness App', 'category_name' => 'Mobile', 'featured_image' => 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=600&h=400&fit=crop', 'client' => 'Pulse Fitness', 'slug' => 'pulse-fitness-app'],
                        ['id' => 4, 'title' => 'FinFlow Dashboard', 'category_name' => 'Web Design', 'featured_image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&h=400&fit=crop', 'client' => 'FinFlow Corp', 'slug' => 'finflow-dashboard'],
                        ['id' => 5, 'title' => 'Bloom E-Commerce', 'category_name' => 'Web Design', 'featured_image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&h=400&fit=crop', 'client' => 'Bloom Cosmetics', 'slug' => 'bloom-ecommerce'],
                        ['id' => 6, 'title' => 'CloudBase SaaS', 'category_name' => 'Web Development', 'featured_image' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=600&h=400&fit=crop', 'client' => 'CloudBase Inc.', 'slug' => 'cloudbase-saas'],
                        ['id' => 7, 'title' => 'Vibe Social Platform', 'category_name' => 'Mobile', 'featured_image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=600&h=400&fit=crop', 'client' => 'Vibe Media', 'slug' => 'vibe-social'],
                        ['id' => 8, 'title' => 'Apex Brand Redesign', 'category_name' => 'Branding', 'featured_image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&h=400&fit=crop', 'client' => 'Apex Corp', 'slug' => 'apex-brand-redesign'],
                        ['id' => 9, 'title' => 'NexGen Website', 'category_name' => 'Web Development', 'featured_image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&h=400&fit=crop', 'client' => 'NexGen Tech', 'slug' => 'nexgen-website'],
                    ];
                }
                foreach ($projects as $idx => $p):
                    $cat = $p['category_name'] ?? 'General';
                    $filter = strtolower(str_replace(' ', '-', $cat));
                    $color = $colorMap[$idx % count($colorMap)];
                    ?>
                    <div class="card hover-lift portfolio-item" data-filter="<?= $filter ?>">
                        <div style="position:relative;overflow:hidden;height:220px">
                            <img src="<?= $p['featured_image'] ?? 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600&h=400&fit=crop' ?>" alt="<?= htmlspecialchars($p['title']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease" loading="lazy">
                            <div style="position:absolute;top:12px;left:12px">
                                <span class="badge" style="background:<?= $color ?>;color:white"><?= htmlspecialchars($cat) ?></span>
                            </div>
                        </div>
                        <div style="padding:20px 24px">
                            <p style="font-size:13px;color:var(--text-muted);margin-bottom:4px"><?= htmlspecialchars($p['client'] ?? '') ?></p>
                            <h5 style="margin-bottom:12px"><?= htmlspecialchars($p['title']) ?></h5>
                            <a href="<?= BASE_URL ?>portfolio/<?= htmlspecialchars($p['slug'] ?? 'project') ?>" class="btn btn-outline btn-sm">View Case Study <i data-lucide="arrow-right" size="14"></i></a>
                            <div class="portfolio-rating-row">
                                <button class="rate-btn" data-rate-item data-item-id="<?= $p['id'] ?>" data-item-type="project" data-item-name="<?= htmlspecialchars($p['title']) ?>">
                                    <i data-lucide="star" size="14"></i> Rate
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section style="background:var(--bg-dark);padding:80px 0;text-align:center">
        <div class="container">
            <div class="reveal">
                <h2 style="color:white;margin-bottom:16px">Have a Project in Mind?</h2>
                <p style="color:var(--text-light);font-size:18px;max-width:500px;margin:0 auto 32px">Let's create something amazing together.</p>
                <a href="<?= BASE_URL ?>contact" class="btn btn-primary btn-lg">Start a Project <i data-lucide="arrow-right" size="20"></i></a>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('[data-filter]');
    const items = document.querySelectorAll('.portfolio-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.dataset.filter;
            items.forEach(item => {
                if (filter === 'all' || item.dataset.filter === filter) {
                    item.style.display = 'block';
                    item.style.opacity = '0';
                    item.style.animation = 'fadeInUp 0.5s ease forwards';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

<div class="rating-modal" id="ratingModal">
    <div class="rating-modal-content">
        <div class="rating-modal-header">
            <h3>Rate This Project</h3>
            <button class="rating-modal-close">&times;</button>
        </div>
        <p class="rating-item-name">Loading...</p>
        <input type="hidden" id="rateItemId" value="">
        <input type="hidden" id="rateItemType" value="">
        <input type="hidden" id="ratingValue" value="0">
        <div id="guestFields" style="display:none;margin-bottom:16px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div class="form-group" style="margin:0"><label class="form-label">Your Name *</label><input type="text" id="guestName" class="form-input" placeholder="John Doe"></div>
                <div class="form-group" style="margin:0"><label class="form-label">Email (optional)</label><input type="email" id="guestEmail" class="form-input" placeholder="john@example.com"></div>
            </div>
        </div>
        <div class="rating-star-row">
            <button class="star-btn" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg></button>
            <button class="star-btn" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg></button>
            <button class="star-btn" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg></button>
            <button class="star-btn" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg></button>
            <button class="star-btn" type="button"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg></button>
        </div>
        <textarea class="rating-review-input" placeholder="Tell us what you think (optional)..."></textarea>
        <button class="rating-submit-btn" type="button">Submit Rating</button>
        <div class="rating-feedback"></div>
    </div>
</div>
<script>
(function(){
var isLoggedIn = <?= json_encode(isLoggedIn()) ?>;
var m=document.querySelector('.rating-modal');if(!m)return;
var row=m.querySelector('.rating-star-row'),stars=row.querySelectorAll('.star-btn'),hInput=document.getElementById('ratingValue');
stars.forEach(function(s,i){s.addEventListener('mouseenter',function(){stars.forEach(function(x,j){x.classList.toggle('hovered',j<=i)})});
s.addEventListener('mouseleave',function(){stars.forEach(function(x){x.classList.remove('hovered')})});
s.addEventListener('click',function(){var v=i+1;stars.forEach(function(x,j){x.classList.toggle('active',j<v)});hInput.value=v})});
document.querySelector('.rating-submit-btn').addEventListener('click',function(){var btn=this,fb=m.querySelector('.rating-feedback'),iid=document.getElementById('rateItemId').value,itype=document.getElementById('rateItemType').value,rtg=parseInt(hInput.value),rev=m.querySelector('.rating-review-input').value.trim();if(!rtg||rtg<1){fb.textContent='Please select a rating';fb.className='rating-feedback error';return}
var fd=new FormData();fd.append('item_id',iid);fd.append('item_type',itype);fd.append('rating',rtg);fd.append('review',rev);
if(!isLoggedIn){var gn=document.getElementById('guestName').value.trim(),ge=document.getElementById('guestEmail').value.trim();if(!gn){fb.textContent='Please enter your name';fb.className='rating-feedback error';return}fd.append('guest_name',gn);fd.append('guest_email',ge)}
btn.disabled=true;btn.textContent='Submitting...';
fetch(BASE_URL+'api/index.php?action=submit_rating',{method:'POST',body:fd}).then(function(r){return r.json()}).then(function(d){if(d.success){fb.textContent='Thank you! Your rating will appear after review.';fb.className='rating-feedback success';btn.textContent='Done';setTimeout(function(){m.classList.remove('active');document.body.style.overflow=''},2000)}else{fb.textContent=d.error||'Failed to submit rating';fb.className='rating-feedback error';btn.disabled=false;btn.textContent='Try Again'}}).catch(function(){fb.textContent='Network error. Please try again.';fb.className='rating-feedback error';btn.disabled=false;btn.textContent='Submit Rating'})});
document.addEventListener('click',function(e){var b=e.target.closest('[data-rate-item]');if(b){var iid=b.getAttribute('data-item-id'),itype=b.getAttribute('data-item-type'),iname=b.getAttribute('data-item-name');m.querySelector('.rating-item-name').textContent=iname;document.getElementById('rateItemId').value=iid;document.getElementById('rateItemType').value=itype;hInput.value=0;m.querySelector('.rating-review-input').value='';m.querySelector('.rating-feedback').textContent='';m.querySelector('.rating-submit-btn').disabled=false;m.querySelector('.rating-submit-btn').textContent='Submit Rating';stars.forEach(function(s){s.classList.remove('active','hovered')});var gf=document.getElementById('guestFields');if(gf)gf.style.display=isLoggedIn?'none':'block';m.classList.add('active');document.body.style.overflow='hidden'}
var cb=e.target.closest('.rating-modal-close,[data-rating-close]');if(cb){m.classList.remove('active');document.body.style.overflow=''}
if(e.target===m){m.classList.remove('active');document.body.style.overflow=''}})
})();
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
