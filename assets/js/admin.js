// ============================================================
// ASAAS STUDIO - Admin Dashboard JavaScript
// Premium SaaS Control Panel Interactions
// ============================================================

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initAdminDropdowns();
        initCharts();
        initDataTables();
        initSearchFilters();
        initConfirmDialogs();
        initSelectAll();
        initInlineEdit();
        initNotificationsCenter();

        // Active sidebar item
        const currentPath = window.location.pathname;
        document.querySelectorAll('.sidebar-item').forEach(item => {
            if (item.getAttribute('href') && currentPath.includes(item.getAttribute('href'))) {
                item.classList.add('active');
            }
        });
    });

    // ============================================================
    // ADMIN DROPDOWNS
    // ============================================================
    function initAdminDropdowns() {
        // Notification dropdown
        const notifBtn = document.querySelector('[data-dropdown="notifications"]');
        const notifDropdown = document.getElementById('notifications-dropdown');

        if (notifBtn && notifDropdown) {
            notifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notifDropdown.classList.toggle('active');

                // Mark as read
                if (notifDropdown.classList.contains('active')) {
                    notifBtn.querySelector('.topbar-badge').style.display = 'none';
                }
            });
        }

        // Profile dropdown
        const profileBtn = document.querySelector('[data-dropdown="profile"]');
        const profileDropdown = document.getElementById('profile-dropdown');

        if (profileBtn && profileDropdown) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileDropdown.classList.toggle('active');
            });
        }

        // Close dropdowns on outside click
        document.addEventListener('click', () => {
            document.querySelectorAll('.topbar-dropdown.active').forEach(d => {
                d.classList.remove('active');
            });
        });
    }

    // ============================================================
    // CHARTS (Vanilla JS)
    // ============================================================
    function initCharts() {
        if (typeof Chart !== 'undefined') return;
        const cd = window.chartData || {};

        // Line Chart
        const lineCanvas = document.getElementById('lineChart');
        if (lineCanvas) {
            const data = cd.lineData || [30, 45, 38, 55, 48, 62, 58, 70, 65, 78, 72, 85];
            const labels = cd.lineLabels || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            drawLineChart(lineCanvas, data, labels);
        }

        // Bar Chart
        const barCanvas = document.getElementById('barChart');
        if (barCanvas) {
            const data = cd.barData || [22, 35, 18, 42, 28, 50];
            const labels = cd.barLabels || ['Web', 'Brand', 'UI/UX', 'Dev', 'Market', 'Mobile'];
            drawBarChart(barCanvas, data, labels);
        }

        // Pie Chart
        const pieCanvas = document.getElementById('pieChart');
        if (pieCanvas) {
            const data = cd.pieData || [
                { label: 'Direct', value: 35, color: '#E8632A' },
                { label: 'Organic', value: 28, color: '#2196F3' },
                { label: 'Social', value: 20, color: '#4CAF50' },
                { label: 'Referral', value: 17, color: '#FF9800' }
            ];
            drawPieChart(pieCanvas, data);
        }
    }

    function drawLineChart(canvas, data, labels) {
        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        canvas.style.width = rect.width + 'px';
        canvas.style.height = rect.height + 'px';
        ctx.scale(dpr, dpr);

        const W = rect.width;
        const H = rect.height;
        const padding = 40;
        const chartW = W - padding * 2;
        const chartH = H - padding * 2;

        if (!data || data.length < 2) data = [0, 0];
        if (!labels || labels.length !== data.length) labels = data.map((_, i) => i + 1);
        const max = Math.max(...data);
        const min = Math.min(...data);
        const range = max - min || 1;
        const primary = '#E8632A';
        const primaryLight = 'rgba(232,99,42,0.1)';

        // Grid lines
        ctx.strokeStyle = '#f0f0f0';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 4; i++) {
            const y = padding + (chartH / 4) * i;
            ctx.beginPath();
            ctx.moveTo(padding, y);
            ctx.lineTo(W - padding, y);
            ctx.stroke();

            // Labels
            ctx.fillStyle = '#8a8aaa';
            ctx.font = '11px Inter, sans-serif';
            ctx.textAlign = 'right';
            const value = Math.round(max - (range / 4) * i);
            ctx.fillText(value, padding - 8, y + 4);
        }

        // Area fill
        ctx.beginPath();
        data.forEach((val, i) => {
            const x = padding + (chartW / (data.length - 1)) * i;
            const y = padding + chartH - ((val - min) / range) * chartH;
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        });
        ctx.lineTo(padding + chartW, padding + chartH);
        ctx.lineTo(padding, padding + chartH);
        ctx.closePath();
        ctx.fillStyle = primaryLight;
        ctx.fill();

        // Line
        ctx.beginPath();
        data.forEach((val, i) => {
            const x = padding + (chartW / (data.length - 1)) * i;
            const y = padding + chartH - ((val - min) / range) * chartH;
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        });
        ctx.strokeStyle = primary;
        ctx.lineWidth = 3;
        ctx.stroke();

        // Dots
        data.forEach((val, i) => {
            const x = padding + (chartW / (data.length - 1)) * i;
            const y = padding + chartH - ((val - min) / range) * chartH;
            ctx.beginPath();
            ctx.arc(x, y, 4, 0, Math.PI * 2);
            ctx.fillStyle = primary;
            ctx.fill();
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 2;
            ctx.stroke();
        });

        // X Labels
        const step = Math.max(1, Math.floor(data.length / 6));
        data.forEach((_, i) => {
            if (i % step === 0 || i === data.length - 1) {
                const x = padding + (chartW / (data.length - 1)) * i;
                ctx.fillStyle = '#8a8aaa';
                ctx.font = '11px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(labels[i], x, H - padding + 18);
            }
        });
    }

    function drawBarChart(canvas, data, labels) {
        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        canvas.style.width = rect.width + 'px';
        canvas.style.height = rect.height + 'px';
        ctx.scale(dpr, dpr);

        const W = rect.width;
        const H = rect.height;
        const padding = 40;
        const chartW = W - padding * 2;
        const chartH = H - padding * 2;

        if (!data || data.length === 0) data = [0];
        if (!labels || labels.length !== data.length) labels = data.map((_, i) => i + 1);
        const colors = ['#E8632A', '#2196F3', '#9C27B0', '#4CAF50', '#FF9800', '#00BCD4'];

        const max = Math.max(...data);
        const barW = chartW / data.length * 0.6;
        const gap = chartW / data.length * 0.4;

        data.forEach((val, i) => {
            const x = padding + (chartW / data.length) * i + gap / 2;
            const barH = (val / max) * chartH;
            const y = padding + chartH - barH;

            // Bar with rounded top
            const radius = 4;
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + barW - radius, y);
            ctx.quadraticCurveTo(x + barW, y, x + barW, y + radius);
            ctx.lineTo(x + barW, padding + chartH);
            ctx.lineTo(x, padding + chartH);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
            ctx.fillStyle = colors[i];
            ctx.fill();

            // Label
            ctx.fillStyle = '#8a8aaa';
            ctx.font = '11px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(labels[i], x + barW / 2, H - padding + 18);

            // Value
            ctx.fillStyle = '#1a1a2e';
            ctx.font = '12px Inter, sans-serif';
            ctx.fontWeight = '600';
            ctx.fillText(val, x + barW / 2, y - 6);
        });
    }

    function drawPieChart(canvas, data) {
        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.parentElement.getBoundingClientRect();
        const size = Math.min(rect.width, rect.height);
        canvas.width = size * dpr;
        canvas.height = size * dpr;
        canvas.style.width = size + 'px';
        canvas.style.height = size + 'px';
        ctx.scale(dpr, dpr);

        const cx = size / 2;
        const cy = size / 2;
        const radius = size / 2 - 20;

        if (!data || data.length === 0) data = [{ label: 'No data', value: 100, color: '#e0e0e0' }];

        const total = data.reduce((s, d) => s + d.value, 0);
        let startAngle = -Math.PI / 2;

        data.forEach(item => {
            const sliceAngle = (item.value / total) * Math.PI * 2;
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, radius, startAngle, startAngle + sliceAngle);
            ctx.closePath();
            ctx.fillStyle = item.color;
            ctx.fill();

            // Label
            const midAngle = startAngle + sliceAngle / 2;
            const labelRadius = radius * 0.6;
            const lx = cx + Math.cos(midAngle) * labelRadius;
            const ly = cy + Math.sin(midAngle) * labelRadius;
            ctx.fillStyle = '#fff';
            ctx.font = '12px Inter, sans-serif';
            ctx.fontWeight = '700';
            ctx.textAlign = 'center';
            ctx.fillText(Math.round(item.value) + '%', lx, ly + 4);

            startAngle += sliceAngle;
        });

        // Center hole
        ctx.beginPath();
        ctx.arc(cx, cy, radius * 0.5, 0, Math.PI * 2);
        ctx.fillStyle = '#fff';
        ctx.fill();

        // Legend
        const legendX = size + 10;
        data.forEach((item, i) => {
            const ly = 20 + i * 24;
            ctx.fillStyle = item.color;
            ctx.fillRect(legendX, ly, 12, 12);
            ctx.fillStyle = '#1a1a2e';
            ctx.font = '13px Inter, sans-serif';
            ctx.textAlign = 'left';
            ctx.fillText(`${item.label} (${item.value}%)`, legendX + 18, ly + 11);
        });
    }

    // ============================================================
    // DATA TABLE
    // ============================================================
    function initDataTables() {
        document.querySelectorAll('.table').forEach(table => {
            const search = table.closest('.table-container')?.previousElementSibling?.querySelector('input[type="search"]');
            if (search) {
                search.addEventListener('input', () => {
                    const query = search.value.toLowerCase();
                    table.querySelectorAll('tbody tr').forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(query) ? '' : 'none';
                    });
                });
            }
        });
    }

    // ============================================================
    // SEARCH FILTERS
    // ============================================================
    function initSearchFilters() {
        document.querySelectorAll('.filter-bar input, .filter-bar select').forEach(filter => {
            filter.addEventListener('change', () => {
                const bar = filter.closest('.filter-bar');
                const filters = {};
                bar.querySelectorAll('select, input').forEach(f => {
                    if (f.value) filters[f.name || f.id] = f.value;
                });
                const event = new CustomEvent('filterchange', { detail: filters });
                document.dispatchEvent(event);
            });
        });
    }

    // ============================================================
    // CONFIRM DIALOGS
    // ============================================================
    function initConfirmDialogs() {
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', (e) => {
                if (!confirm(el.dataset.confirm || 'Are you sure?')) {
                    e.preventDefault();
                }
            });
        });
    }

    // ============================================================
    // SELECT ALL
    // ============================================================
    function initSelectAll() {
        document.querySelectorAll('[data-select-all]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const target = checkbox.dataset.selectAll;
                const container = target ? document.getElementById(target) : checkbox.closest('table');
                if (container) {
                    container.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                        cb.checked = checkbox.checked;
                    });
                }
            });
        });
    }

    // ============================================================
    // INLINE EDIT
    // ============================================================
    function initInlineEdit() {
        document.querySelectorAll('[data-inline-edit]').forEach(el => {
            el.addEventListener('dblclick', () => {
                const current = el.textContent;
                const input = document.createElement('input');
                input.type = 'text';
                input.value = current;
                input.className = 'form-input';
                input.style.padding = '4px 8px';
                input.style.fontSize = 'inherit';

                el.textContent = '';
                el.appendChild(input);
                input.focus();

                const save = () => {
                    const val = input.value.trim();
                    if (val && val !== current) {
                        el.textContent = val;
                        const event = new CustomEvent('inlineupdate', {
                            detail: { field: el.dataset.field, value: val, id: el.dataset.id }
                        });
                        document.dispatchEvent(event);
                    } else {
                        el.textContent = current;
                    }
                };

                input.addEventListener('blur', save);
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') { input.blur(); }
                    if (e.key === 'Escape') { el.textContent = current; }
                });
            });
        });
    }

    // ============================================================
    // NOTIFICATIONS CENTER
    // ============================================================
    function initNotificationsCenter() {
        // Mark single notification as read
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                this.classList.add('read');
                const id = this.dataset.id;
                if (id) {
                    fetch(BASE_URL + 'api/index.php?action=mark_read', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + encodeURIComponent(id)
                    });
                }
            });
        });

        // Mark all as read
        document.querySelector('[data-mark-all-read]')?.addEventListener('click', () => {
            fetch(BASE_URL + 'api/index.php?action=mark_all_read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=0'
            }).then(() => {
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.add('read');
                });
            });
        });
    }

    // ============================================================
    // WINDOW RESIZE HANDLER
    // ============================================================
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            initCharts();
        }, 250);
    });

})();
