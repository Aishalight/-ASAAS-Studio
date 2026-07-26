<?php
$seoTitle = 'FAQ — Digital Agency Somalia Frequently Asked Questions';
$seoDesc = 'Find answers to common questions about ASAAS Studio Somalia services, pricing, process, and timelines. Everything you need to know before starting your project.';
$seoKeywords = 'digital agency Somalia FAQ, web design questions Somalia, agency pricing Somalia, website process, ASAAS studio help';
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
                    'q' => 'What is the typical timeline for a web development project?',
                    'a' => 'Timelines vary based on scope and complexity. A standard marketing website typically ships in 4-8 weeks. E-commerce platforms may take 8-16 weeks. Complex web applications can span 3-6 months or more. We provide a detailed project roadmap during the proposal phase — no surprises, no ambiguity.'
                ],
                [
                    'q' => 'How much does a website cost?',
                    'a' => 'Every project is unique, so we custom-quote based on your specific needs. Broadly speaking, polished brochure sites start around $5,000, mid-range business websites range from $10,000 to $30,000, and complex web applications or platforms start at $50,000. We believe in transparent pricing — you only pay for what moves your business forward.'
                ],
                [
                    'q' => 'Do you offer ongoing maintenance and support?',
                    'a' => 'Absolutely. We offer flexible retainer plans that cover security updates, performance monitoring, content updates, and technical support. Our team is available around the clock for critical issues. Retainers start at $500/month and scale with your needs.'
                ],
                [
                    'q' => 'What technologies do you use?',
                    'a' => 'We select the optimal technology stack for each project. Frontend work typically uses React, Next.js, or Vue.js. Backend development spans Node.js, Python (Django), and PHP (Laravel). For hosting, we work with AWS, Vercel, and Netlify. The technology serves the solution — we never force a one-size-fits-all approach.'
                ],
                [
                    'q' => 'How do we get started?',
                    'a' => 'Reach out through our <a href="' . BASE_URL . 'contact" style="color:var(--primary)">contact form</a> or <a href="' . BASE_URL . 'contact#booking" style="color:var(--primary)">schedule a discovery call</a>. We will discuss your goals, challenges, timeline, and budget — then deliver a tailored proposal within 48 hours. No pressure, just clarity and a clear path forward.'
                ],
                [
                    'q' => 'Do you work with startups or only established businesses?',
                    'a' => 'We work with businesses at every stage — from early-stage startups building their first product to established enterprises undergoing digital transformation. We tailor our engagement model to fit your stage and budget.'
                ],
                [
                    'q' => 'What information do you need to provide a quote?',
                    'a' => 'The more context, the better. A clear project brief, examples of sites or apps you admire, your target audience, key features, budget range, and any technical constraints are helpful. But even a rough idea is enough to start the conversation.'
                ],
                [
                    'q' => 'Can you work with our existing team or agency?',
                    'a' => 'Yes. We often collaborate with in-house teams, design agencies, and marketing firms. Whether you need us to lead a project end-to-end or augment your existing team with specialized expertise, we can adapt to your workflow.'
                ],
                [
                    'q' => 'What is your revision policy?',
                    'a' => 'Our service agreements include a defined number of revision rounds for each phase (design, development). Additional revisions beyond the agreed scope are billed at our standard hourly rate. We recommend aligning on all feedback in batch to keep the process efficient.'
                ],
                [
                    'q' => 'Do you offer refunds?',
                    'a' => 'We do not offer full refunds for custom work due to the nature of the services provided. If you are unsatisfied with any phase of the project, we will work with you to make it right. Detailed terms are outlined in each service agreement.'
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
