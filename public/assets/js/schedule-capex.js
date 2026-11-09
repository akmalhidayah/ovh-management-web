/*
 * Rencana Kerja Capex (frontend interaktif, data dummy dari window.SC_DATA).
 * Perubahan hanya hidup di memori halaman; catatan (note) disimpan di localStorage per ajuan.
 * Skrip toleran terhadap elemen yang tidak ada karena dimuat oleh halaman yang dirender server-side.
 */
(function () {
    'use strict';

    var root = document.querySelector('[data-sc]');
    if (!root || !window.SC_DATA || !Array.isArray(window.SC_DATA.rows)) {
        return;
    }

    var rows = window.SC_DATA.rows;
    var PAGE_SIZE = { disetujui: 10, dibatalkan: 10, rekap: 10, form: 10 };
    var RECAP_SIZE = 5;
    var STATUS_CLASS = { Lanjut: 'is-lanjut', Pending: 'is-pending', Batal: 'is-batal' };
    var NOTE_CLASS = { 'Selesai': 'is-done', 'In Progress': 'is-progress', 'Pending': 'is-pending', 'Penting': 'is-important' };
    var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    var state = {
        tab: root.getAttribute('data-initial-tab') || 'dashboard',
        form: false,
        year: '2026',
        plant: '',
        q: '',
        pages: { disetujui: 1, dibatalkan: 1, rekap: 1, form: 1, recap: 1 },
        recapAll: false,
        editId: null,
        noteRowId: null,
        noteEditId: null,
        lastFocus: null
    };

    /* ---------- util ---------- */
    function $(sel, ctx) { return (ctx || root).querySelector(sel); }
    function $$(sel, ctx) { return Array.prototype.slice.call((ctx || root).querySelectorAll(sel)); }
    function esc(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function money(n) {
        return Number(n || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function toast(message, icon) {
        if (window.Swal) {
            window.Swal.fire({ toast: true, position: 'top-end', icon: icon || 'success', title: message, timer: 2200, showConfirmButton: false });
        }
    }
    function ask(title, text) {
        if (window.Swal) {
            return window.Swal.fire({ title: title, text: text, icon: 'question', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal' })
                .then(function (r) { return !!r.isConfirmed; });
        }
        return Promise.resolve(window.confirm(text || title));
    }
    function pad(n) { return String(n).length < 2 ? '0' + n : String(n); }
    function nowLabel() {
        var d = new Date();
        return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear() + ', ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    function todayIso() {
        var d = new Date();
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }
    function dateLabel(iso) {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
        return m ? m[3] + ' ' + MONTHS[Number(m[2]) - 1] + ' ' + m[1] : (iso || '-');
    }
    function download(filename, content) {
        var blob = new Blob(['\uFEFF' + content], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(function () { URL.revokeObjectURL(url); }, 500);
    }
    function csvCell(v) { return '"' + String(v == null ? '' : v).replace(/"/g, '""') + '"'; }
    function findRow(id) { return rows.filter(function (r) { return r.id === id; })[0] || null; }
    function total(r) { return Number(r.qty || 0) * Number(r.price || 0); }

    /* ---------- filter ---------- */
    function matches(r) {
        if (String(r.year) !== state.year) { return false; }
        if (state.plant && r.plant !== state.plant) { return false; }
        if (!state.q) { return true; }
        var hay = [r.unit, r.short_text, r.material_no, r.mrp_type, r.status, r.plant, r.rif].join(' ').toLowerCase();
        return hay.indexOf(state.q) !== -1;
    }
    function listFor(key) {
        return rows.filter(function (r) {
            if (!matches(r)) { return false; }
            if (key === 'disetujui') { return r.status !== 'Batal'; }
            if (key === 'dibatalkan') { return r.status === 'Batal'; }
            return true;
        });
    }

    /* ---------- pager ---------- */
    function renderPager(nav, totalItems, page, size, onChange) {
        if (!nav) { return; }
        var pages = Math.max(1, Math.ceil(totalItems / size));
        var html = '<button type="button" data-page="' + (page - 1) + '" aria-label="Sebelumnya"' + (page <= 1 ? ' disabled' : '') + '><i class="bi bi-chevron-left"></i></button>';
        for (var p = 1; p <= pages; p++) {
            if (pages > 7 && p !== 1 && p !== pages && Math.abs(p - page) > 1) {
                if (p === 2 || p === pages - 1) { html += '<span class="sc-pager-gap">…</span>'; }
                continue;
            }
            html += '<button type="button" data-page="' + p + '"' + (p === page ? ' class="is-active" aria-current="page"' : '') + '>' + p + '</button>';
        }
        html += '<button type="button" data-page="' + (page + 1) + '" aria-label="Berikutnya"' + (page >= pages ? ' disabled' : '') + '><i class="bi bi-chevron-right"></i></button>';
        nav.innerHTML = html;
        nav.onclick = function (e) {
            var btn = e.target.closest('button[data-page]');
            if (!btn || btn.disabled) { return; }
            onChange(Number(btn.getAttribute('data-page')));
        };
    }
    function clampPage(key, totalItems, size) {
        var pages = Math.max(1, Math.ceil(totalItems / size));
        if (state.pages[key] > pages) { state.pages[key] = pages; }
        if (state.pages[key] < 1) { state.pages[key] = 1; }
        return state.pages[key];
    }
    function summary(from, to, totalItems, noun) {
        return totalItems === 0 ? 'Tidak ada data' : 'Menampilkan ' + from + (noun === 'material' ? ' - ' : '-') + to + ' dari ' + totalItems + ' ' + noun;
    }

    /* ---------- tabel ajuan ---------- */
    function noteCount(id) { return loadNotes(id).length; }

    function hasDrawing(r) { return !!(r.drawing && r.drawing !== '-'); }
    function bomChip(r) {
        var ok = r.bom_status === 'OK';
        return '<span class="sc-bom-tag ' + (ok ? 'is-ok' : 'is-no') + '">' + (ok ? '<i class="bi bi-check2" aria-hidden="true"></i> ' : '') + esc(r.bom_status) + '</span>';
    }
    function drawingCell(r) {
        return hasDrawing(r)
            ? '<button type="button" class="sc-icon is-pdf" data-sc-drawing="' + esc(r.id) + '" aria-label="Lihat drawing ' + esc(r.short_text) + '"><i class="bi bi-file-earmark-pdf-fill"></i></button>'
            : '<span class="sc-muted">-</span>';
    }
    function cancelCell(r, key) {
        if (key === 'disetujui') { return ''; }
        if (key === 'dibatalkan') {
            return '<button type="button" class="sc-cancel" data-sc-restore="' + esc(r.id) + '" title="Klik untuk memulihkan ajuan">Cancel</button>';
        }
        if (r.status === 'Batal') { return '<span class="sc-cancel is-done">Cancel</span>'; }
        return r.bom_status === 'NO'
            ? '<button type="button" class="sc-cancel" data-sc-cancel="' + esc(r.id) + '" aria-label="Batalkan ajuan ' + esc(r.short_text) + '">Cancel</button>'
            : '';
    }
    function simpleRow(r, no, key) {
        return '<tr data-id="' + esc(r.id) + '">' +
            '<td>' + no + '</td>' +
            '<td class="sc-strong">' + esc(r.plant) + '</td>' +
            '<td>' + esc(r.unit) + '</td>' +
            '<td class="sc-mono sc-muted-text">' + esc(r.mrp_type) + '</td>' +
            '<td class="sc-mono">' + esc(r.material_no) + '</td>' +
            '<td class="sc-strong">' + esc(r.short_text) + '</td>' +
            '<td class="is-num sc-strong">' + Number(r.qty || 0).toLocaleString('id-ID') + '</td>' +
            '<td>' + esc(r.uom) + '</td>' +
            '<td class="is-num">' + Number(r.price || 0).toLocaleString('id-ID') + '</td>' +
            '<td class="is-num sc-total">' + total(r).toLocaleString('id-ID') + '</td>' +
            '<td>' + bomChip(r) + '</td>' +
            '<td>' + drawingCell(r) + '</td>' +
            '<td>' + esc(r.rif) + '</td>' +
            '<td>' + editButton(r) + '</td>' +
            '<td class="sc-cancel-col">' + cancelCell(r, key) + '</td>' +
            '<td>' + noteButton(r, noteCount(r.id)) + '</td></tr>';
    }
    function wideRow(r, no) {
        return '<tr data-id="' + esc(r.id) + '">' +
            '<td>' + no + '</td>' +
            '<td><span class="sc-plant-tag">' + esc(r.plant) + '</span></td>' +
            '<td class="sc-strong">' + esc(r.unit) + '</td>' +
            '<td>' + esc(r.plant_section) + '</td>' +
            '<td class="sc-strong">' + esc(r.program) + '</td>' +
            '<td class="sc-mono">' + esc(r.equipment_id) + '</td>' +
            '<td class="is-num">' + esc(r.qty_bom) + '</td>' +
            '<td>' + esc(r.uom_bom) + '</td>' +
            '<td>' + esc(r.mrp_type) + '</td>' +
            '<td>' + esc(r.lifetime) + '</td>' +
            '<td class="sc-mono">' + esc(r.notification) + '</td>' +
            '<td class="sc-mono">' + esc(r.order) + '</td>' +
            '<td class="sc-mono">' + esc(r.reservation) + '</td>' +
            '<td class="sc-mono">' + esc(r.material_no) + '</td>' +
            '<td class="sc-mono">' + esc(r.po_ref) + '</td>' +
            '<td>' + esc(r.po_date) + '</td>' +
            '<td class="is-num">' + money(r.price) + '</td>' +
            '<td class="is-num">' + esc(r.qty) + '</td>' +
            '<td class="is-num sc-total">' + money(total(r)) + '</td>' +
            '<td>' + esc(r.pic) + '</td>' +
            '<td class="is-num">' + esc(r.qty_approved) + '</td>' +
            '<td class="sc-strong">' + esc(r.short_text) + '</td>' +
            '<td>' + bomChip(r) + '</td>' +
            '<td>' + drawingCell(r) + '</td>' +
            '<td>' + esc(r.rif) + '</td>' +
            '<td>' + statusBadge(r.status) + '</td>' +
            '<td>' + editButton(r) + '</td>' +
            '<td class="sc-cancel-col">' + cancelCell(r, 'rekap') + '</td>' +
            '<td>' + noteButton(r, noteCount(r.id)) + '</td></tr>';
    }
    function statusBadge(status) {
        return '<span class="sc-status ' + (STATUS_CLASS[status] || '') + '">' + esc(status) + '</span>';
    }
    function noteButton(r, count) {
        return '<button type="button" class="sc-icon' + (count ? ' has-notes' : '') + '" data-sc-note-open="' + esc(r.id) + '" aria-label="Catatan ' + esc(r.short_text) + (count ? ' (' + count + ')' : '') + '"><i class="bi bi-chat-left-text"></i>' + (count ? '<b class="sc-count">' + count + '</b>' : '') + '</button>';
    }
    function editButton(r) {
        return '<button type="button" class="sc-icon is-edit" data-sc-edit="' + esc(r.id) + '" aria-label="Edit ' + esc(r.short_text) + '"><i class="bi bi-pencil-square"></i></button>';
    }

    function renderList(key) {
        var box = $('[data-sc-list="' + key + '"]');
        if (!box) { return; }
        var items = listFor(key === 'form' ? 'rekap' : key);
        var size = PAGE_SIZE[key];
        var page = clampPage(key, items.length, size);
        var start = (page - 1) * size;
        var slice = items.slice(start, start + size);
        var tbody = $('[data-sc-body]', box);
        tbody.innerHTML = slice.map(function (r, i) { return key === 'form' ? wideRow(r, start + i + 1) : simpleRow(r, start + i + 1, key); }).join('');
        var empty = $('[data-sc-empty]', box);
        if (empty) { empty.hidden = items.length !== 0; }
        var sum = $('[data-sc-summary]', box);
        if (sum) { sum.textContent = summary(items.length ? start + 1 : 0, start + slice.length, items.length, 'material'); }
        renderPager($('[data-sc-pager]', box), items.length, page, size, function (p) {
            state.pages[key] = p;
            renderList(key);
        });
    }

    /* ---------- dashboard ---------- */
    function renderRecap() {
        var box = $('[data-sc-recap]');
        if (!box) { return; }
        var all = $$('[data-sc-recap-row]', box);
        var visible = all.filter(function (tr) {
            var okPlant = !state.plant || tr.getAttribute('data-plant') === state.plant;
            var okQ = !state.q || tr.getAttribute('data-text').indexOf(state.q) !== -1;
            return okPlant && okQ;
        });
        var size = state.recapAll ? Math.max(visible.length, 1) : RECAP_SIZE;
        var page = clampPage('recap', visible.length, size);
        var start = (page - 1) * size;
        all.forEach(function (tr) { tr.hidden = true; });
        visible.slice(start, start + size).forEach(function (tr) { tr.hidden = false; });
        var empty = $('[data-sc-recap-empty]', box);
        if (empty) { empty.hidden = visible.length !== 0; }
        var sum = $('[data-sc-recap-summary]', box);
        if (sum) { sum.textContent = summary(visible.length ? start + 1 : 0, Math.min(start + size, visible.length), visible.length, 'data'); }
        renderPager($('[data-sc-recap-pager]', box), visible.length, page, size, function (p) {
            state.pages.recap = p;
            renderRecap();
        });
        var toggle = $('[data-sc-recap-all]', box);
        if (toggle) { toggle.innerHTML = (state.recapAll ? 'Lihat Ringkas' : 'Lihat Semua') + ' <i class="bi bi-arrow-right" aria-hidden="true"></i>'; }
        $$('[data-sc-plant-row]').forEach(function (li) {
            li.classList.toggle('is-dim', !!state.plant && li.getAttribute('data-sc-plant-row') !== state.plant);
        });
    }

    /* ---------- tab & mode ---------- */
    function currentMode() {
        if (state.tab === 'dashboard') { return 'dashboard'; }
        return state.tab === 'rekap' && state.form ? 'form' : 'list';
    }
    function applyMode() {
        var mode = currentMode();
        $$('[data-sc-show]').forEach(function (el) { el.hidden = el.getAttribute('data-sc-show') !== mode; });
        var list = $('[data-sc-rekap-list]');
        var form = $('[data-sc-rekap-form]');
        if (list) { list.hidden = state.form; }
        if (form) { form.hidden = !state.form; }
    }
    function setTab(key, focus) {
        state.tab = key;
        if (key !== 'rekap') { state.form = false; }
        $$('[data-sc-tab]').forEach(function (btn) {
            var on = btn.getAttribute('data-sc-tab') === key;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
            btn.tabIndex = on ? 0 : -1;
            if (on && focus) { btn.focus(); }
        });
        $$('[data-sc-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-sc-panel') !== key; });
        applyMode();
        renderActive();
        try {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', key);
            window.history.replaceState(null, '', url.toString());
        } catch (e) { /* URL API tidak tersedia: abaikan */ }
    }
    function renderActive() {
        if (state.tab === 'dashboard') { renderRecap(); return; }
        if (state.tab === 'rekap') { renderList(state.form ? 'form' : 'rekap'); return; }
        renderList(state.tab);
    }
    function refreshAll() {
        renderRecap();
        ['disetujui', 'dibatalkan', 'rekap', 'form'].forEach(renderList);
    }
    function resetPages() {
        Object.keys(state.pages).forEach(function (k) { state.pages[k] = 1; });
    }

    /* ---------- modal umum ---------- */
    function openModal(name, opener) {
        var modal = $('[data-sc-modal="' + name + '"]');
        if (!modal) { return null; }
        state.lastFocus = opener || document.activeElement;
        modal.hidden = false;
        document.body.classList.add('sc-modal-open');
        var first = modal.querySelector('input:not([readonly]):not([type=hidden]), textarea, select, button');
        if (first) { first.focus(); }
        return modal;
    }
    function closeModal(modal) {
        if (!modal || modal.hidden) { return; }
        modal.hidden = true;
        if (!$('[data-sc-modal]:not([hidden])')) { document.body.classList.remove('sc-modal-open'); }
        if (state.lastFocus && document.contains(state.lastFocus)) { state.lastFocus.focus(); }
    }
    function openModalEl() { return $('[data-sc-modal]:not([hidden])'); }

    /* ---------- modal edit ---------- */
    var editForm = $('[data-sc-edit-form]');
    var longText = editForm ? $('[data-sc-longtext]', editForm) : null;

    function field(name) { return editForm.elements[name]; }
    function setBom(value) {
        field('bom_status').value = value;
        $$('[data-bom]', editForm).forEach(function (btn) {
            btn.setAttribute('aria-pressed', btn.getAttribute('data-bom') === value ? 'true' : 'false');
        });
    }
    function syncTotal() {
        var qty = Number(field('qty').value) || 0;
        var price = Number(field('price').value) || 0;
        field('qty_price').value = qty;
        field('total').value = money(qty * price);
    }
    function openEdit(id, opener) {
        if (!editForm) { return; }
        var r = id ? findRow(id) : null;
        state.editId = r ? r.id : null;
        editForm.reset();
        var defaults = { year: state.year, status: 'Pending', bom_status: 'OK', category: 'Critical Sparepart', qty: 1, price: 0, warranty: 12 };
        Array.prototype.forEach.call(editForm.elements, function (el) {
            if (!el.name || el.name === 'total' || el.name === 'qty_price') { return; }
            var value = r ? r[el.name] : defaults[el.name];
            el.value = value == null ? '' : value;
        });
        longText.textContent = r ? (r.long_text || '') : '';
        setBom(r ? r.bom_status : 'OK');
        $('[data-sc-edit-mode]').textContent = r ? '(EDIT)' : '(TAMBAH)';
        $('[data-sc-edit-error]').hidden = true;
        syncTotal();
        openModal('edit', opener);
    }
    function saveEdit() {
        var err = $('[data-sc-edit-error]');
        var unit = field('unit').value.trim();
        var qty = Number(field('qty').value);
        var price = Number(field('price').value);
        var problems = [];
        if (!unit) { problems.push('Unit Kerja wajib diisi.'); }
        if (!(qty > 0)) { problems.push('Quantity harus lebih dari 0.'); }
        if (!(price >= 0) || field('price').value === '') { problems.push('Harga satuan wajib diisi.'); }
        if (problems.length) {
            err.textContent = problems.join(' ');
            err.hidden = false;
            return;
        }
        var r = state.editId ? findRow(state.editId) : null;
        var isNew = !r;
        if (isNew) {
            r = { id: 'sc-new-' + Date.now(), notes: [] };
            rows.unshift(r);
        }
        Array.prototype.forEach.call(editForm.elements, function (el) {
            if (!el.name || el.name === 'total' || el.name === 'qty_price' || el.name === 'year') { return; }
            r[el.name] = el.value;
        });
        r.year = isNew ? Number(state.year) : r.year;
        r.qty = qty;
        r.price = price;
        r.qty_bom = Number(r.qty_bom) || 0;
        r.qty_approved = Number(r.qty_approved) || 0;
        r.long_text = longText.textContent.trim();
        if (r.status === 'Batal') { r.bom_status = 'NO'; r.qty_approved = 0; }
        if (isNew) {
            r.plant = state.plant || guessPlant(r.plant_section);
            if (!r.pic) { r.pic = '-'; }
        }
        closeModal($('[data-sc-modal="edit"]'));
        refreshAll();
        toast(isNew ? 'Ajuan baru ditambahkan (pratinjau).' : 'Perubahan ajuan disimpan (pratinjau).');
    }
    function guessPlant(section) {
        var options = $$('[data-sc-plant] option').map(function (o) { return o.value; }).filter(Boolean);
        return options.indexOf(section) !== -1 ? section : (options[3] || options[0] || '');
    }

    /* ---------- modal catatan (localStorage) ---------- */
    var noteForm = $('[data-sc-note-form]');
    var noteCache = {};

    function storageKey(id) { return 'sc-notes:' + id; }
    function loadNotes(id) {
        if (noteCache[id]) { return noteCache[id]; }
        var list = null;
        try {
            var raw = window.localStorage.getItem(storageKey(id));
            if (raw) { list = JSON.parse(raw); }
        } catch (e) { list = null; }
        if (!Array.isArray(list)) {
            var r = findRow(id);
            list = r && Array.isArray(r.notes) ? r.notes.map(function (n) { return Object.assign({}, n); }) : [];
        }
        noteCache[id] = list;
        return list;
    }
    function saveNotes(id) {
        try { window.localStorage.setItem(storageKey(id), JSON.stringify(noteCache[id] || [])); } catch (e) { /* storage penuh/diblok */ }
    }
    function noteFilter() {
        var q = ($('[data-sc-note-search]').value || '').trim().toLowerCase();
        var st = $('[data-sc-note-filter]').value;
        return loadNotes(state.noteRowId).filter(function (n) {
            return (!st || n.status === st) && (!q || (n.text || '').toLowerCase().indexOf(q) !== -1);
        });
    }
    function renderNotes() {
        var all = loadNotes(state.noteRowId);
        var shown = noteFilter();
        var body = $('[data-sc-note-body]');
        body.innerHTML = shown.map(function (n, i) {
            var long = (n.text || '').length > 70;
            return '<tr data-note="' + esc(n.id) + '">' +
                '<td>' + (i + 1) + '</td>' +
                '<td class="sc-mono"><i class="bi bi-calendar3" aria-hidden="true"></i> ' + esc(dateLabel(n.date)) + '</td>' +
                '<td><span class="sc-note-text" data-full="' + esc(n.text) + '">' + esc(long ? n.text.slice(0, 70) + '…' : n.text) + '</span>' +
                (long ? '<button type="button" class="sc-readmore" data-sc-note-more aria-expanded="false">Read More +</button>' : '') + '</td>' +
                '<td><span class="sc-note-status ' + (NOTE_CLASS[n.status] || '') + '">' + esc(n.status) + '</span></td>' +
                '<td class="sc-note-actions">' +
                '<button type="button" data-sc-note-edit="' + esc(n.id) + '" aria-label="Ubah catatan"><i class="bi bi-pencil-square"></i></button>' +
                '<button type="button" data-sc-note-del="' + esc(n.id) + '" aria-label="Hapus catatan"><i class="bi bi-trash3"></i></button>' +
                '<button type="button" class="' + (n.status === 'Selesai' ? 'is-done' : '') + '" data-sc-note-done="' + esc(n.id) + '" aria-label="Tandai selesai" aria-pressed="' + (n.status === 'Selesai') + '"><i class="bi ' + (n.status === 'Selesai' ? 'bi-check-circle-fill' : 'bi-circle') + '"></i></button>' +
                '</td></tr>';
        }).join('');
        $('[data-sc-note-empty]').hidden = shown.length !== 0;
        $('[data-sc-note-total]').textContent = all.length;
        $('[data-sc-note-donecount]').textContent = all.filter(function (n) { return n.status === 'Selesai'; }).length;
        $('[data-sc-note-summary]').textContent = 'Menampilkan ' + shown.length + ' dari ' + all.length + ' data catatan';
    }
    function resetNoteForm() {
        state.noteEditId = null;
        noteForm.reset();
        noteForm.elements.date.value = todayIso();
        $('[data-sc-note-form-title]').textContent = 'TAMBAH REMARK BARU';
        $('[data-sc-note-submit]').textContent = 'Simpan Remark';
        $('[data-sc-note-cancel]').hidden = true;
        $('[data-sc-note-error]').hidden = true;
    }
    function openNotes(id, opener) {
        var r = findRow(id);
        if (!r || !noteForm) { return; }
        state.noteRowId = id;
        $('[data-sc-note-context]').textContent = '— ' + r.short_text;
        $('[data-sc-note-search]').value = '';
        $('[data-sc-note-filter]').value = '';
        resetNoteForm();
        renderNotes();
        openModal('note', opener);
    }
    function submitNote() {
        var date = noteForm.elements.date.value;
        var text = noteForm.elements.text.value.trim();
        var status = noteForm.elements.status.value;
        var err = $('[data-sc-note-error]');
        if (!date || !text) {
            err.textContent = 'Tanggal dan catatan wajib diisi.';
            err.hidden = false;
            return;
        }
        var list = loadNotes(state.noteRowId);
        if (state.noteEditId) {
            list.forEach(function (n) { if (n.id === state.noteEditId) { n.date = date; n.text = text; n.status = status; } });
        } else {
            list.push({ id: 'n-' + Date.now(), date: date, status: status, text: text });
        }
        saveNotes(state.noteRowId);
        toast(state.noteEditId ? 'Remark diperbarui.' : 'Remark ditambahkan.');
        resetNoteForm();
        renderNotes();
        refreshAll();
    }
    function noteCsv() {
        var lines = [['No', 'Tanggal', 'Status', 'Catatan'].map(csvCell).join(',')];
        noteFilter().forEach(function (n, i) { lines.push([i + 1, n.date, n.status, n.text].map(csvCell).join(',')); });
        download('catatan-' + state.noteRowId + '.csv', lines.join('\r\n'));
    }

    /* ---------- export tabel ---------- */
    function exportCsv() {
        var items = listFor('rekap');
        var head = ['No', 'Plant', 'Unit Kerja', 'MRP', 'No. Material', 'Short Text Material', 'Quantity', 'OUM', 'Harga Satuan', 'Harga Total', 'Status BOM', 'RIF', 'Status Ajuan'];
        var lines = [head.map(csvCell).join(',')];
        items.forEach(function (r, i) {
            lines.push([i + 1, r.plant, r.unit, r.mrp_type, r.material_no, r.short_text, r.qty, r.uom, r.price, total(r), r.bom_status, r.rif, r.status].map(csvCell).join(','));
        });
        download('rekap-ajuan-capex-' + state.year + '.csv', lines.join('\r\n'));
        toast('Export dimulai (' + items.length + ' baris).');
    }

    /* ---------- event ---------- */
    root.addEventListener('click', function (e) {
        var t = e.target;
        var tab = t.closest('[data-sc-tab]');
        if (tab) { setTab(tab.getAttribute('data-sc-tab')); return; }
        var edit = t.closest('[data-sc-edit]');
        if (edit) { openEdit(edit.getAttribute('data-sc-edit'), edit); return; }
        var note = t.closest('[data-sc-note-open]');
        if (note) { openNotes(note.getAttribute('data-sc-note-open'), note); return; }
        if (t.closest('[data-sc-close]')) { closeModal(t.closest('[data-sc-modal]')); return; }

        if (t.closest('[data-sc-add]')) { state.form = true; setTab('rekap'); return; }
        if (t.closest('[data-sc-back]')) { state.form = false; applyMode(); renderActive(); return; }
        if (t.closest('[data-sc-new]')) { openEdit(null, t.closest('[data-sc-new]')); return; }
        if (t.closest('[data-sc-export]')) { exportCsv(); return; }
        if (t.closest('[data-sc-print]')) { window.print(); return; }
        if (t.closest('[data-sc-import]')) { $('[data-sc-import-input]').click(); return; }
        if (t.closest('[data-sc-draft]')) { toast('Draft tersimpan (pratinjau).'); return; }
        if (t.closest('[data-sc-send]')) {
            var count = listFor('rekap').length;
            ask('Kirim ajuan?', count + ' ajuan akan dikirim untuk evaluasi (pratinjau).').then(function (ok) { if (ok) { toast('Ajuan dikirim (pratinjau).'); } });
            return;
        }
        if (t.closest('[data-sc-refresh]')) {
            var btn = t.closest('[data-sc-refresh]');
            btn.classList.add('is-spinning');
            setTimeout(function () {
                btn.classList.remove('is-spinning');
                $('[data-sc-updated]').textContent = nowLabel();
                refreshAll();
            }, 700);
            return;
        }
        if (t.closest('[data-sc-recap-all]')) { state.recapAll = !state.recapAll; state.pages.recap = 1; renderRecap(); return; }
        var more = t.closest('[data-sc-readmore]');
        if (more) {
            var detail = more.parentNode.querySelector('.sc-note-detail');
            var open = detail.hidden;
            detail.hidden = !open;
            more.setAttribute('aria-expanded', open ? 'true' : 'false');
            more.innerHTML = (open ? 'Show Less ' : 'Read More ') + '<i class="bi ' + (open ? 'bi-caret-down-fill' : 'bi-caret-right-fill') + '" aria-hidden="true"></i>';
            return;
        }
        var toggle = t.closest('[data-sc-notes-toggle]');
        if (toggle) {
            var extras = $$('[data-extra]', toggle.closest('[data-sc-notes]'));
            var expanded = toggle.getAttribute('data-expanded') === '1';
            extras.forEach(function (li) { li.hidden = expanded; });
            toggle.setAttribute('data-expanded', expanded ? '0' : '1');
            toggle.textContent = expanded
                ? 'Menampilkan 5 dari ' + toggle.getAttribute('data-total') + ' catatan evaluasi'
                : 'Menampilkan semua ' + toggle.getAttribute('data-total') + ' catatan evaluasi';
            return;
        }

        /* cancel / pulihkan / drawing */
        var cancel = t.closest('[data-sc-cancel]');
        if (cancel) {
            var cr = findRow(cancel.getAttribute('data-sc-cancel'));
            if (cr) {
                ask('Batalkan ajuan?', cr.short_text + ' akan dipindahkan ke tab Dibatalkan.').then(function (ok) {
                    if (!ok) { return; }
                    cr.status = 'Batal'; cr.bom_status = 'NO'; cr.qty_approved = 0;
                    refreshAll();
                    toast('Ajuan dibatalkan (pratinjau).');
                });
            }
            return;
        }
        var restore = t.closest('[data-sc-restore]');
        if (restore) {
            var rr = findRow(restore.getAttribute('data-sc-restore'));
            if (rr) {
                ask('Pulihkan ajuan?', rr.short_text + ' akan dikembalikan ke daftar ajuan (status Pending).').then(function (ok) {
                    if (!ok) { return; }
                    rr.status = 'Pending'; rr.qty_approved = rr.qty;
                    refreshAll();
                    toast('Ajuan dipulihkan (pratinjau).');
                });
            }
            return;
        }
        var drawing = t.closest('[data-sc-drawing]');
        if (drawing) {
            var dr = findRow(drawing.getAttribute('data-sc-drawing'));
            if (dr) { toast('Pratinjau drawing: ' + dr.drawing, 'info'); }
            return;
        }

        /* edit: bom toggle & toolbar teks */
        var bom = t.closest('[data-bom]');
        if (bom) { setBom(bom.getAttribute('data-bom')); return; }
        var cmd = t.closest('[data-cmd]');
        if (cmd) {
            var command = cmd.getAttribute('data-cmd');
            longText.focus();
            if (command === 'createLink') {
                var url = window.prompt('Masukkan URL (https://...)');
                if (url && /^https?:\/\//i.test(url)) { document.execCommand('createLink', false, url); }
            } else {
                document.execCommand(command, false, null);
            }
            return;
        }

        /* catatan */
        var nEdit = t.closest('[data-sc-note-edit]');
        if (nEdit) {
            var n = loadNotes(state.noteRowId).filter(function (x) { return x.id === nEdit.getAttribute('data-sc-note-edit'); })[0];
            if (n) {
                state.noteEditId = n.id;
                noteForm.elements.date.value = n.date;
                noteForm.elements.status.value = n.status;
                noteForm.elements.text.value = n.text;
                $('[data-sc-note-form-title]').textContent = 'UBAH REMARK';
                $('[data-sc-note-submit]').textContent = 'Perbarui Remark';
                $('[data-sc-note-cancel]').hidden = false;
                noteForm.elements.text.focus();
            }
            return;
        }
        var nDel = t.closest('[data-sc-note-del]');
        if (nDel) {
            var delId = nDel.getAttribute('data-sc-note-del');
            ask('Hapus catatan?', 'Catatan ini akan dihapus dari perangkat ini.').then(function (ok) {
                if (!ok) { return; }
                noteCache[state.noteRowId] = loadNotes(state.noteRowId).filter(function (x) { return x.id !== delId; });
                saveNotes(state.noteRowId);
                renderNotes();
                refreshAll();
            });
            return;
        }
        var nDone = t.closest('[data-sc-note-done]');
        if (nDone) {
            var doneId = nDone.getAttribute('data-sc-note-done');
            loadNotes(state.noteRowId).forEach(function (x) { if (x.id === doneId) { x.status = x.status === 'Selesai' ? 'Pending' : 'Selesai'; } });
            saveNotes(state.noteRowId);
            renderNotes();
            return;
        }
        var nMore = t.closest('[data-sc-note-more]');
        if (nMore) {
            var span = nMore.parentNode.querySelector('.sc-note-text');
            var expand = nMore.getAttribute('aria-expanded') !== 'true';
            span.textContent = expand ? span.getAttribute('data-full') : span.getAttribute('data-full').slice(0, 70) + '…';
            nMore.setAttribute('aria-expanded', expand ? 'true' : 'false');
            nMore.textContent = expand ? 'Show Less -' : 'Read More +';
            return;
        }
        if (t.closest('[data-sc-note-cancel]')) { resetNoteForm(); return; }
        if (t.closest('[data-sc-note-export]')) { noteCsv(); return; }
        if (t.closest('[data-sc-note-print]')) {
            document.body.classList.add('sc-print-note');
            window.print();
            setTimeout(function () { document.body.classList.remove('sc-print-note'); }, 300);
        }
    });

    root.addEventListener('keydown', function (e) {
        var tab = e.target.closest ? e.target.closest('[data-sc-tab]') : null;
        if (tab && (e.key === 'ArrowRight' || e.key === 'ArrowLeft')) {
            var tabs = $$('[data-sc-tab]');
            var idx = tabs.indexOf(tab) + (e.key === 'ArrowRight' ? 1 : -1);
            var next = tabs[(idx + tabs.length) % tabs.length];
            setTab(next.getAttribute('data-sc-tab'), true);
            e.preventDefault();
        }
    });

    document.addEventListener('keydown', function (e) {
        var modal = openModalEl();
        if (!modal) { return; }
        if (e.key === 'Escape') {
            if (window.Swal && window.Swal.isVisible && window.Swal.isVisible()) { return; }
            closeModal(modal);
            return;
        }
        if (e.key === 'Tab') {
            var focusable = $$('a[href], button:not([disabled]), input:not([disabled]):not([type=hidden]):not([readonly]), select, textarea, [contenteditable=true]', modal)
                .filter(function (el) { return el.offsetParent !== null; });
            if (!focusable.length) { return; }
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
            else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
        }
    });

    $('[data-sc-year]').addEventListener('change', function (e) { state.year = e.target.value; resetPages(); refreshAll(); });
    $('[data-sc-plant]').addEventListener('change', function (e) { state.plant = e.target.value; resetPages(); refreshAll(); });
    var searchTimer = null;
    $('[data-sc-search]').addEventListener('input', function (e) {
        var value = e.target.value.trim().toLowerCase();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { state.q = value; resetPages(); refreshAll(); }, 150);
    });

    $('[data-sc-import-input]').addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        if (file) { toast('File "' + file.name + '" dipilih (pratinjau, belum diproses).', 'info'); }
        e.target.value = '';
    });

    if (editForm) {
        editForm.addEventListener('submit', function (e) { e.preventDefault(); saveEdit(); });
        ['qty', 'price'].forEach(function (name) { field(name).addEventListener('input', syncTotal); });
        $('[data-sc-longtext]', editForm).addEventListener('paste', function (e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text');
            document.execCommand('insertText', false, text);
        });
        $$('.sc-rte-bar button', editForm).forEach(function (b) { b.addEventListener('mousedown', function (ev) { ev.preventDefault(); }); });
    }
    if (noteForm) {
        noteForm.addEventListener('submit', function (e) { e.preventDefault(); submitNote(); });
        $('[data-sc-note-search]').addEventListener('input', renderNotes);
        $('[data-sc-note-filter]').addEventListener('change', renderNotes);
    }

    /* ---------- init ---------- */
    $$('[data-sc-tab]').forEach(function (btn) { btn.tabIndex = btn.classList.contains('is-active') ? 0 : -1; });
    applyMode();
    refreshAll();
})();
