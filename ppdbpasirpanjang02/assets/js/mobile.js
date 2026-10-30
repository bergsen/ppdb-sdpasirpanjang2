/* mobile.js - pendukung tampilan mobile PPDB
   1) Tabel -> kartu: mengisi data-label tiap <td> dari <th>.
   2) Sidebar: drawer di ponsel (tutup dengan tombol dalam, tombol Esc, atau overlay). */
(function () {
    'use strict';

    var clean = function (t) { return (t || '').replace(/\s+/g, ' ').trim(); };

    function prepareTable(table) {
        if (!table || table.classList.contains('no-cards') || table.dataset.mReady === '1') return;
        var headRow = table.querySelector('thead tr:last-child') || table.querySelector('tr');
        if (!headRow) return;

        // Susun label per indeks kolom (hormati colspan pada <th>)
        var labels = [];
        Array.prototype.forEach.call(headRow.children, function (th) {
            var span = parseInt(th.getAttribute('colspan') || '1', 10) || 1;
            for (var i = 0; i < span; i++) labels.push(clean(th.textContent));
        });
        if (!labels.length) return;

        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function (tr) {
            var col = 0;
            Array.prototype.forEach.call(tr.children, function (td) {
                if (td.tagName !== 'TD') return;
                var span = parseInt(td.getAttribute('colspan') || '1', 10) || 1;
                var label = labels[col] || '';
                col += span;
                if (span > 1) return; // baris "tidak ada data"
                if (!td.hasAttribute('data-label')) td.setAttribute('data-label', label);
                var l = td.getAttribute('data-label') || label;
                if (/^no\.?$/i.test(l)) td.classList.add('m-no');
                var onlyControls = td.children.length > 0 &&
                    !clean(td.textContent.replace(/[\u200b]/g, '')).replace(/Akun Anda|Ubah Data|Lihat|Hapus|Edit/gi, '').length;
                if (/aksi|action|opsi/i.test(l) || onlyControls) td.classList.add('m-actions');
            });
        });
        table.classList.add('m-cards');
        table.dataset.mReady = '1';
    }

    function prepareAll(root) {
        (root || document).querySelectorAll('table').forEach(prepareTable);
    }

    // ----- Sidebar -----
    function initSidebar() {
        var sidebar = document.getElementById('sidebar');
        if (!sidebar) return;
        var overlay = document.getElementById('sidebarOverlay');
        var isMobile = function () { return window.matchMedia('(max-width: 768px)').matches; };

        var sync = function () {
            document.body.classList.toggle('sidebar-open', sidebar.classList.contains('mobile-open'));
        };
        new MutationObserver(sync).observe(sidebar, { attributes: true, attributeFilter: ['class'] });

        var closeDrawer = function () {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            sync();
        };

        // Tombol hamburger di dalam sidebar: di ponsel fungsinya menutup drawer
        var inner = document.getElementById('sidebarToggle');
        if (inner) inner.addEventListener('click', function () {
            if (isMobile()) { sidebar.classList.remove('collapsed'); closeDrawer(); }
        });

        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });
        window.addEventListener('resize', function () { if (!isMobile()) closeDrawer(); });
        sync();
    }

    function init() {
        prepareAll();
        initSidebar();
        // Tabel yang dimuat belakangan (AJAX / modal)
        var timer = null;
        new MutationObserver(function () {
            clearTimeout(timer);
            timer = setTimeout(prepareAll, 80);
        }).observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
