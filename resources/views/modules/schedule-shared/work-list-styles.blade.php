@push('styles')
<style>
    .schedule-work-list { --bwl-blue: #214f82; color: #233a55; display: grid; gap: 16px; min-width: 0; }
    .bwl-toolbar { display: grid; grid-template-columns: 110px 145px minmax(220px, 1fr) auto; align-items: end; gap: 20px; }
    .bwl-toolbar label { display: grid; gap: 7px; font-size: 12px; font-weight: 700; color: #26384a; }
    .bwl-toolbar .form-select, .bwl-toolbar .form-control { min-height: 39px; font-size: 12px; background-color: #f8fafc; border-color: #dae4ed; border-radius: 6px; }
    .bwl-search > span { position: relative; }
    .bwl-search i { position: absolute; left: 12px; top: 12px; color: #90a2b5; }
    .bwl-search input { padding-left: 33px; }
    .bwl-toolbar-actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .bwl-button { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 38px; padding: 8px 13px; border: 1px solid transparent; border-radius: 6px; font: inherit; font-size: 12px; font-weight: 600; white-space: nowrap; }
    .bwl-button-primary { background: var(--bwl-blue, #214f82); color: #fff; }
    .bwl-button-primary:hover { background: #173b65; }
    .bwl-button-light { border-color: #d5e0eb; background: #fff; color: #325578; }
    .bwl-button-light:hover, .bwl-button-light[aria-pressed="true"] { background: #edf3fa; border-color: #779abe; }
    .bwl-button-export { background: #edf7f0; border-color: #c8e2d0; color: #2d7047; }
    .bwl-button-export:hover { background: #deefe4; }
    .bwl-button:focus-visible, .bwl-icon:focus-visible, .bwl-page:focus-visible { outline: 3px solid #80aee0; outline-offset: 2px; }
    .bwl-preview-note { color: #718297; font-size: 11px; display: flex; align-items: baseline; gap: 7px; }
    .bwl-feedback { border: 1px solid #bfd9cb; background: #f0faf4; color: #256343; padding: 10px 14px; font-size: 12px; border-radius: 6px; }
    .bwl-card { border: 1px solid #dce5ee; background: #fff; min-width: 0; }
    .bwl-table-scroll { overflow: auto; max-height: 72vh; scrollbar-width: thin; }
    .bwl-table-scroll:focus-visible { outline: 2px solid #80aee0; outline-offset: -2px; }
    .bwl-table { width: 100%; min-width: 1280px; border-collapse: separate; border-spacing: 0; font-size: 11px; }
    .bwl-table th { position: sticky; top: 0; z-index: 1; padding: 13px 10px; background: #f6f9fc; color: #5b6f87; font-size: 9px; font-weight: 800; letter-spacing: .025em; text-transform: uppercase; border-bottom: 1px solid #d9e3ed; white-space: nowrap; }
    .bwl-table td { padding: 12px 10px; border-bottom: 1px solid #edf1f6; vertical-align: middle; }
    .bwl-table tbody tr:last-child td { border-bottom: 0; }
    .bwl-table tbody tr:nth-child(even) { background: #fbfcfe; }
    .bwl-table tbody tr:hover { background: #f1f6fc; }
    .bwl-table td:first-child { color: #7c8fa7; text-align: center; font-size: 10px; }
    .bwl-unit { min-width: 155px; font-weight: 600; }
    .bwl-plant { min-width: 77px; color: #315a87; font-weight: 600; }
    .bwl-description { min-width: 240px; max-width: 340px; line-height: 1.65; font-weight: 600; }
    .bwl-meta { min-width: 120px; font-size: 10px; color: #61758c; }
    .bwl-notification { font-variant-numeric: tabular-nums; white-space: nowrap; font-size: 10px; }
    .bwl-center { text-align: center; white-space: nowrap; }
    .bwl-number { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; font-weight: 600; }
    .bwl-total { color: #218464; }
    .bwl-cancelled .bwl-number { color: #94a3b8; }
    .bwl-pill { display: inline-flex; justify-content: center; align-items: center; gap: 5px; min-width: 60px; padding: 5px 8px; border-radius: 5px; font-size: 9px; font-weight: 700; white-space: nowrap; }
    .bwl-pill::before { content: ''; width: 4px; height: 4px; background: currentColor; border-radius: 50%; opacity: .7; }
    .bwl-pill.is-lanjut { color: #257d55; background: #e9f7ee; }
    .bwl-pill.is-pgo { color: #236d78; background: #e6f3f5; }
    .bwl-pill.is-ts { color: #284d8c; background: #eaf0fc; }
    .bwl-pill.is-bms { color: #9b6607; background: #fff3d6; }
    .bwl-pill.is-cancel { color: #c43c50; background: #feebee; }
    .bwl-pill.is-kontrak { color: #7945bc; background: #f2eafd; }
    .bwl-pill.is-fabrikasi { color: #a46512; background: #fff1dc; }
    .bwl-pill.is-tender { color: #2964ba; background: #eaf2ff; }
    .bwl-pill.is-done { color: #2d7253; background: #e7f5ed; }
    .bwl-pill.is-review { color: #526078; background: #edf0f5; }
    .bwl-icon { position: relative; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border: 1px solid #d4e1ef; border-radius: 6px; background: #f5f9fd; color: #406a99; font-size: 13px; }
    .bwl-icon:hover { background: #e6effa; border-color: #9bb9dc; }
    .bwl-icon-edit { color: #b27920; border-color: #eddbb9; background: #fffaf0; }
    .bwl-icon-edit:hover { background: #fff0d5; border-color: #d9b572; }
    .bwl-note-count { position: absolute; top: -6px; right: -6px; min-width: 15px; height: 15px; padding: 0 3px; border-radius: 10px; background: #329972; color: #fff; font-size: 9px; line-height: 15px; box-shadow: 0 0 0 2px #fff; }
    .bwl-empty { padding: 45px 20px; display: grid; justify-items: center; gap: 9px; color: #72839a; font-size: 12px; }
    .bwl-empty > i { font-size: 25px; }
    .bwl-empty strong { color: #3e5671; }
    .bwl-footer { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 14px 16px; border-top: 1px solid #e1e8ef; font-size: 11px; color: #627991; }
    .bwl-pagination-controls, .bwl-pagination-controls label, .bwl-pagination-controls nav { display: flex; align-items: center; gap: 7px; }
    .bwl-pagination-controls select { padding: 5px 7px; border: 1px solid #d5dfeb; border-radius: 5px; background: #fff; color: #355576; }
    .bwl-page { min-width: 30px; height: 30px; border: 1px solid #d5dfeb; border-radius: 5px; background: #fff; color: #32597e; }
    .bwl-page[aria-current="page"] { background: #214f82; border-color: #214f82; color: #fff; }
    .bwl-page:disabled { opacity: .4; cursor: not-allowed; }
    .schedule-work-list > [data-close-detail] { justify-self: start; }
    .bwl-modal .modal-content { border: 0; border-radius: 12px; overflow: hidden; }
    .bwl-modal .modal-header, .bwl-modal .modal-footer { padding: 18px 22px; border-color: #e1e8ef; }
    .bwl-modal .modal-title { font-size: 18px; font-weight: 700; color: #213e5f; }
    .bwl-modal .modal-header p { margin: 5px 18px 0 0; color: #75869a; font-size: 12px; }
    .bwl-modal .modal-body { padding: 22px; }
    .bwl-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .bwl-form label { display: grid; gap: 6px; font-size: 12px; font-weight: 600; color: #3e5672; }
    .bwl-modal .form-control, .bwl-modal .form-select { font-size: 13px; border-color: #d4dfeb; }
    .bwl-span-2 { grid-column: 1 / -1; }
    .bwl-form-total { padding: 15px; background: #eef6f3; border-radius: 6px; display: flex; justify-content: space-between; gap: 10px; color: #326752; font-size: 13px; }
    .bwl-form-total output { font-weight: 700; }
    .bwl-notes-list { display: grid; gap: 10px; margin-bottom: 22px; }
    .bwl-note { padding: 12px 14px; background: #f5f8fc; border: 1px solid #e0e8f1; border-radius: 8px; }
    .bwl-note p { margin: 0 0 9px; white-space: pre-wrap; overflow-wrap: anywhere; font-size: 13px; color: #304963; }
    .bwl-note-footer { display: flex; align-items: center; justify-content: space-between; color: #7f8fa1; font-size: 10px; }
    .bwl-note-footer button { background: transparent; color: #b34b5a; border: 0; padding: 4px; }
    .bwl-notes-empty { padding: 18px; border: 1px dashed #d6e0eb; border-radius: 7px; text-align: center; color: #7f8fa1; font-size: 12px; }
    .schedule-work-list [hidden] { display: none !important; }
    @media (max-width: 1199.98px) { .bwl-toolbar { grid-template-columns: 110px 145px minmax(200px, 1fr); } .bwl-toolbar-actions { grid-column: 1 / -1; } }
    @media (max-width: 575.98px) {
        .bwl-toolbar { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px; }
        .bwl-search { grid-column: 1 / -1; }
        .bwl-toolbar-actions { justify-content: stretch; }
        .bwl-toolbar-actions .bwl-button { flex: 1; }
        .bwl-pagination-controls { flex-wrap: wrap; width: 100%; justify-content: space-between; }
        .bwl-pagination-controls nav { flex-wrap: wrap; }
        .bwl-modal .modal-body { padding: 16px; }
        .bwl-form { grid-template-columns: minmax(0, 1fr); }
        .bwl-form-total { flex-wrap: wrap; }
    }
</style>
@endpush
