/*
 * Rencana Kerja Barang (frontend interaktif, data dummy dari window.SB_DATA).
 * Perubahan hanya hidup di memori halaman; catatan (note) disimpan di localStorage per ajuan.
 * Skrip toleran terhadap elemen yang tidak ada karena dimuat oleh halaman yang dirender server-side.
 */
(function () {
    'use strict';

    var root = document.querySelector('[data-sb]');
    if (!root || !window.SB_DATA || !Array.isArray(window.SB_DATA.rows)) {
        return;
    }

    var rows = window.SB_DATA.rows;
    var PAGE_SIZE = { disetujui: 15, dibatalkan: 15, rekap: 15, form: 15 };
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
        var hay = [r.unit, r.project, r.account, r.spec, r.status, r.plant].join(' ').toLowerCase();
        return hay.indexOf(state.q) !== -1;
    }
    function listFor(key) {
        return rows.filter(function (r) {
            if (!matches(r)) { return false; }
            if (key === 'disetujui') { return r.status === 'Lanjut'; }
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
                if (p === 2 || p === pages - 1) { html += '<span class="sb-pager-gap">…</span>'; }
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

    function simpleRow(r, no) {
        var notes = noteCount(r.id);
        return '<tr data-id="' + esc(r.id) + '">' +
            '<td>' + no + '</td>' +
            '<td><span class="sb-plant-tag">' + esc(r.plant) + '</span></td>' +
            '<td class="sb-strong">' + esc(r.unit) + '</td>' +
            '<td class="sb-strong">' + esc(r.project) + '</td>' +
            '<td class="sb-mono">' + esc(r.account) + '</td>' +
            '<td class="sb-spec">' + esc(r.spec) + '</td>' +
            '<td class="is-num sb-strong">' + esc(r.qty) + '</td>' +
            '<td>' + esc(r.uom) + '</td>' +
            '<td class="is-num">' + money(r.price) + '</td>' +
            '<td class="is-num sb-total">' + money(total(r)) + '</td>' +
            '<td>' + statusBadge(r.status) + '</td>' +
            '<td>' + noteButton(r, notes) + '</td>' +
            '<td>' + editButton(r) + '</td></tr>';
    }
    function wideRow(r, no) {
        return '<tr data-id="' + esc(r.id) + '">' +
            '<td>' + no + '</td>' +
            '<td><span class="sb-plant-tag">' + esc(r.plant) + '</span></td>' +
            '<td class="sb-strong">' + esc(r.unit) + '</td>' +
            '<td>' + esc(r.plant_section) + '</td>' +
            '<td class="sb-strong">' + esc(r.project) + '</td>' +
            '<td class="sb-mono">' + esc(r.equipment_id) + '</td>' +
            '<td class="is-num">' + esc(r.qty_bom) + '</td>' +
            '<td>' + esc(r.uom_bom) + '</td>' +
            '<td>' + esc(r.mrp_type) + '</td>' +
            '<td>' + esc(r.lifetime) + '</td>' +
            '<td class="sb-mono">' + esc(r.notification) + '</td>' +
            '<td class="sb-mono">' + esc(r.order) + '</td>' +
            '<td class="sb-mono">' + esc(r.reservation) + '</td>' +
            '<td class="sb-mono">' + esc(r.material_no) + '</td>' +
            '<td class="sb-mono">' + esc(r.po_ref) + '</td>' +
            '<td>' + esc(r.po_date) + '</td>' +
            '<td class="is-num">' + money(r.price) + '</td>' +
            '<td class="is-num">' + esc(r.qty) + '</td>' +
            '<td class="is-num sb-total">' + money(total(r)) + '</td>' +
            '<td>' + esc(r.pic) + '</td>' +
            '<td class="is-num">' + esc(r.qty_approved) + '</td>' +
            '<td><span class="sb-bom-tag ' + (r.bom_status === 'OK' ? 'is-ok' : 'is-no') + '">' + esc(r.bom_status) + '</span></td>' +
            '<td class="sb-evaluation">' + esc(r.evaluation_note) + '</td>' +
            '<td>' + statusBadge(r.status) + '</td>' +
            '<td>' + noteButton(r, noteCount(r.id)) + '</td>' +
            '<td>' + editButton(r) + '</td></tr>';
    }
    function statusBadge(status) {
        return '<span class="sb-status ' + (STATUS_CLASS[status] || '') + '">' + esc(status) + '</span>';
    }
    function noteButton(r, count) {
        return '<button type="button" class="sb-icon' + (count ? ' has-notes' : '') + '" data-sb-note-open="' + esc(r.id) + '" aria-label="Catatan ' + esc(r.project) + (count ? ' (' + count + ')' : '') + '"><i class="bi bi-chat-left-text"></i></button>';
    }
    function editButton(r) {
        return '<button type="button" class="sb-icon is-edit" data-sb-edit="' + esc(r.id) + '" aria-label="Edit ' + esc(r.project) + '"><i class="bi bi-pencil-square"></i></button>';
    }

    function renderList(key) {
        var box = $('[data-sb-list="' + key + '"]');
        if (!box) { return; }
        var items = listFor(key === 'form' ? 'rekap' : key);
        var size = PAGE_SIZE[key];
        var page = clampPage(key, items.length, size);
        var start = (page - 1) * size;
        var slice = items.slice(start, start + size);
        var tbody = $('[data-sb-body]', box);
        tbody.innerHTML = slice.map(function (r, i) { return key === 'form' ? wideRow(r, start + i + 1) : simpleRow(r, start + i + 1); }).join('');
        var empty = $('[data-sb-empty]', box);
        if (empty) { empty.hidden = items.length !== 0; }
        var sum = $('[data-sb-summary]', box);
        if (sum) { sum.textContent = summary(items.length ? start + 1 : 0, start + slice.length, items.length, 'data'); }
        renderPager($('[data-sb-pager]', box), items.length, page, size, function (p) {
            state.pages[key] = p;
            renderList(key);
        });
    }

    /* ---------- dashboard ---------- */
    function renderRecap() {
        var box = $('[data-sb-recap]');
        if (!box) { return; }
        var all = $$('[data-sb-recap-row]', box);
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
        var empty = $('[data-sb-recap-empty]', box);
        if (empty) { empty.hidden = visible.length !== 0; }
        var sum = $('[data-sb-recap-summary]', box);
        if (sum) { sum.textContent = summary(visible.length ? start + 1 : 0, Math.min(start + size, visible.length), visible.length, 'data'); }
        renderPager($('[data-sb-recap-pager]', box), visible.length, page, size, function (p) {
            state.pages.recap = p;
            renderRecap();
        });
        var toggle = $('[data-sb-recap-all]', box);
        if (toggle) { toggle.innerHTML = (state.recapAll ? 'Lihat Ringkas' : 'Lihat Semua') + ' <i class="bi bi-arrow-right" aria-hidden="true"></i>'; }
        $$('[data-sb-plant-row]').forEach(function (li) {
            li.classList.toggle('is-dim', !!state.plant && li.getAttribute('data-sb-plant-row') !== state.plant);
        });
    }

    /* ---------- tab & mode ---------- */
    function currentMode() {
        if (state.tab === 'dashboard') { return 'dashboard'; }
        return state.tab === 'rekap' && state.form ? 'form' : 'list';
    }
    function applyMode() {
        var mode = currentMode();
        $$('[data-sb-show]').forEach(function (el) { el.hidden = el.getAttribute('data-sb-show') !== mode; });
        var list = $('[data-sb-rekap-list]');
        var form = $('[data-sb-rekap-form]');
        if (list) { list.hidden = state.form; }
        if (form) { form.hidden = !state.form; }
    }
    function setTab(key, focus) {
        state.tab = key;
        if (key !== 'rekap') { state.form = false; }
        $$('[data-sb-tab]').forEach(function (btn) {
            var on = btn.getAttribute('data-sb-tab') === key;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
            btn.tabIndex = on ? 0 : -1;
            if (on && focus) { btn.focus(); }
        });
        $$('[data-sb-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-sb-panel') !== key; });
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
        var modal = $('[data-sb-modal="' + name + '"]');
        if (!modal) { return null; }
        state.lastFocus = opener || document.activeElement;
        modal.hidden = false;
        document.body.classList.add('sb-modal-open');
        var first = modal.querySelector('input:not([readonly]):not([type=hidden]), textarea, select, button');
        if (first) { first.focus(); }
        return modal;
    }
    function closeModal(modal) {
        if (!modal || modal.hidden) { return; }
        modal.hidden = true;
        if (!$('[data-sb-modal]:not([hidden])')) { document.body.classList.remove('sb-modal-open'); }
        if (state.lastFocus && document.contains(state.lastFocus)) { state.lastFocus.focus(); }
    }
    function openModalEl() { return $('[data-sb-modal]:not([hidden])'); }

    /* ---------- modal edit ---------- */
    var editForm = $('[data-sb-edit-form]');
    var longText = editForm ? $('[data-sb-longtext]', editForm) : null;

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
        $('[data-sb-edit-mode]').textContent = r ? '(EDIT)' : '(TAMBAH)';
        $('[data-sb-edit-error]').hidden = true;
        syncTotal();
        openModal('edit', opener);
    }
    function saveEdit() {
        var err = $('[data-sb-edit-error]');
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
            r = { id: 'sb-new-' + Date.now(), account: '-', notes: [] };
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
        r.spec = r.short_text || r.spec || '';
        r.project = r.program || r.project || '-';
        if (isNew) {
            r.plant = state.plant || guessPlant(r.plant_section);
            if (!r.pic) { r.pic = '-'; }
        }
        closeModal($('[data-sb-modal="edit"]'));
        refreshAll();
        toast(isNew ? 'Ajuan baru ditambahkan (pratinjau).' : 'Perubahan ajuan disimpan (pratinjau).');
    }
    function guessPlant(section) {
        var options = $$('[data-sb-plant] option').map(function (o) { return o.value; }).filter(Boolean);
        return options.indexOf(section) !== -1 ? section : (options[3] || options[0] || '');
    }

    /* ---------- modal catatan (localStorage) ---------- */
    var noteForm = $('[data-sb-note-form]');
    var noteCache = {};

    function storageKey(id) { return 'sb-notes:' + id; }
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
        var q = ($('[data-sb-note-search]').value || '').trim().toLowerCase();
        var st = $('[data-sb-note-filter]').value;
        return loadNotes(state.noteRowId).filter(function (n) {
            return (!st || n.status === st) && (!q || (n.text || '').toLowerCase().indexOf(q) !== -1);
        });
    }
    function renderNotes() {
        var all = loadNotes(state.noteRowId);
        var shown = noteFilter();
        var body = $('[data-sb-note-body]');
        body.innerHTML = shown.map(function (n, i) {
            var long = (n.text || '').length > 70;
            return '<tr data-note="' + esc(n.id) + '">' +
                '<td>' + (i + 1) + '</td>' +
                '<td class="sb-mono"><i class="bi bi-calendar3" aria-hidden="true"></i> ' + esc(dateLabel(n.date)) + '</td>' +
                '<td><span class="sb-note-text" data-full="' + esc(n.text) + '">' + esc(long ? n.text.slice(0, 70) + '…' : n.text) + '</span>' +
                (long ? '<button type="button" class="sb-readmore" data-sb-note-more aria-expanded="false">Read More +</button>' : '') + '</td>' +
                '<td><span class="sb-note-status ' + (NOTE_CLASS[n.status] || '') + '">' + esc(n.status) + '</span></td>' +
                '<td class="sb-note-actions">' +
                '<button type="button" data-sb-note-edit="' + esc(n.id) + '" aria-label="Ubah catatan"><i class="bi bi-pencil-square"></i></button>' +
                '<button type="button" data-sb-note-del="' + esc(n.id) + '" aria-label="Hapus catatan"><i class="bi bi-trash3"></i></button>' +
                '<button type="button" class="' + (n.status === 'Selesai' ? 'is-done' : '') + '" data-sb-note-done="' + esc(n.id) + '" aria-label="Tandai selesai" aria-pressed="' + (n.status === 'Selesai') + '"><i class="bi ' + (n.status === 'Selesai' ? 'bi-check-circle-fill' : 'bi-circle') + '"></i></button>' +
                '</td></tr>';
        }).join('');
        $('[data-sb-note-empty]').hidden = shown.length !== 0;
        $('[data-sb-note-total]').textContent = all.length;
        $('[data-sb-note-donecount]').textContent = all.filter(function (n) { return n.status === 'Selesai'; }).length;
        $('[data-sb-note-summary]').textContent = 'Menampilkan ' + shown.length + ' dari ' + all.length + ' data catatan';
    }
    function resetNoteForm() {
        state.noteEditId = null;
        noteForm.reset();
        noteForm.elements.date.value = todayIso();
        $('[data-sb-note-form-title]').textContent = 'TAMBAH REMARK BARU';
        $('[data-sb-note-submit]').textContent = 'Simpan Remark';
        $('[data-sb-note-cancel]').hidden = true;
        $('[data-sb-note-error]').hidden = true;
    }
    function openNotes(id, opener) {
        var r = findRow(id);
        if (!r || !noteForm) { return; }
        state.noteRowId = id;
        $('[data-sb-note-context]').textContent = '— ' + r.project;
        $('[data-sb-note-search]').value = '';
        $('[data-sb-note-filter]').value = '';
        resetNoteForm();
        renderNotes();
        openModal('note', opener);
    }
    function submitNote() {
        var date = noteForm.elements.date.value;
        var text = noteForm.elements.text.value.trim();
        var status = noteForm.elements.status.value;
        var err = $('[data-sb-note-error]');
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
        var head = ['No', 'Plant', 'Unit Kerja', 'Deskripsi Proyek', 'No. Akun', 'Spesifikasi', 'Qty', 'UoM', 'Harga Unit', 'Total Harga', 'Status Ajuan'];
        var lines = [head.map(csvCell).join(',')];
        items.forEach(function (r, i) {
            lines.push([i + 1, r.plant, r.unit, r.project, r.account, r.spec, r.qty, r.uom, r.price, total(r), r.status].map(csvCell).join(','));
        });
        download('rekap-ajuan-barang-' + state.year + '.csv', lines.join('\r\n'));
        toast('Export dimulai (' + items.length + ' baris).');
    }

    /* ---------- event ---------- */
    root.addEventListener('click', function (e) {
        var t = e.target;
        var tab = t.closest('[data-sb-tab]');
        if (tab) { setTab(tab.getAttribute('data-sb-tab')); return; }
        var edit = t.closest('[data-sb-edit]');
        if (edit) { openEdit(edit.getAttribute('data-sb-edit'), edit); return; }
        var note = t.closest('[data-sb-note-open]');
        if (note) { openNotes(note.getAttribute('data-sb-note-open'), note); return; }
        if (t.closest('[data-sb-close]')) { closeModal(t.closest('[data-sb-modal]')); return; }

        if (t.closest('[data-sb-add]')) { state.form = true; setTab('rekap'); return; }
        if (t.closest('[data-sb-back]')) { state.form = false; applyMode(); renderActive(); return; }
        if (t.closest('[data-sb-new]')) { openEdit(null, t.closest('[data-sb-new]')); return; }
        if (t.closest('[data-sb-export]')) { exportCsv(); return; }
        if (t.closest('[data-sb-print]')) { window.print(); return; }
        if (t.closest('[data-sb-import]')) { $('[data-sb-import-input]').click(); return; }
        if (t.closest('[data-sb-draft]')) { toast('Draft tersimpan (pratinjau).'); return; }
        if (t.closest('[data-sb-send]')) {
            var count = listFor('rekap').length;
            ask('Kirim ajuan?', count + ' ajuan akan dikirim untuk evaluasi (pratinjau).').then(function (ok) { if (ok) { toast('Ajuan dikirim (pratinjau).'); } });
            return;
        }
        if (t.closest('[data-sb-refresh]')) {
            var btn = t.closest('[data-sb-refresh]');
            btn.classList.add('is-spinning');
            setTimeout(function () {
                btn.classList.remove('is-spinning');
                $('[data-sb-updated]').textContent = nowLabel();
                refreshAll();
            }, 700);
            return;
        }
        if (t.closest('[data-sb-recap-all]')) { state.recapAll = !state.recapAll; state.pages.recap = 1; renderRecap(); return; }
        var more = t.closest('[data-sb-readmore]');
        if (more) {
            var detail = more.parentNode.querySelector('.sb-note-detail');
            var open = detail.hidden;
            detail.hidden = !open;
            more.setAttribute('aria-expanded', open ? 'true' : 'false');
            more.innerHTML = (open ? 'Show Less ' : 'Read More ') + '<i class="bi ' + (open ? 'bi-caret-down-fill' : 'bi-caret-right-fill') + '" aria-hidden="true"></i>';
            return;
        }
        var toggle = t.closest('[data-sb-notes-toggle]');
        if (toggle) {
            var extras = $$('[data-extra]', toggle.closest('[data-sb-notes]'));
            var expanded = toggle.getAttribute('data-expanded') === '1';
            extras.forEach(function (li) { li.hidden = expanded; });
            toggle.setAttribute('data-expanded', expanded ? '0' : '1');
            toggle.textContent = expanded
                ? 'Menampilkan 5 dari ' + toggle.getAttribute('data-total') + ' catatan evaluasi'
                : 'Menampilkan semua ' + toggle.getAttribute('data-total') + ' catatan evaluasi';
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
        var nEdit = t.closest('[data-sb-note-edit]');
        if (nEdit) {
            var n = loadNotes(state.noteRowId).filter(function (x) { return x.id === nEdit.getAttribute('data-sb-note-edit'); })[0];
            if (n) {
                state.noteEditId = n.id;
                noteForm.elements.date.value = n.date;
                noteForm.elements.status.value = n.status;
                noteForm.elements.text.value = n.text;
                $('[data-sb-note-form-title]').textContent = 'UBAH REMARK';
                $('[data-sb-note-submit]').textContent = 'Perbarui Remark';
                $('[data-sb-note-cancel]').hidden = false;
                noteForm.elements.text.focus();
            }
            return;
        }
        var nDel = t.closest('[data-sb-note-del]');
        if (nDel) {
            var delId = nDel.getAttribute('data-sb-note-del');
            ask('Hapus catatan?', 'Catatan ini akan dihapus dari perangkat ini.').then(function (ok) {
                if (!ok) { return; }
                noteCache[state.noteRowId] = loadNotes(state.noteRowId).filter(function (x) { return x.id !== delId; });
                saveNotes(state.noteRowId);
                renderNotes();
                refreshAll();
            });
            return;
        }
        var nDone = t.closest('[data-sb-note-done]');
        if (nDone) {
            var doneId = nDone.getAttribute('data-sb-note-done');
            loadNotes(state.noteRowId).forEach(function (x) { if (x.id === doneId) { x.status = x.status === 'Selesai' ? 'Pending' : 'Selesai'; } });
            saveNotes(state.noteRowId);
            renderNotes();
            return;
        }
        var nMore = t.closest('[data-sb-note-more]');
        if (nMore) {
            var span = nMore.parentNode.querySelector('.sb-note-text');
            var expand = nMore.getAttribute('aria-expanded') !== 'true';
            span.textContent = expand ? span.getAttribute('data-full') : span.getAttribute('data-full').slice(0, 70) + '…';
            nMore.setAttribute('aria-expanded', expand ? 'true' : 'false');
            nMore.textContent = expand ? 'Show Less -' : 'Read More +';
            return;
        }
        if (t.closest('[data-sb-note-cancel]')) { resetNoteForm(); return; }
        if (t.closest('[data-sb-note-export]')) { noteCsv(); return; }
        if (t.closest('[data-sb-note-print]')) {
            document.body.classList.add('sb-print-note');
            window.print();
            setTimeout(function () { document.body.classList.remove('sb-print-note'); }, 300);
        }
    });

    root.addEventListener('keydown', function (e) {
        var tab = e.target.closest ? e.target.closest('[data-sb-tab]') : null;
        if (tab && (e.key === 'ArrowRight' || e.key === 'ArrowLeft')) {
            var tabs = $$('[data-sb-tab]');
            var idx = tabs.indexOf(tab) + (e.key === 'ArrowRight' ? 1 : -1);
            var next = tabs[(idx + tabs.length) % tabs.length];
            setTab(next.getAttribute('data-sb-tab'), true);
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

    $('[data-sb-year]').addEventListener('change', function (e) { state.year = e.target.value; resetPages(); refreshAll(); });
    $('[data-sb-plant]').addEventListener('change', function (e) { state.plant = e.target.value; resetPages(); refreshAll(); });
    var searchTimer = null;
    $('[data-sb-search]').addEventListener('input', function (e) {
        var value = e.target.value.trim().toLowerCase();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { state.q = value; resetPages(); refreshAll(); }, 150);
    });

    $('[data-sb-import-input]').addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        if (file) { toast('File "' + file.name + '" dipilih (pratinjau, belum diproses).', 'info'); }
        e.target.value = '';
    });

    if (editForm) {
        editForm.addEventListener('submit', function (e) { e.preventDefault(); saveEdit(); });
        ['qty', 'price'].forEach(function (name) { field(name).addEventListener('input', syncTotal); });
        $('[data-sb-longtext]', editForm).addEventListener('paste', function (e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text');
            document.execCommand('insertText', false, text);
        });
        $$('.sb-rte-bar button', editForm).forEach(function (b) { b.addEventListener('mousedown', function (ev) { ev.preventDefault(); }); });
    }
    if (noteForm) {
        noteForm.addEventListener('submit', function (e) { e.preventDefault(); submitNote(); });
        $('[data-sb-note-search]').addEventListener('input', renderNotes);
        $('[data-sb-note-filter]').addEventListener('change', renderNotes);
    }

    /* ---------- init ---------- */
    $$('[data-sb-tab]').forEach(function (btn) { btn.tabIndex = btn.classList.contains('is-active') ? 0 : -1; });
    applyMode();
    refreshAll();
})();
