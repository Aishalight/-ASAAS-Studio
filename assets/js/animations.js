// ============================================================
// ASAAS STUDIO - Animation System
// Pure JS animations - lightweight, smooth, professional
// ============================================================

const ASAAS = {
    // Initialize all animations
    init: function() {
        this.initScrollReveal();
        this.initCounters();
        this.initParallax();
        this.initTyping();
        this.initPageLoad();
        this.initSmoothScroll();
        this.initNavScroll();
    },

    // ============================================================
    // SCROLL REVEAL - Reveal elements on scroll
    // ============================================================
    initScrollReveal: function() {
        const reveals = document.querySelectorAll('.reveal');
        if (!reveals.length) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        reveals.forEach(el => observer.observe(el));
    },

    // ============================================================
    // COUNTERS - Animated number counting
    // ============================================================
    initCounters: function() {
        const counters = document.querySelectorAll('.counter');
        if (!counters.length) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.dataset.target) || 0;
                    const duration = parseInt(entry.target.dataset.duration) || 2000;
                    const suffix = entry.target.dataset.suffix || '';
                    this.animateCounter(entry.target, target, duration, suffix);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(el => observer.observe(el));
    },

    animateCounter: function(element, target, duration, suffix) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;

        const update = () => {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString() + suffix;
                return;
            }
            element.textContent = Math.floor(current).toLocaleString() + suffix;
            requestAnimationFrame(update);
        };
        update();
    },

    // ============================================================
    // PARALLAX - Subtle parallax effect
    // ============================================================
    initParallax: function() {
        const parallaxElements = document.querySelectorAll('.parallax');
        if (!parallaxElements.length) return;

        window.addEventListener('scroll', () => {
            const scrollY = window.pageYOffset;
            parallaxElements.forEach(el => {
                const speed = el.dataset.speed || 0.5;
                el.style.backgroundPositionY = scrollY * speed + 'px';
            });
        }, { passive: true });
    },

    // ============================================================
    // TYPING EFFECT
    // ============================================================
    initTyping: function() {
        const elements = document.querySelectorAll('.typing-text');
        elements.forEach(el => {
            const text = el.textContent;
            el.textContent = '';
            el.style.visibility = 'visible';
            let index = 0;
            const type = () => {
                if (index < text.length) {
                    el.textContent += text.charAt(index);
                    index++;
                    setTimeout(type, 50 + Math.random() * 50);
                }
            };
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    setTimeout(type, 500);
                    observer.unobserve(el);
                }
            });
            observer.observe(el);
        });
    },

    // ============================================================
    // PAGE LOAD ANIMATION
    // ============================================================
    initPageLoad: function() {
        const loadingOverlay = document.querySelector('.loading-overlay');
        if (loadingOverlay) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    loadingOverlay.classList.add('hidden');
                }, 300);
            });
            setTimeout(() => {
                loadingOverlay.classList.add('hidden');
            }, 1500);
        }

        // Animate elements with animation classes on load
        document.querySelectorAll('.fade-in, .fade-in-up, .fade-in-down, .fade-in-left, .fade-in-right, .scale-in').forEach(el => {
            el.style.opacity = '0';
        });

        setTimeout(() => {
            document.querySelectorAll('.fade-in, .fade-in-up, .fade-in-down, .fade-in-left, .fade-in-right, .scale-in').forEach(el => {
                el.style.opacity = '';
            });
        }, 100);
    },

    // ============================================================
    // SMOOTH SCROLL
    // ============================================================
    initSmoothScroll: function() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    const offset = 100;
                    const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({ top, behavior: 'smooth' });
                }
            });
        });
    },

    // ============================================================
    // NAV SCROLL EFFECT
    // ============================================================
    initNavScroll: function() {
        const header = document.querySelector('.header');
        if (!header) return;

        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            lastScroll = currentScroll;
        }, { passive: true });
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => ASAAS.init());
