/*
 * Rencana Kerja Jasa (frontend interaktif, data dummy dari window.SJ_DATA).
 * Perubahan hanya hidup di memori halaman; catatan (note) disimpan di localStorage per ajuan.
 * Skrip toleran terhadap elemen yang tidak ada karena dimuat oleh halaman yang dirender server-side.
 */
(function () {
    'use strict';

    var root = document.querySelector('[data-sj]');
    if (!root || !window.SJ_DATA || !Array.isArray(window.SJ_DATA.rows)) {
        return;
    }

    var rows = window.SJ_DATA.rows;
    var PAGE_SIZE = { disetujui: 15, intern: 15, dibatalkan: 15, rekap: 15, form: 15 };
    var INTERNAL_STATUSES = ['PGO', 'TS', 'BMS'];
    var RECAP_SIZE = 5;
    var STATUS_CLASS = { Lanjut: 'is-lanjut', PGO: 'is-pgo', TS: 'is-ts', BMS: 'is-bms', Batal: 'is-batal' };
    var STATUS_ICON = { Lanjut: 'bi-check2', PGO: 'bi-check2', TS: 'bi-file-earmark-text', BMS: 'bi-building', Batal: 'bi-x' };
    var PROCESS_CLASS = { 'Kontrak': 'is-kontrak', 'Fabrikasi': 'is-fabrikasi', 'Tender': 'is-tender', 'Cancel': 'is-cancel', 'BMS Review': 'is-bms-review' };
    var NOTE_CLASS = { 'Selesai': 'is-done', 'In Progress': 'is-progress', 'Pending': 'is-pending', 'Penting': 'is-important' };
    var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    var state = {
        tab: root.getAttribute('data-initial-tab') || 'dashboard',
        form: false,
        year: '2026',
        plant: '',
        q: '',
        pages: { disetujui: 1, intern: 1, dibatalkan: 1, rekap: 1, form: 1, recap: 1 },
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
        var hay = [r.unit, r.description, r.notification, r.status, r.process, r.plant].join(' ').toLowerCase();
        return hay.indexOf(state.q) !== -1;
    }
    function listFor(key) {
        return rows.filter(function (r) {
            if (!matches(r)) { return false; }
            if (key === 'disetujui') { return r.status === 'Lanjut'; }
            if (key === 'intern') { return INTERNAL_STATUSES.indexOf(r.status) !== -1; }
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
                if (p === 2 || p === pages - 1) { html += '<span class="sj-pager-gap">…</span>'; }
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
        return totalItems === 0 ? 'Tidak ada data' : 'Menampilkan ' + from + '-' + to + ' dari ' + totalItems + ' ' + noun;
    }

    /* ---------- tabel ajuan ---------- */
    function noteCount(id) { return loadNotes(id).length; }

    function simpleRow(r, no, withProcess) {
        return '<tr data-id="' + esc(r.id) + '">' +
            '<td>' + no + '</td>' +
            '<td class="sj-unit-cell">' + esc(r.unit) + '</td>' +
            '<td><span class="sj-plant-tag">' + esc(r.plant) + '</span></td>' +
            '<td class="sj-mono">' + esc(r.notification) + '</td>' +
            '<td class="sj-desc">' + esc(r.description) + '</td>' +
            '<td class="is-num sj-strong">' + esc(r.duration) + '</td>' +
            '<td class="sj-muted">' + esc(r.uom_duration) + '</td>' +
            '<td class="is-num sj-strong">' + esc(r.qty) + '</td>' +
            '<td class="sj-muted">' + esc(r.uom_qty) + '</td>' +
            '<td class="is-num">' + money(r.price) + '</td>' +
            '<td class="is-num sj-total">' + money(total(r)) + '</td>' +
            '<td>' + statusBadge(r.status) + '</td>' +
            (withProcess ? '<td>' + processPill(r.process) + '</td>' : '') +
            '<td>' + noteButton(r, noteCount(r.id)) + '</td>' +
            '<td>' + editButton(r) + '</td></tr>';
    }
    function wideRow(r, no) {
        return '<tr data-id="' + esc(r.id) + '">' +
            '<td>' + no + '</td>' +
            '<td><span class="sj-plant-tag">' + esc(r.plant) + '</span></td>' +
            '<td class="sj-unit-cell">' + esc(r.unit) + '</td>' +
            '<td>' + esc(r.plant_section) + '</td>' +
            '<td class="sj-desc">' + esc(r.description) + '</td>' +
            '<td class="sj-mono">' + esc(r.equipment_id) + '</td>' +
            '<td class="is-num">' + esc(r.qty_bom) + '</td>' +
            '<td>' + esc(r.uom_bom) + '</td>' +
            '<td>' + esc(r.mrp_type) + '</td>' +
            '<td>' + esc(r.lifetime) + '</td>' +
            '<td class="sj-mono">' + esc(r.notification) + '</td>' +
            '<td class="sj-mono">' + esc(r.order) + '</td>' +
            '<td class="sj-mono">' + esc(r.reservation) + '</td>' +
            '<td class="sj-mono">' + esc(r.material_no) + '</td>' +
            '<td class="sj-mono">' + esc(r.po_ref) + '</td>' +
            '<td>' + esc(r.po_date) + '</td>' +
            '<td class="is-num">' + money(r.price) + '</td>' +
            '<td class="is-num">' + esc(r.qty) + '</td>' +
            '<td class="is-num sj-total">' + money(total(r)) + '</td>' +
            '<td>' + esc(r.pic) + '</td>' +
            '<td class="is-num">' + esc(r.qty_approved) + '</td>' +
            '<td><span class="sj-bom-tag ' + (r.bom_status === 'OK' ? 'is-ok' : 'is-no') + '">' + esc(r.bom_status) + '</span></td>' +
            '<td class="sj-evaluation">' + esc(r.evaluation_note || '-') + '</td>' +
            '<td>' + statusBadge(r.status) + '</td>' +
            '<td>' + processPill(r.process) + '</td>' +
            '<td>' + noteButton(r, noteCount(r.id)) + '</td>' +
            '<td>' + editButton(r) + '</td></tr>';
    }
    function statusBadge(status) {
        return '<span class="sj-status ' + (STATUS_CLASS[status] || '') + '"><i class="bi ' + (STATUS_ICON[status] || 'bi-dot') + '" aria-hidden="true"></i> ' + esc(status) + '</span>';
    }
    function processPill(process) {
        return '<span class="sj-process ' + (PROCESS_CLASS[process] || '') + '">' + esc(process || '-') + '</span>';
    }
    function noteButton(r, count) {
        return '<button type="button" class="sj-icon' + (count ? ' has-notes' : '') + '" data-sj-note-open="' + esc(r.id) + '" aria-label="Catatan ' + esc(r.description) + (count ? ' (' + count + ')' : '') + '"><i class="bi bi-chat-left-text"></i>' + (count ? '<b class="sj-count">' + count + '</b>' : '') + '</button>';
    }
    function editButton(r) {
        return '<button type="button" class="sj-icon is-edit" data-sj-edit="' + esc(r.id) + '" aria-label="Edit ' + esc(r.description) + '"><i class="bi bi-pencil-square"></i></button>';
    }

    function renderList(key) {
        var box = $('[data-sj-list="' + key + '"]');
        if (!box) { return; }
        var items = listFor(key === 'form' ? 'rekap' : key);
        var size = PAGE_SIZE[key];
        var page = clampPage(key, items.length, size);
        var start = (page - 1) * size;
        var slice = items.slice(start, start + size);
        var tbody = $('[data-sj-body]', box);
        tbody.innerHTML = slice.map(function (r, i) { return key === 'form' ? wideRow(r, start + i + 1) : simpleRow(r, start + i + 1, key === 'rekap'); }).join('');
        var empty = $('[data-sj-empty]', box);
        if (empty) { empty.hidden = items.length !== 0; }
        var sum = $('[data-sj-summary]', box);
        if (sum) { sum.textContent = summary(items.length ? start + 1 : 0, start + slice.length, items.length, 'data'); }
        renderPager($('[data-sj-pager]', box), items.length, page, size, function (p) {
            state.pages[key] = p;
            renderList(key);
        });
    }

    /* ---------- dashboard ---------- */
    function renderRecap() {
        var box = $('[data-sj-recap]');
        if (!box) { return; }
        var all = $$('[data-sj-recap-row]', box);
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
        var empty = $('[data-sj-recap-empty]', box);
        if (empty) { empty.hidden = visible.length !== 0; }
        var sum = $('[data-sj-recap-summary]', box);
        if (sum) { sum.textContent = summary(visible.length ? start + 1 : 0, Math.min(start + size, visible.length), visible.length, 'data'); }
        renderPager($('[data-sj-recap-pager]', box), visible.length, page, size, function (p) {
            state.pages.recap = p;
            renderRecap();
        });
        var toggle = $('[data-sj-recap-all]', box);
        if (toggle) { toggle.innerHTML = (state.recapAll ? 'Lihat Ringkas' : 'Lihat Semua') + ' <i class="bi bi-arrow-right" aria-hidden="true"></i>'; }
        $$('[data-sj-plant-row]').forEach(function (li) {
            li.classList.toggle('is-dim', !!state.plant && li.getAttribute('data-sj-plant-row') !== state.plant);
        });
    }

    /* ---------- tab & mode ---------- */
    function currentMode() {
        if (state.tab === 'dashboard') { return 'dashboard'; }
        return state.tab === 'rekap' && state.form ? 'form' : 'list';
    }
    function applyMode() {
        var mode = currentMode();
        $$('[data-sj-show]').forEach(function (el) { el.hidden = el.getAttribute('data-sj-show') !== mode; });
        var list = $('[data-sj-rekap-list]');
        var form = $('[data-sj-rekap-form]');
        if (list) { list.hidden = state.form; }
        if (form) { form.hidden = !state.form; }
    }
    function setTab(key, focus) {
        state.tab = key;
        if (key !== 'rekap') { state.form = false; }
        $$('[data-sj-tab]').forEach(function (btn) {
            var on = btn.getAttribute('data-sj-tab') === key;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
            btn.tabIndex = on ? 0 : -1;
            if (on && focus) { btn.focus(); }
        });
        $$('[data-sj-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-sj-panel') !== key; });
        var hero = $('[data-sj-hero]');
        if (hero) { hero.hidden = key !== 'dashboard'; }
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
        ['disetujui', 'intern', 'dibatalkan', 'rekap', 'form'].forEach(renderList);
    }
    function resetPages() {
        Object.keys(state.pages).forEach(function (k) { state.pages[k] = 1; });
    }

    /* ---------- modal umum ---------- */
    function openModal(name, opener) {
        var modal = $('[data-sj-modal="' + name + '"]');
        if (!modal) { return null; }
        state.lastFocus = opener || document.activeElement;
        modal.hidden = false;
        document.body.classList.add('sj-modal-open');
        var first = modal.querySelector('input:not([readonly]):not([type=hidden]), textarea, select, button');
        if (first) { first.focus(); }
        return modal;
    }
    function closeModal(modal) {
        if (!modal || modal.hidden) { return; }
        modal.hidden = true;
        if (!$('[data-sj-modal]:not([hidden])')) { document.body.classList.remove('sj-modal-open'); }
        if (state.lastFocus && document.contains(state.lastFocus)) { state.lastFocus.focus(); }
    }
    function openModalEl() { return $('[data-sj-modal]:not([hidden])'); }

    /* ---------- modal edit ---------- */
    var editForm = $('[data-sj-edit-form]');

    function field(name) { return editForm.elements[name]; }
    function syncTotal() {
        field('total').value = money((Number(field('qty').value) || 0) * (Number(field('price').value) || 0));
    }
    function fillEdit(r) {
        var defaults = { year: state.year, plant: state.plant, status: 'Lanjut', uom_duration: 'HARI', uom_qty: 'LOT', duration: 1, qty: 1, price: 0 };
        Array.prototype.forEach.call(editForm.elements, function (el) {
            if (!el.name || el.name === 'total') { return; }
            var value = r ? r[el.name] : defaults[el.name];
            el.value = value == null ? '' : value;
        });
        syncTotal();
    }
    function openEdit(id, opener) {
        if (!editForm) { return; }
        var r = id ? findRow(id) : null;
        state.editId = r ? r.id : null;
        fillEdit(r);
        $('[data-sj-edit-mode]').textContent = r ? '(EDIT)' : '(TAMBAH)';
        $('[data-sj-edit-error]').hidden = true;
        openModal('edit', opener);
    }
    function saveEdit() {
        var err = $('[data-sj-edit-error]');
        var qty = Number(field('qty').value);
        var price = Number(field('price').value);
        var duration = Number(field('duration').value);
        var problems = [];
        if (!field('unit').value.trim()) { problems.push('Unit Kerja wajib diisi.'); }
        if (!field('description').value.trim()) { problems.push('Deskripsi Pekerjaan wajib diisi.'); }
        if (field('duration').value !== '' && !(duration >= 0)) { problems.push('Durasi tidak valid.'); }
        if (!(qty > 0)) { problems.push('Quantity harus lebih dari 0.'); }
        if (field('price').value === '' || !(price >= 0)) { problems.push('Harga satuan wajib diisi.'); }
        if (problems.length) {
            err.textContent = problems.join(' ');
            err.hidden = false;
            return;
        }
        var r = state.editId ? findRow(state.editId) : null;
        var isNew = !r;
        if (isNew) {
            r = {
                id: 'sj-new-' + Date.now(), year: Number(state.year), notes: [], plant_section: '', qty_bom: qty, uom_bom: 'UNIT', mrp_type: '-',
                lifetime: '-', order: '-', reservation: '-', material_no: '-', po_ref: '-', po_date: '-', pic: '-', qty_approved: qty, bom_status: 'OK'
            };
            rows.unshift(r);
        }
        Array.prototype.forEach.call(editForm.elements, function (el) {
            if (!el.name || el.name === 'total' || el.name === 'year') { return; }
            r[el.name] = el.value.trim();
        });
        r.qty = qty;
        r.price = price;
        r.duration = field('duration').value === '' ? 0 : duration;
        r.plant = r.plant || state.plant || guessPlant('');
        if (isNew) { r.plant_section = r.plant; r.qty_bom = qty; r.qty_approved = qty; }
        closeModal($('[data-sj-modal="edit"]'));
        refreshAll();
        toast(isNew ? 'Ajuan baru ditambahkan (pratinjau).' : 'Perubahan ajuan disimpan (pratinjau).');
    }
    function guessPlant(section) {
        var options = $$('[data-sj-plant] option').map(function (o) { return o.value; }).filter(Boolean);
        return options.indexOf(section) !== -1 ? section : (options[3] || options[0] || '');
    }

    /* ---------- modal catatan (localStorage) ---------- */
    var noteForm = $('[data-sj-note-form]');
    var noteCache = {};

    function storageKey(id) { return 'sj-notes:' + id; }
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
        var q = ($('[data-sj-note-search]').value || '').trim().toLowerCase();
        var st = $('[data-sj-note-filter]').value;
        return loadNotes(state.noteRowId).filter(function (n) {
            return (!st || n.status === st) && (!q || (n.text || '').toLowerCase().indexOf(q) !== -1);
        });
    }
    function renderNotes() {
        var all = loadNotes(state.noteRowId);
        var shown = noteFilter();
        var body = $('[data-sj-note-body]');
        body.innerHTML = shown.map(function (n, i) {
            var long = (n.text || '').length > 70;
            return '<tr data-note="' + esc(n.id) + '">' +
                '<td>' + (i + 1) + '</td>' +
                '<td class="sj-mono"><i class="bi bi-calendar3" aria-hidden="true"></i> ' + esc(dateLabel(n.date)) + '</td>' +
                '<td><span class="sj-note-text" data-full="' + esc(n.text) + '">' + esc(long ? n.text.slice(0, 70) + '…' : n.text) + '</span>' +
                (long ? '<button type="button" class="sj-readmore" data-sj-note-more aria-expanded="false">Read More +</button>' : '') + '</td>' +
                '<td><span class="sj-note-status ' + (NOTE_CLASS[n.status] || '') + '">' + esc(n.status) + '</span></td>' +
                '<td class="sj-note-actions">' +
                '<button type="button" data-sj-note-edit="' + esc(n.id) + '" aria-label="Ubah catatan"><i class="bi bi-pencil-square"></i></button>' +
                '<button type="button" data-sj-note-del="' + esc(n.id) + '" aria-label="Hapus catatan"><i class="bi bi-trash3"></i></button>' +
                '<button type="button" class="' + (n.status === 'Selesai' ? 'is-done' : '') + '" data-sj-note-done="' + esc(n.id) + '" aria-label="Tandai selesai" aria-pressed="' + (n.status === 'Selesai') + '"><i class="bi ' + (n.status === 'Selesai' ? 'bi-check-circle-fill' : 'bi-circle') + '"></i></button>' +
                '</td></tr>';
        }).join('');
        $('[data-sj-note-empty]').hidden = shown.length !== 0;
        $('[data-sj-note-total]').textContent = all.length;
        $('[data-sj-note-donecount]').textContent = all.filter(function (n) { return n.status === 'Selesai'; }).length;
        $('[data-sj-note-summary]').textContent = 'Menampilkan ' + shown.length + ' dari ' + all.length + ' data catatan';
    }
    function resetNoteForm() {
        state.noteEditId = null;
        noteForm.reset();
        noteForm.elements.date.value = todayIso();
        $('[data-sj-note-form-title]').textContent = 'TAMBAH REMARK BARU';
        $('[data-sj-note-submit]').textContent = 'Simpan Remark';
        $('[data-sj-note-cancel]').hidden = true;
        $('[data-sj-note-error]').hidden = true;
    }
    function openNotes(id, opener) {
        var r = findRow(id);
        if (!r || !noteForm) { return; }
        state.noteRowId = id;
        $('[data-sj-note-context]').textContent = '— ' + r.description;
        $('[data-sj-note-search]').value = '';
        $('[data-sj-note-filter]').value = '';
        resetNoteForm();
        renderNotes();
        openModal('note', opener);
    }
    function submitNote() {
        var date = noteForm.elements.date.value;
        var text = noteForm.elements.text.value.trim();
        var status = noteForm.elements.status.value;
        var err = $('[data-sj-note-error]');
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
        var head = ['No', 'Unit Kerja', 'Plant', 'Notifikasi', 'Description Pekerjaan', 'Durasi', 'OUM', 'Qty', 'OUM', 'Harga Satuan', 'Total Harga', 'Status Ajuan', 'Proses Pengadaan'];
        var lines = [head.map(csvCell).join(',')];
        items.forEach(function (r, i) {
            lines.push([i + 1, r.unit, r.plant, r.notification, r.description, r.duration, r.uom_duration, r.qty, r.uom_qty, r.price, total(r), r.status, r.process].map(csvCell).join(','));
        });
        download('rekap-ajuan-jasa-' + state.year + '.csv', lines.join('\r\n'));
        toast('Export dimulai (' + items.length + ' baris).');
    }

    /* ---------- event ---------- */
    root.addEventListener('click', function (e) {
        var t = e.target;
        var tab = t.closest('[data-sj-tab]');
        if (tab) { setTab(tab.getAttribute('data-sj-tab')); return; }
        var edit = t.closest('[data-sj-edit]');
        if (edit) { openEdit(edit.getAttribute('data-sj-edit'), edit); return; }
        var note = t.closest('[data-sj-note-open]');
        if (note) { openNotes(note.getAttribute('data-sj-note-open'), note); return; }
        if (t.closest('[data-sj-close]')) { closeModal(t.closest('[data-sj-modal]')); return; }

        if (t.closest('[data-sj-add]')) { state.form = true; setTab('rekap'); return; }
        if (t.closest('[data-sj-back]')) { state.form = false; applyMode(); renderActive(); return; }
        if (t.closest('[data-sj-new]')) { openEdit(null, t.closest('[data-sj-new]')); return; }
        if (t.closest('[data-sj-export]')) { exportCsv(); return; }
        if (t.closest('[data-sj-print]')) { window.print(); return; }
        if (t.closest('[data-sj-import]')) { $('[data-sj-import-input]').click(); return; }
        if (t.closest('[data-sj-draft]')) { toast('Draft tersimpan (pratinjau).'); return; }
        if (t.closest('[data-sj-send]')) {
            var count = listFor('rekap').length;
            ask('Kirim ajuan?', count + ' ajuan akan dikirim untuk evaluasi (pratinjau).').then(function (ok) { if (ok) { toast('Ajuan dikirim (pratinjau).'); } });
            return;
        }
        if (t.closest('[data-sj-refresh]')) {
            var btn = t.closest('[data-sj-refresh]');
            btn.classList.add('is-spinning');
            setTimeout(function () {
                btn.classList.remove('is-spinning');
                $('[data-sj-updated]').textContent = nowLabel();
                refreshAll();
            }, 700);
            return;
        }
        if (t.closest('[data-sj-recap-all]')) { state.recapAll = !state.recapAll; state.pages.recap = 1; renderRecap(); return; }
        var more = t.closest('[data-sj-readmore]');
        if (more) {
            var detail = more.parentNode.querySelector('.sj-note-detail');
            var open = detail.hidden;
            detail.hidden = !open;
            more.setAttribute('aria-expanded', open ? 'true' : 'false');
            more.innerHTML = (open ? 'Show Less ' : 'Read More ') + '<i class="bi ' + (open ? 'bi-caret-down-fill' : 'bi-caret-right-fill') + '" aria-hidden="true"></i>';
            return;
        }
        var toggle = t.closest('[data-sj-notes-toggle]');
        if (toggle) {
            var extras = $$('[data-extra]', toggle.closest('[data-sj-notes]'));
            var expanded = toggle.getAttribute('data-expanded') === '1';
            extras.forEach(function (li) { li.hidden = expanded; });
            toggle.setAttribute('data-expanded', expanded ? '0' : '1');
            toggle.textContent = expanded
                ? 'Menampilkan 5 dari ' + toggle.getAttribute('data-total') + ' catatan evaluasi'
                : 'Menampilkan semua ' + toggle.getAttribute('data-total') + ' catatan evaluasi';
            return;
        }

        if (t.closest('[data-sj-reset]')) { fillEdit(state.editId ? findRow(state.editId) : null); return; }

        /* catatan */
        var nEdit = t.closest('[data-sj-note-edit]');
        if (nEdit) {
            var n = loadNotes(state.noteRowId).filter(function (x) { return x.id === nEdit.getAttribute('data-sj-note-edit'); })[0];
            if (n) {
                state.noteEditId = n.id;
                noteForm.elements.date.value = n.date;
                noteForm.elements.status.value = n.status;
                noteForm.elements.text.value = n.text;
                $('[data-sj-note-form-title]').textContent = 'UBAH REMARK';
                $('[data-sj-note-submit]').textContent = 'Perbarui Remark';
                $('[data-sj-note-cancel]').hidden = false;
                noteForm.elements.text.focus();
            }
            return;
        }
        var nDel = t.closest('[data-sj-note-del]');
        if (nDel) {
            var delId = nDel.getAttribute('data-sj-note-del');
            ask('Hapus catatan?', 'Catatan ini akan dihapus dari perangkat ini.').then(function (ok) {
                if (!ok) { return; }
                noteCache[state.noteRowId] = loadNotes(state.noteRowId).filter(function (x) { return x.id !== delId; });
                saveNotes(state.noteRowId);
                renderNotes();
                refreshAll();
            });
            return;
        }
        var nDone = t.closest('[data-sj-note-done]');
        if (nDone) {
            var doneId = nDone.getAttribute('data-sj-note-done');
            loadNotes(state.noteRowId).forEach(function (x) { if (x.id === doneId) { x.status = x.status === 'Selesai' ? 'Pending' : 'Selesai'; } });
            saveNotes(state.noteRowId);
            renderNotes();
            return;
        }
        var nMore = t.closest('[data-sj-note-more]');
        if (nMore) {
            var span = nMore.parentNode.querySelector('.sj-note-text');
            var expand = nMore.getAttribute('aria-expanded') !== 'true';
            span.textContent = expand ? span.getAttribute('data-full') : span.getAttribute('data-full').slice(0, 70) + '…';
            nMore.setAttribute('aria-expanded', expand ? 'true' : 'false');
            nMore.textContent = expand ? 'Show Less -' : 'Read More +';
            return;
        }
        if (t.closest('[data-sj-note-cancel]')) { resetNoteForm(); return; }
        if (t.closest('[data-sj-note-export]')) { noteCsv(); return; }
        if (t.closest('[data-sj-note-print]')) {
            document.body.classList.add('sj-print-note');
            window.print();
            setTimeout(function () { document.body.classList.remove('sj-print-note'); }, 300);
        }
    });

    root.addEventListener('keydown', function (e) {
        var tab = e.target.closest ? e.target.closest('[data-sj-tab]') : null;
        if (tab && (e.key === 'ArrowRight' || e.key === 'ArrowLeft')) {
            var tabs = $$('[data-sj-tab]');
            var idx = tabs.indexOf(tab) + (e.key === 'ArrowRight' ? 1 : -1);
            var next = tabs[(idx + tabs.length) % tabs.length];
            setTab(next.getAttribute('data-sj-tab'), true);
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

    $('[data-sj-year]').addEventListener('change', function (e) { state.year = e.target.value; resetPages(); refreshAll(); });
    $('[data-sj-plant]').addEventListener('change', function (e) { state.plant = e.target.value; resetPages(); refreshAll(); });
    var searchTimer = null;
    $('[data-sj-search]').addEventListener('input', function (e) {
        var value = e.target.value.trim().toLowerCase();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { state.q = value; resetPages(); refreshAll(); }, 150);
    });

    $('[data-sj-import-input]').addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        if (file) { toast('File "' + file.name + '" dipilih (pratinjau, belum diproses).', 'info'); }
        e.target.value = '';
    });

    if (editForm) {
        editForm.addEventListener('submit', function (e) { e.preventDefault(); saveEdit(); });
        ['qty', 'price'].forEach(function (name) { field(name).addEventListener('input', syncTotal); });
    }
    if (noteForm) {
        noteForm.addEventListener('submit', function (e) { e.preventDefault(); submitNote(); });
        $('[data-sj-note-search]').addEventListener('input', renderNotes);
        $('[data-sj-note-filter]').addEventListener('change', renderNotes);
    }

    /* ---------- init ---------- */
    $$('[data-sj-tab]').forEach(function (btn) { btn.tabIndex = btn.classList.contains('is-active') ? 0 : -1; });
    applyMode();
    refreshAll();
})();
