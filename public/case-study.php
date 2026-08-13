<?php
$slug = $_GET['slug'] ?? '';
$db = Database::getInstance()->getConnection();

if ($slug) {
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM portfolio_projects p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ? AND p.status = 'published'");
    $stmt->execute([$slug]);
    $project = $stmt->fetch();
}

if (empty($project)) {
    $seoTitle = 'Project Not Found';
    require __DIR__ . '/../includes/header.php';
    ?>
    <main class="page-transition">
        <section style="padding-top:calc(var(--header-height) + 120px);padding-bottom:80px;text-align:center">
            <div class="container">
                <h1 style="font-size:clamp(2rem,4vw,3rem);margin-bottom:16px">Project Not Found</h1>
                <p style="color:var(--text-secondary);margin-bottom:32px">The project you are looking for does not exist or has been removed.</p>
                <a href="<?= BASE_URL ?>portfolio" class="btn btn-primary"><i data-lucide="arrow-left" size="16"></i> Back to Portfolio</a>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/../includes/footer.php'; exit;
}

$seoTitle = htmlspecialchars($project['title']) . ' | ASAAS Studio Portfolio';
$seoDesc = htmlspecialchars($project['description'] ?? '');
$techs = array_filter(array_map('trim', explode(',', $project['technologies'] ?? '')));
$galleryImages = json_decode($project['gallery_images'] ?? '[]', true) ?: [];
require __DIR__ . '/../includes/header.php';
?>
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
.rating-section{max-width:800px;margin:0 auto;padding:40px 0;border-top:1px solid var(--border)}
.rating-section h3{margin-bottom:8px;font-size:20px}
.rating-section .rating-subtitle{color:var(--text-muted);font-size:14px;margin-bottom:24px}
.rating-section .rating-prompt{display:flex;flex-direction:column;align-items:center;gap:16px;padding:32px;background:var(--bg-light);border-radius:var(--radius-md);text-align:center}
.rating-section .rating-prompt p{color:var(--text-secondary);font-size:15px}
[data-theme="dark"] .rating-modal-content{background:#1e1e32;border:1px solid #2a2a44}
[data-theme="dark"] .rating-review-input{background:#16162a;border-color:#2a2a44;color:var(--text-white)}
[data-theme="dark"] .rating-section .rating-prompt{background:#16162a}
</style>
<main class="page-transition">
    <section style="padding-top:calc(var(--header-height) + 60px);background:var(--bg-dark);position:relative;overflow:hidden">
        <div style="position:absolute;inset:0;opacity:0.08">
            <div style="position:absolute;width:500px;height:500px;border-radius:50%;background:var(--primary);top:-200px;right:-100px"></div>
            <div style="position:absolute;width:300px;height:300px;border-radius:50%;background:var(--primary);bottom:-100px;left:-50px"></div>
        </div>
        <div class="container" style="position:relative;z-index:1;padding:80px 0 60px">
            <div class="fade-in-up" style="max-width:800px">
                <?php if ($project['category_name']): ?>
                <span class="badge" style="background:#2196F3;color:white;margin-bottom:16px"><?= htmlspecialchars($project['category_name']) ?></span>
                <?php endif; ?>
                <?php $pTypeLabel = ['client' => 'Client', 'internal' => 'Internal', 'concept' => 'Concept', 'student' => 'Student'][$project['project_type'] ?? ''] ?? ''; ?>
                <?php if ($pTypeLabel): ?>
                <span class="badge" style="background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.3);margin-bottom:16px"><?= $pTypeLabel ?> Project</span>
                <?php endif; ?>
                <h1 style="color:white;font-size:clamp(2rem,4vw,3.5rem);margin-bottom:16px"><?= htmlspecialchars($project['title']) ?></h1>
                <?php if ($project['description']): ?>
                <p style="color:var(--text-light);font-size:18px;margin-bottom:24px"><?= htmlspecialchars($project['description']) ?></p>
                <?php endif; ?>
                <div style="display:flex;gap:32px;flex-wrap:wrap">
                    <?php if ($project['client']): ?><div><span style="color:var(--text-muted);font-size:13px">Client</span><p style="color:white;font-weight:600"><?= htmlspecialchars($project['client']) ?></p></div><?php endif; ?>
                    <?php if ($project['project_date']): ?><div><span style="color:var(--text-muted);font-size:13px">Date</span><p style="color:white;font-weight:600"><?= date('M Y', strtotime($project['project_date'])) ?></p></div><?php endif; ?>
                    <?php if (!empty($techs)): ?><div><span style="color:var(--text-muted);font-size:13px">Technologies</span><p style="color:white;font-weight:600"><?= htmlspecialchars(implode(', ', array_slice($techs, 0, 3))) ?></p></div><?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div style="max-width:800px;margin:0 auto">
                <?php if ($project['featured_image']): ?>
                <div class="reveal" style="margin-bottom:40px">
                    <img src="<?= BASE_URL . $project['featured_image'] ?>" alt="<?= htmlspecialchars($project['title']) ?>" style="width:100%;border-radius:var(--radius-xl);box-shadow:var(--shadow-lg)">
                </div>
                <?php endif; ?>

                <?php if ($project['content']): ?>
                <div class="reveal" style="margin-bottom:40px;font-size:16px;line-height:1.9;color:var(--text-secondary)">
                    <?= $project['content'] ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($galleryImages)): ?>
                <div class="reveal" style="margin-bottom:40px">
                    <h3 style="margin-bottom:16px">Gallery</h3>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
                        <?php foreach ($galleryImages as $img): ?>
                        <img src="<?= BASE_URL . $img ?>" alt="Gallery" style="width:100%;height:160px;object-fit:cover;border-radius:var(--radius-md)">
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($techs)): ?>
                <div class="reveal" style="margin-bottom:40px">
                    <h3 style="margin-bottom:16px">Technologies Used</h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap">
                        <?php foreach ($techs as $t): ?>
                            <span class="badge badge-primary"><?= htmlspecialchars($t) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($project['testimonial']): ?>
                <div class="reveal" style="background:var(--bg-light);padding:32px;border-radius:var(--radius-lg);margin-bottom:40px;border-left:4px solid var(--primary)">
                    <p style="font-size:16px;font-style:italic;color:var(--text-secondary);line-height:1.7;margin-bottom:16px">"<?= htmlspecialchars($project['testimonial']) ?>"</p>
                    <div>
                        <strong><?= htmlspecialchars($project['testimonial_author'] ?? '') ?></strong>
                        <?php if ($project['testimonial_position']): ?>
                        <span style="color:var(--text-muted);font-size:13px;margin-left:8px"><?= htmlspecialchars($project['testimonial_position']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="reveal" style="display:flex;gap:16px;flex-wrap:wrap">
                    <a href="<?= BASE_URL ?>portfolio" class="btn btn-secondary"><i data-lucide="arrow-left" size="16"></i> Back to Portfolio</a>
                    <?php if ($project['project_url']): ?>
                    <a href="<?= htmlspecialchars($project['project_url']) ?>" target="_blank" class="btn btn-primary">Visit Project <i data-lucide="external-link" size="16"></i></a>
                    <?php endif; ?>
                    <?php if ($project['github_url']): ?>
                    <a href="<?= htmlspecialchars($project['github_url']) ?>" target="_blank" class="btn btn-secondary">GitHub <i data-lucide="github" size="16"></i></a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>contact" class="btn btn-primary">Start Similar Project <i data-lucide="arrow-right" size="16"></i></a>
                </div>

                <div class="rating-section reveal">
                    <h3>Rate This Project</h3>
                    <p class="rating-subtitle">We value your feedback! Let us know what you think.</p>
                    <div class="rating-prompt">
                        <p>How would you rate the <?= htmlspecialchars($project['title']) ?>?</p>
                        <button class="btn btn-primary" data-rate-item data-item-id="<?= $project['id'] ?>" data-item-type="project" data-item-name="<?= htmlspecialchars($project['title']) ?>">
                            <i data-lucide="star" size="16"></i> Rate Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

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
