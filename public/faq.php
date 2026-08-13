<?php
$seoTitle = 'FAQ | ASAAS Studio Somalia';
$seoDesc = 'Frequently asked questions about ASAAS Studio: services, starting prices, process, and timelines. Everything you need to know before starting a project.';
$seoKeywords = 'ASAAS studio FAQ, web design questions Somalia, studio pricing Somalia, website process, ASAAS studio help';
require __DIR__ . '/../includes/header.php'; ?>

<main class="page-transition">
    <section style="padding-top:calc(var(--header-height) + 60px);background:var(--bg-light)">
        <div class="container" style="padding:60px 0">
            <div class="fade-in-up" style="max-width:800px;margin:0 auto">
                <div class="section-tag"><i data-lucide="help-circle" size="16"></i>Help</div>
                <h1 style="font-size:clamp(2rem,4vw,3rem);margin-bottom:8px">Frequently Asked Questions</h1>
                <p style="color:var(--text-secondary);font-size:18px">Everything you need to know about working with us.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container" style="max-width:800px;margin:0 auto">
            <?php $faqs = [
                [
                    'q' => 'How much does a website cost?',
                    'a' => 'Starter websites start at $99, business websites at $299, and custom web systems at $999. Every project is different, so we give you a clear quote based on your specific requirements before any work starts.'
                ],
                [
                    'q' => 'What is the typical timeline for a project?',
                    'a' => 'It depends on the scope. A simple website can take about a week, while a custom web system takes longer. We agree on a timeline before we start and keep you updated as we go.'
                ],
                [
                    'q' => 'Do you offer ongoing maintenance and support?',
                    'a' => 'Yes. Our maintenance plan starts at $49/month and covers updates, security, backups, and small changes, so your website stays secure and running smoothly.'
                ],
                [
                    'q' => 'What technologies do you use?',
                    'a' => 'We choose tools that fit the project. We build with PHP, JavaScript, and standard web technologies, and we keep things simple and maintainable.'
                ],
                [
                    'q' => 'How do we get started?',
                    'a' => 'Reach out through our <a href="' . BASE_URL . 'contact" style="color:var(--primary)">contact form</a> or book a call. Tell us what you want to build and we will reply with next steps and a quote.'
                ],
                [
                    'q' => 'Do you work with clients outside Mogadishu?',
                    'a' => 'Yes. We work remotely and can serve clients anywhere with an internet connection.'
                ],
                [
                    'q' => 'What information do you need to provide a quote?',
                    'a' => 'A short description of what you want to build is enough to start. Examples of sites you like and details about your users help us give a more accurate quote.'
                ],
                [
                    'q' => 'What is your revision policy?',
                    'a' => 'Our agreements include a defined number of revision rounds for each phase. Additional revisions beyond the agreed scope are quoted separately.'
                ],
                [
                    'q' => 'Do you offer refunds?',
                    'a' => 'Because our work is custom, we do not offer full refunds. If you are not satisfied with a phase of the project, we will work with you to make it right. Terms are outlined in each service agreement.'
                ],
            ];
            foreach ($faqs as $i => $f):
            ?>
                <div class="reveal" style="margin-bottom:12px;background:var(--bg-white);border-radius:var(--radius-md);border:1px solid var(--border)">
                    <div onclick="toggleFaq(<?= $i ?>)" style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;cursor:pointer;transition:all .3s ease">
                        <h6 style="font-weight:600;font-size:15px;margin:0;padding-right:24px"><?= $f['q'] ?></h6>
                        <svg id="faq-icon-<?= $i ?>" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;transition:transform .3s ease"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </div>
                    <div id="faq-content-<?= $i ?>" style="max-height:0;overflow:hidden;transition:max-height .4s ease,opacity .3s ease;opacity:0">
                        <div style="padding:0 24px 20px">
                            <p style="font-size:14px;color:var(--text-secondary);line-height:1.8;margin:0"><?= $f['a'] ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section" style="background:var(--bg-dark);text-align:center">
        <div class="container">
            <div class="reveal">
                <h2 style="color:white;margin-bottom:16px">Still Have Questions?</h2>
                <p style="color:var(--text-light);font-size:18px;max-width:500px;margin:0 auto 32px">We are happy to answer anything not covered here.</p>
                <a href="<?= BASE_URL ?>contact" class="btn btn-primary btn-lg">Get in Touch <i data-lucide="arrow-right" size="20"></i></a>
            </div>
        </div>
    </section>
</main>

<script>
function toggleFaq(i) {
    var contents = document.querySelectorAll('[id^="faq-content-"]');
    var icons = document.querySelectorAll('[id^="faq-icon-"]');
    contents.forEach(function(c, idx) {
        if (idx === i) {
            var isOpen = c.style.maxHeight && c.style.maxHeight !== '0px';
            if (isOpen) {
                c.style.maxHeight = '0px';
                c.style.opacity = '0';
                icons[idx].style.transform = 'rotate(0deg)';
            } else {
                c.style.maxHeight = c.scrollHeight + 40 + 'px';
                c.style.opacity = '1';
                icons[idx].style.transform = 'rotate(45deg)';
            }
        } else {
            c.style.maxHeight = '0px';
            c.style.opacity = '0';
            icons[idx].style.transform = 'rotate(0deg)';
        }
    });
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
