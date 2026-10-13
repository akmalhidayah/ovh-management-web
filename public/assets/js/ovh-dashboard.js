(function () {
    const body = document.body;
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const closeTargets = document.querySelectorAll('[data-sidebar-close], .sidebar-link:not(.sidebar-group-toggle), .sidebar-sublink');
    const groupToggles = document.querySelectorAll('.sidebar-group-toggle');
    const desktopQuery = window.matchMedia('(min-width: 992px)');

    function isDesktop() {
        return desktopQuery.matches;
    }

    if (localStorage.getItem('ovh-sidebar-collapsed') === 'true' && isDesktop()) {
        body.classList.add('sidebar-collapsed');
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            if (isDesktop()) {
                body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('ovh-sidebar-collapsed', body.classList.contains('sidebar-collapsed') ? 'true' : 'false');
                return;
            }

            body.classList.toggle('sidebar-open');
        });
    }

    closeTargets.forEach(function (target) {
        target.addEventListener('click', function () {
            if (!isDesktop()) {
                body.classList.remove('sidebar-open');
            }
        });
    });

    groupToggles.forEach(function (button) {
        button.addEventListener('click', function () {
            if (body.classList.contains('sidebar-collapsed') && isDesktop()) {
                body.classList.remove('sidebar-collapsed');
                localStorage.setItem('ovh-sidebar-collapsed', 'false');
            }

            const group = button.closest('[data-sidebar-group]');
            const isOpen = group.classList.toggle('open');
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    desktopQuery.addEventListener('change', function () {
        body.classList.remove('sidebar-open');
        if (!isDesktop()) {
            body.classList.remove('sidebar-collapsed');
            return;
        }

        if (localStorage.getItem('ovh-sidebar-collapsed') === 'true') {
            body.classList.add('sidebar-collapsed');
        }
    });

    document.querySelectorAll('[data-work-plan-table]').forEach(function (container) {
        const rows = Array.from(container.querySelectorAll('[data-work-plan-row]'));
        const search = container.querySelector('[data-work-plan-search]');
        const pagination = container.querySelector('[data-work-plan-pagination]');
        const summary = container.querySelector('[data-work-plan-summary]');
        const empty = container.querySelector('[data-work-plan-empty]');
        const perPage = Math.max(1, Number(container.dataset.perPage) || 10);
        let currentPage = 1;
        let query = '';

        function button(label, page, options) {
            const element = document.createElement('button');
            element.type = 'button';
            element.className = 'work-plan-page-btn';
            element.textContent = label;
            element.disabled = Boolean(options && options.disabled);

            if (options && options.active) {
                element.classList.add('active');
                element.setAttribute('aria-current', 'page');
            }

            element.addEventListener('click', function () {
                currentPage = page;
                render();
            });

            return element;
        }

        function render() {
            const filteredRows = rows.filter(function (row) {
                return row.textContent.toLowerCase().includes(query);
            });
            const pageCount = Math.max(1, Math.ceil(filteredRows.length / perPage));
            currentPage = Math.min(currentPage, pageCount);
            const startIndex = (currentPage - 1) * perPage;
            const endIndex = Math.min(startIndex + perPage, filteredRows.length);
            const visibleRows = new Set(filteredRows.slice(startIndex, endIndex));

            rows.forEach(function (row) {
                row.hidden = !visibleRows.has(row);
            });

            empty.hidden = filteredRows.length !== 0;
            summary.textContent = filteredRows.length === 0
                ? '0 data ditemukan'
                : `Menampilkan ${startIndex + 1}-${endIndex} dari ${filteredRows.length} data`;

            pagination.replaceChildren();
            pagination.appendChild(button('Sebelumnya', Math.max(1, currentPage - 1), {
                disabled: currentPage === 1 || filteredRows.length === 0,
            }));

            for (let page = 1; page <= pageCount; page += 1) {
                pagination.appendChild(button(String(page), page, {
                    active: page === currentPage,
                    disabled: filteredRows.length === 0,
                }));
            }

            pagination.appendChild(button('Berikutnya', Math.min(pageCount, currentPage + 1), {
                disabled: currentPage === pageCount || filteredRows.length === 0,
            }));
        }

        if (search) {
            search.addEventListener('input', function () {
                query = search.value.trim().toLowerCase();
                currentPage = 1;
                render();
            });
        }

        render();
    });

    if (!window.Chart) {
        return;
    }

    const palette = ['#1d4ed8', '#16a34a', '#f59e0b', '#dc2626', '#64748b'];

    document.querySelectorAll('[data-chart]').forEach(function (canvas) {
        const type = canvas.dataset.chart;
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'];
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: type === 'doughnut' ? {} : { y: { beginAtZero: true, grid: { color: '#eef2f7' } }, x: { grid: { display: false } } },
        };

        const config = type === 'doughnut'
            ? {
                type: 'doughnut',
                data: { labels: ['Draft', 'Proses', 'Selesai', 'Terlambat'], datasets: [{ data: [18, 42, 31, 9], backgroundColor: palette }] },
                options: commonOptions,
            }
            : {
                type: type,
                data: {
                    labels: labels,
                    datasets: [
                        { label: type === 'bar' ? 'Barang' : 'Plan', data: [18, 28, 36, 48, 58, 66, 76], borderColor: palette[0], backgroundColor: type === 'bar' ? palette[0] : 'rgba(29,78,216,.12)', tension: .35, fill: type !== 'bar' },
                        { label: type === 'bar' ? 'Jasa' : 'Actual', data: [12, 22, 31, 44, 52, 61, 68], borderColor: palette[1], backgroundColor: type === 'bar' ? palette[1] : 'rgba(22,163,74,.12)', tension: .35, fill: type !== 'bar' },
                    ],
                },
                options: commonOptions,
            };

        new Chart(canvas, config);
    });
})();
