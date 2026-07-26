// ============================================================
// ASAAS STUDIO - Main JavaScript
// Core UI Interactions
// ============================================================

(function() {
    'use strict';

    function init() {
        initMobileNav();
        initAccordions();
        initTabs();
        initModals();
        initDropdowns();
        initTestimonialSlider();
        initFormValidation();
        initAlerts();
        initToastNotifications();
        initLucideIcons();
        initBackToTop();
        initFileUpload();
        initSearch();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function initLucideIcons() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // ============================================================
    // TOAST NOTIFICATION
    // ============================================================
    function showToast(msg, type, duration) {
        type = type || 'info';
        duration = duration || 4000;
        var t = document.createElement('div');
        t.textContent = msg;
        t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;padding:14px 24px;border-radius:10px;font-size:14px;font-weight:500;color:white;background:#1e1e32;box-shadow:0 8px 32px rgba(0,0,0,0.3);transform:translateY(20px);opacity:0;transition:all 0.3s ease;max-width:380px;line-height:1.5';
        if (type === 'info') t.style.background = '#1e1e32';
        else if (type === 'success') t.style.background = '#10b981';
        else if (type === 'error') t.style.background = '#ef4444';
        document.body.appendChild(t);
        requestAnimationFrame(function() {
            t.style.transform = 'translateY(0)';
            t.style.opacity = '1';
        });
        setTimeout(function() {
            t.style.transform = 'translateY(20px)';
            t.style.opacity = '0';
            setTimeout(function() { t.remove(); }, 300);
        }, duration);
    }

    // ============================================================
    // AUTO-REFRESH — polls for new notifications/messages
    // ============================================================
    (function startPolling() {
        var lastNotifCount = -1;
        var lastMsgCount = -1;

        function poll() {
            if (typeof BASE_URL === 'undefined') return;
            var xhr = new XMLHttpRequest();
            xhr.open('GET', BASE_URL + 'api?action=poll&_=' + Date.now(), true);
            xhr.onload = function() {
                if (xhr.status !== 200) return;
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (!r.success) return;

                    // Update notification badge
                    var badge = document.querySelector('[data-notif-badge], .user-topbar-badge');
                    if (badge && typeof r.data.notifications !== 'undefined') {
                        var n = parseInt(r.data.notifications) || 0;
                        if (n !== lastNotifCount) {
                            lastNotifCount = n;
                            badge.textContent = n;
                            badge.style.display = n > 0 ? '' : 'none';
                            if (n > 0 && lastNotifCount >= 0) {
                                showToast('You have ' + n + ' new notification' + (n > 1 ? 's' : ''), 'info', 4000);
                            }
                        }
                    }

                    // Update message badge (user dashboard)
                    var msgBadge = document.querySelector('[data-msg-badge]');
                    if (msgBadge && typeof r.data.messages !== 'undefined') {
                        var m = parseInt(r.data.messages) || 0;
                        msgBadge.textContent = m;
                        msgBadge.style.display = m > 0 ? '' : 'none';
                    }

                    // Update contact badge (admin)
                    var contactBadge = document.querySelector('[data-contact-badge]');
                    if (contactBadge && typeof r.data.contacts !== 'undefined') {
                        var c = parseInt(r.data.contacts) || 0;
                        contactBadge.textContent = c;
                        contactBadge.style.display = c > 0 ? '' : 'none';
                    }
                } catch(e) {}
            };
            xhr.send();
        }

        // Initial poll after 3s, then every 8s
        setTimeout(poll, 3000);
        setInterval(poll, 8000);
    })();

    // ============================================================
    // MOBILE NAV
    // ============================================================
    function initMobileNav() {
        var toggle = document.querySelector('.mobile-toggle');
        var nav = document.querySelector('.nav');
        if (!toggle || !nav) return;

        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            nav.classList.toggle('active');
            toggle.setAttribute('aria-expanded', nav.classList.contains('active'));
            // Swap menu/x icon
            var icon = toggle.querySelector('i');
            if (icon) {
                icon.setAttribute('data-lucide', nav.classList.contains('active') ? 'x' : 'menu');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!toggle.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                var icon = toggle.querySelector('i');
                if (icon) {
                    icon.setAttribute('data-lucide', 'menu');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            }
        });

        // Close on link click
        nav.querySelectorAll('.nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                nav.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
                var icon = toggle.querySelector('i');
                if (icon) {
                    icon.setAttribute('data-lucide', 'menu');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            });
        });
    }

    // ============================================================
    // ACCORDIONS
    // ============================================================
    function initAccordions() {
        document.querySelectorAll('.accordion-header').forEach(function(header) {
            header.addEventListener('click', function() {
                var content = this.nextElementSibling;
                var isActive = this.classList.contains('active');
                var accordion = this.closest('.accordion');
                if (accordion) {
                    accordion.querySelectorAll('.accordion-header').forEach(function(h) {
                        h.classList.remove('active');
                        if (h.nextElementSibling) h.nextElementSibling.classList.remove('active');
                    });
                }
                if (!isActive) {
                    this.classList.add('active');
                    if (content) content.classList.add('active');
                }
            });
        });
        document.querySelectorAll('.accordion').forEach(function(acc) {
            var first = acc.querySelector('.accordion-header');
            if (first && !acc.querySelector('.accordion-header.active')) {
                first.classList.add('active');
                if (first.nextElementSibling) first.nextElementSibling.classList.add('active');
            }
        });
    }

    // ============================================================
    // TABS
    // ============================================================
    function initTabs() {
        document.querySelectorAll('.tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                var container = this.closest('.tabs');
                var target = this.dataset.tab;
                if (!container) return;
                container.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
                this.classList.add('active');
                var contentContainer = this.closest('.tab-container');
                if (contentContainer) {
                    contentContainer.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
                    var tc = contentContainer.querySelector('[data-tab-content="' + target + '"]');
                    if (tc) tc.classList.add('active');
                }
            });
        });
    }

    // ============================================================
    // MODALS
    // ============================================================
    function initModals() {
        document.querySelectorAll('[data-modal]').forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                var modal = document.getElementById(this.dataset.modal);
                if (modal) openModal(modal);
            });
        });
        document.querySelectorAll('.modal-close, [data-modal-close]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var modal = this.closest('.modal-overlay');
                if (modal) closeModal(modal);
            });
        });
        document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this);
            });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(closeModal);
            }
        });
    }

    function openModal(modal) {
        var el = typeof modal === 'string' ? document.getElementById(modal) : modal;
        if (el) { el.classList.add('active'); document.body.style.overflow = 'hidden'; }
    }

    function closeModal(modal) {
        var el = typeof modal === 'string' ? document.getElementById(modal) : modal;
        if (el) { el.classList.remove('active'); document.body.style.overflow = ''; }
    }

    window.openModal = openModal;
    window.closeModal = closeModal;

    // ============================================================
    // DROPDOWNS
    // ============================================================
    function initDropdowns() {
        document.querySelectorAll('[data-dropdown]').forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                var target = this.dataset.dropdown;
                var dropdown = document.getElementById(target) || this.nextElementSibling;
                if (!dropdown) return;
                var isActive = dropdown.classList.contains('active');
                document.querySelectorAll('.dropdown.active, .topbar-dropdown.active').forEach(function(d) {
                    if (d !== dropdown) d.classList.remove('active');
                });
                dropdown.classList.toggle('active');
            });
        });
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown.active, .topbar-dropdown.active').forEach(function(d) {
                d.classList.remove('active');
            });
        });
    }

    // ============================================================
    // TESTIMONIAL SLIDER
    // ============================================================
    function initTestimonialSlider() {
        document.querySelectorAll('.testimonial-slider').forEach(function(slider) {
            var track = slider.querySelector('.slider-track');
            var slides = slider.querySelectorAll('.testimonial-card');
            var prev = slider.querySelector('.slider-prev');
            var next = slider.querySelector('.slider-next');
            var dots = slider.querySelector('.slider-dots');
            if (!track || !slides.length) return;
            var current = 0;
            var total = slides.length;
            if (dots) {
                slides.forEach(function(_, i) {
                    var dot = document.createElement('button');
                    dot.className = 'slider-dot' + (i === 0 ? ' active' : '');
                    dot.addEventListener('click', function() { goTo(i); });
                    dots.appendChild(dot);
                });
            }
            function goTo(index) {
                current = index;
                track.style.transform = 'translateX(-' + (current * 100) + '%)';
                if (dots) {
                    dots.querySelectorAll('.slider-dot').forEach(function(d, i) {
                        d.classList.toggle('active', i === current);
                    });
                }
            }
            if (prev) prev.addEventListener('click', function() { goTo(current > 0 ? current - 1 : total - 1); });
            if (next) next.addEventListener('click', function() { goTo(current < total - 1 ? current + 1 : 0); });
            var interval = setInterval(function() { goTo(current < total - 1 ? current + 1 : 0); }, 5000);
            slider.addEventListener('mouseenter', function() { clearInterval(interval); });
            slider.addEventListener('mouseleave', function() {
                interval = setInterval(function() { goTo(current < total - 1 ? current + 1 : 0); }, 5000);
            });
        });
    }

    // ============================================================
    // FORM VALIDATION
    // ============================================================
    function initFormValidation() {
        document.querySelectorAll('form[data-validate]').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                var valid = true;
                this.querySelectorAll('[required]').forEach(function(field) {
                    var group = field.closest('.form-group');
                    var error = group ? group.querySelector('.form-error') : null;
                    if (!field.value.trim()) {
                        if (error) error.textContent = 'This field is required';
                        field.style.borderColor = '#F44336';
                        valid = false;
                    } else {
                        if (error) error.textContent = '';
                        field.style.borderColor = '';
                        if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                            if (error) error.textContent = 'Please enter a valid email';
                            field.style.borderColor = '#F44336';
                            valid = false;
                        }
                        if (field.dataset.validate === 'password' && field.value.length < 8) {
                            if (error) error.textContent = 'Password must be at least 8 characters';
                            field.style.borderColor = '#F44336';
                            valid = false;
                        }
                    }
                });
                if (!valid) e.preventDefault();
            });
            this.querySelectorAll('[required]').forEach(function(field) {
                field.addEventListener('input', function() {
                    var group = this.closest('.form-group');
                    var error = group ? group.querySelector('.form-error') : null;
                    if (error) error.textContent = '';
                    this.style.borderColor = '';
                });
            });
        });
    }

    // ============================================================
    // ALERTS DISMISS
    // ============================================================
    function initAlerts() {
        document.querySelectorAll('.alert-dismiss').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var alert = this.closest('.alert');
                if (alert) alert.remove();
            });
        });
        document.querySelectorAll('.flash-message').forEach(function(msg) {
            setTimeout(function() {
                msg.style.opacity = '0';
                msg.style.transform = 'translateX(100%)';
                msg.style.transition = 'all 0.3s ease';
                setTimeout(function() { if (msg.parentNode) msg.remove(); }, 300);
            }, 4000);
        });
    }

    // ============================================================
    // TOAST NOTIFICATIONS
    // ============================================================
    function initToastNotifications() {}

    window.showToast = function(message, type, duration) {
        type = type || 'info';
        duration = duration || 4000;
        var container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        var iconName = type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : type === 'warning' ? 'alert-triangle' : 'info';
        toast.innerHTML = '<i data-lucide="' + iconName + '" size="20"></i><span style="flex:1">' + message + '</span><button onclick="this.closest(\'.toast\').remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:var(--text-muted)">&times;</button>';
        container.appendChild(toast);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(function() { if (toast.parentNode) toast.remove(); }, 300);
        }, duration);
    };

    // ============================================================
    // FILE UPLOAD
    // ============================================================
    function initFileUpload() {
        document.querySelectorAll('.upload-zone').forEach(function(zone) {
            var input = zone.querySelector('input[type="file"]');
            if (!input) return;
            zone.addEventListener('click', function() { input.click(); });
            zone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('dragover'); });
            zone.addEventListener('dragleave', function() { this.classList.remove('dragover'); });
            zone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                }
            });
            input.addEventListener('change', function() {
                if (this.files.length) {
                    var file = this.files[0];
                    var preview = zone.querySelector('.upload-preview');
                    if (preview) {
                        if (file.type.startsWith('image/')) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-height:200px;border-radius:8px">';
                            };
                            reader.readAsDataURL(file);
                        } else {
                            preview.textContent = 'Selected: ' + file.name;
                        }
                    }
                }
            });
        });
    }

    // ============================================================
    // SEARCH
    // ============================================================
    function initSearch() {
        var input = document.querySelector('.topbar-search input');
        if (!input) return;
        var timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                var q = input.value.trim();
                if (q.length > 2) {
                    document.dispatchEvent(new CustomEvent('globalsearch', { detail: { query: q } }));
                }
            }, 300);
        });
    }

    // ============================================================
    // BACK TO TOP
    // ============================================================
    function initBackToTop() {
        var btn = document.querySelector('.back-to-top');
        if (!btn) return;
        window.addEventListener('scroll', function() {
            btn.classList.toggle('visible', window.pageYOffset > 500);
        });
        btn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

})();
