(function() {
    'use strict';

    var Dashboard = {
        init: function() {
            this.initMessages();
            this.initNotifications();
            this.initCounters();
            this.initScrollReveal();
            this.initSkeletonLoaders();
        },

        initMessages: function() {
            // Message thread switching is handled inline in messages.php
        },

        // ============================================================
        // NOTIFICATION POLLING
        // ============================================================
        initNotifications: function() {
            // Notification polling is handled by main.js (polls every 8s)
        },

        // ============================================================
        // ANIMATED COUNTERS - animates stat numbers on load
        // ============================================================
        initCounters: function() {
            var values = document.querySelectorAll('.dash-stat-value');
            if (!values.length) return;

            values.forEach(function(el) {
                var text = el.textContent.trim();
                var target = parseInt(text.replace(/[^0-9]/g, '')) || 0;
                if (target === 0) return;

                var current = 0;
                var step = Math.max(1, Math.floor(target / 30));
                var suffix = text.replace(/[0-9]/g, '');

                el.textContent = '0' + suffix;
                el.classList.add('counter-pop');

                var interval = setInterval(function() {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(interval);
                        el.classList.add('counted');
                    }
                    el.textContent = current.toLocaleString() + suffix;
                }, 30);
            });
        },

        // ============================================================
        // SCROLL REVEAL
        // ============================================================
        initScrollReveal: function() {
            var reveals = document.querySelectorAll('.reveal');
            if (!reveals.length) return;

            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

            reveals.forEach(function(el) { observer.observe(el); });
        },

        // ============================================================
        // THEME TOGGLE
        // ============================================================
        initTheme: function() {
            var toggle = document.getElementById('theme-toggle');
            if (!toggle) return;

            var html = document.documentElement;
            var saved = localStorage.getItem('theme');
            if (saved === 'dark') html.setAttribute('data-theme', 'dark');

            toggle.addEventListener('click', function() {
                var isDark = html.getAttribute('data-theme') === 'dark';
                if (isDark) {
                    html.removeAttribute('data-theme');
                    localStorage.setItem('theme', 'light');
                } else {
                    html.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                }
            });
        },

        initSkeletonLoaders: function() {
            // reserved for future use
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        Dashboard.init();
    });

})();
