<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a href="<?= BASE_URL ?>home" class="footer-brand">
                    <img src="<?= BASE_URL ?>uploads/logo2_blackbackground.png" alt="ASAAS" style="height:56px;width:auto">
                </a>
                <p class="footer-desc">A small digital studio based in Mogadishu, building simple, useful websites and web systems.</p>
                <?php
                $socialTwitter = getSetting('social_twitter', '#');
                $socialInstagram = getSetting('social_instagram', '#');
                $socialLinkedin = getSetting('social_linkedin', '#');
                $socialGithub = getSetting('social_github', '#');
                ?>
                <div class="footer-social">
                    <a href="<?= $socialTwitter ?>" aria-label="Twitter" target="_blank" rel="noopener"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg></a>
                    <a href="<?= $socialInstagram ?>" aria-label="Instagram" target="_blank" rel="noopener"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg></a>
                    <a href="<?= $socialLinkedin ?>" aria-label="LinkedIn" target="_blank" rel="noopener"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg></a>
                    <a href="<?= $socialGithub ?>" aria-label="GitHub" target="_blank" rel="noopener"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg></a>
                </div>
            </div>
            <div>
                <h4 class="footer-title">Services</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>services">Websites</a></li>
                    <li><a href="<?= BASE_URL ?>services">Custom Web Systems</a></li>
                    <li><a href="<?= BASE_URL ?>services">UI/UX Design</a></li>
                    <li><a href="<?= BASE_URL ?>services">Website Maintenance</a></li>
                    <li><a href="<?= BASE_URL ?>services">SEO & Digital Presence</a></li>
                    <li><a href="<?= BASE_URL ?>services">Social Media Management</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-title">Company</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>about">About Us</a></li>
                    <li><a href="<?= BASE_URL ?>portfolio">Portfolio</a></li>
                    <li><a href="<?= BASE_URL ?>blog">Blog</a></li>
                    <li><a href="<?= BASE_URL ?>contact">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-title">Resources</h4>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>blog">Insights</a></li>
                    <li><a href="<?= BASE_URL ?>faq">FAQ</a></li>
                    <li><a href="<?= BASE_URL ?>privacy-policy">Privacy Policy</a></li>
                    <li><a href="<?= BASE_URL ?>terms-of-service">Terms of Service</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-title">Contact</h4>
                <ul class="footer-links">
                    <li><a href="mailto:info@asaas-studio.tech">info@asaas-studio.tech</a></li>
                    <li><a href="https://asaas-studio.tech" target="_blank" rel="noopener">asaas-studio.tech</a></li>
                    <li>Mogadishu, Somalia</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> ASAAS. All rights reserved.</p>
            <p>Made with care by the ASAAS team in Mogadishu, Somalia</p>
        </div>
    </div>
</footer>

<?php
$whatsappNumber = getSetting('social_whatsapp', '');
if ($whatsappNumber):
    $waClean = preg_replace('/[^0-9]/', '', $whatsappNumber);
    $waHref = 'https://wa.me/' . $waClean . '?text=' . urlencode('Hi ASAAS Studio! I\'d like to discuss a project.');
?>
<a href="<?= $waHref ?>" target="_blank" rel="noopener" aria-label="Chat on WhatsApp" class="whatsapp-float" id="wa-float">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="white">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
    <span class="wa-bubble">
        <span class="wa-bubble-text">Need a project? Let's talk! 👋</span>
        <span class="wa-bubble-close" onclick="event.preventDefault();event.stopPropagation();document.querySelector('.wa-bubble').style.display='none'">&times;</span>
    </span>
</a>
<style>
.whatsapp-float {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #25D366;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(37, 211, 102, 0.4);
    text-decoration: none;
    animation: wa-bounce 2s ease-in-out infinite;
}
.whatsapp-float:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 24px rgba(37, 211, 102, 0.5);
    animation-play-state: paused;
}
.whatsapp-float::before {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: rgba(37, 211, 102, 0.2);
    animation: wa-pulse 2s ease-in-out infinite;
}
.wa-bubble {
    position: absolute;
    bottom: 70px;
    right: 0;
    background: #fff;
    color: #1a1a2e;
    padding: 10px 16px;
    border-radius: 12px 12px 0 12px;
    white-space: nowrap;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    display: flex;
    align-items: center;
    gap: 8px;
    opacity: 0;
    transform: translateY(10px) scale(0.9);
    animation: wa-bubble-in 0.4s 1s ease forwards, wa-bubble-out 0.3s 8s ease forwards;
}
.wa-bubble::after {
    content: '';
    position: absolute;
    bottom: -8px;
    right: 12px;
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 8px solid #fff;
}
.wa-bubble-close {
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    color: #999;
    margin-left: 4px;
}
.wa-bubble-close:hover { color: #333; }
@keyframes wa-bounce {
    0%, 100% { transform: translateY(0); }
    15% { transform: translateY(-8px); }
    30% { transform: translateY(0); }
    45% { transform: translateY(-4px); }
    60% { transform: translateY(0); }
}
@keyframes wa-pulse {
    0%, 100% { transform: scale(1); opacity: 0.6; }
    50% { transform: scale(1.15); opacity: 0; }
}
@keyframes wa-bubble-in {
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes wa-bubble-out {
    to { opacity: 0; transform: translateY(10px) scale(0.9); }
}
@media (max-width: 480px) {
    .whatsapp-float { bottom: 16px; right: 16px; width: 52px; height: 52px; }
    .whatsapp-float svg { width: 24px; height: 24px; }
    .wa-bubble { font-size: 13px; padding: 8px 12px; }
}
</style>
<?php endif; ?>

<script src="<?= BASE_URL ?>assets/js/animations.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
