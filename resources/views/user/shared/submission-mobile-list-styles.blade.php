@once
@push('styles')
<style>
    .submission-mobile-list { display: none; }
    @media (max-width: 767.98px) {
        .commissioning-list-panel { padding: .9rem; }
        .commissioning-list-toolbar { gap: .75rem; }
        .commissioning-list-toolbar h2 { font-size: 1rem; }
        .commissioning-toolbar-actions { width: 100%; }
        .commissioning-toolbar-actions .btn { flex: 1 1 auto; }
        .commissioning-filter-bar {
            align-items: stretch;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: .45rem;
            justify-content: stretch;
        }
        .commissioning-filter-bar label { grid-column: 1 / -1; }
        .commissioning-filter-bar .form-select { width: 100%; }
        .submission-mobile-list {
            display: grid;
            gap: .8rem;
        }
        .submission-mobile-card {
            border: 1px solid #dbe3ef;
            border-radius: .75rem;
            background: #fff;
            padding: .75rem;
            box-shadow: 0 .65rem 1.5rem rgba(15, 23, 42, .07);
        }
        .submission-mobile-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .6rem;
            padding-bottom: .55rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .submission-mobile-form {
            color: #172033;
            font-size: .9rem;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }
        .submission-mobile-template {
            color: #64748b;
            font-size: .74rem;
            margin-top: .15rem;
        }
        .submission-mobile-main {
            display: grid;
            gap: .5rem .6rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            padding: .6rem 0;
        }
        .submission-mobile-main > div {
            min-width: 0;
        }
        .submission-mobile-main span {
            color: #64748b;
            display: block;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .submission-mobile-main strong {
            color: #172033;
            display: block;
            font-size: .82rem;
            margin-top: .1rem;
            overflow-wrap: anywhere;
        }
        .submission-mobile-main small {
            color: #64748b;
            display: block;
            font-size: .72rem;
            margin-top: .05rem;
            overflow-wrap: anywhere;
        }
        .submission-mobile-approval {
            align-items: flex-start;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: .6rem;
            color: #475569;
            display: flex;
            gap: .5rem;
            font-size: .8rem;
            line-height: 1.35;
            margin-bottom: .6rem;
            padding: .5rem .6rem;
        }
        .submission-mobile-active-step {
            display: block;
            margin-top: .1rem;
            overflow-wrap: anywhere;
        }
        .submission-mobile-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
        }
        .submission-mobile-actions .btn,
        .submission-mobile-actions form {
            flex: 1 1 calc(50% - .45rem);
        }
        .submission-mobile-actions form .btn {
            width: 100%;
        }
        .submission-mobile-empty {
            border: 1px dashed #cbd5e1;
            border-radius: .75rem;
            color: #64748b;
            padding: 1.2rem;
            text-align: center;
        }
    }
</style>
@endpush
@endonce
