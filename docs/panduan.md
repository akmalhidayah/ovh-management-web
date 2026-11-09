# AGENTS.md

## Purpose

This repository is a Laravel 12 web application for overhaul management. Its implemented domains include QC inspections, commissioning forms, multi-step approval, master equipment data, organization sections, notifications, and role-aware admin/user dashboards. Treat submitted inspection documents, approval records, signatures, and attachment files as auditable business records.

These instructions apply to the entire repository. A more specific `AGENTS.md` in a child directory may override them for that subtree.

## Technology and Commands

- PHP 8.2+, Laravel 12, Eloquent, Blade, PHPUnit 11.
- Vite 7 and Tailwind CSS 4 provide the compiled frontend entry points.
- Bootstrap classes and repository-owned files under `public/assets/` are also used extensively; do not assume every page is Tailwind-only or Vite-only.
- PDF generation uses `barryvdh/laravel-dompdf`; PDF attachment merging uses `iio/libmergepdf`; spreadsheet handling uses PhpSpreadsheet.

Standard setup and verification commands:

```bash
composer setup
composer dev
composer test
./vendor/bin/pint --test
npm run build
```

Useful focused test commands:

```bash
php artisan test --filter=QcFormSubmissionTest
php artisan test --filter=CommissioningFormFlowTest
php artisan test --filter=AdminInspectionMetricsTest
php artisan test --filter=MultiAccessModeTest
php artisan test --filter=PasswordResetTest
```

If dependencies are already installed, do not run installation or dependency-update commands merely to inspect the project. Never claim a command passed if PHP, Composer, Node, npm, or `vendor/` is unavailable; report the verification gap explicitly.

## Repository Map

- `routes/web.php`: all HTTP routes and middleware groupings. Route closures are currently present, so do not assume `php artisan route:cache` is supported without first removing or validating every closure.
- `app/Http/Controllers/Admin/`: admin dashboards, master data, templates, users, notifications, and submission administration.
- `app/Http/Controllers/User/Qc/` and `User/Commissioning/`: form lifecycle, attachments, PDFs, drafts, and history.
- `app/Http/Controllers/PublicApprovalController.php`: unauthenticated magic-link approval surface. Changes here are security-sensitive.
- `app/Http/Middleware/`: user type, active role, and admin menu authorization.
- `app/Models/`: Eloquent models and route-binding behavior.
- `app/Services/`: approval workflow, document number allocation, deletion, status synchronization, and PDF merging.
- `app/Support/QcTemplates/` and `app/Support/Commissioning/`: fixed form schemas and normalization rules.
- `app/Support/UserRoleUiData.php`: shared user-role page data; large and high-impact.
- `resources/views/pdf/`: Dompdf-oriented document layouts. Keep their CSS conservative and self-contained.
- `resources/views/`: Blade screens and partials.
- `public/assets/`: directly served legacy/application CSS and JavaScript. Preserve cache behavior when changing these assets.
- `database/migrations/` and `database/seeders/`: schema history and repeatable reference data.
- `tests/Feature/`: primary regression suite; most behavior is covered through HTTP/database tests.
- `tests/Unit/`: isolated PDF/support tests.

## Core Domain Invariants

### Identity and access

- Authentication alone is insufficient. Preserve the route middleware chain: `auth`, `usertype`, `role`, and `adminmenu` each enforce a different layer.
- A user may have multiple access modes. Keep `active_access_mode` and `active_user_role` consistent with `User::availableAccessModes()`, `CheckUserType`, `CheckRole`, and `AccessModeController`.
- Admin menu visibility is also server-side authorization. Do not rely on hiding buttons in Blade; mutating endpoints must remain protected by middleware and, where relevant, explicit role checks.
- Resource ownership must be checked for submission and attachment routes. An attachment is authorized through its parent submission, not just because the attachment ID exists.
- Public registration is controlled by `PublicRegistrationAccess` and defaults to enabled when the setting is absent. Do not infer its state from comments in `routes/web.php`.

### Inspection submissions

- QC and commissioning submissions have closely related but separate schemas and fixed-template rules. A fix in one domain may need an equivalent change in the other, but do not force abstraction when the business rules differ.
- Drafts and `revision_required` records are editable; later states must not silently become editable.
- Preserve allowed status transitions and their side effects on master-data inspection status and approval flows.
- A submission stores a template snapshot. Render historical submissions from the snapshot/frozen data so later template edits do not rewrite document history.
- Keep both human-facing document numbers and route binding compatible with legacy URL redirects in `routes/web.php`.
- Document number generation must remain atomic. Use `DocumentNumberGenerator` and its transaction/`lockForUpdate` behavior; never implement `max + 1` without locking.
- Store related database mutations in transactions. Database rollback does not roll back filesystem writes, so explicitly clean up newly written files when a transaction or later operation fails.

### Approval flow

- Approval URLs are bearer credentials. Never log, persist, or display raw tokens outside the one response that needs the link; logs may contain only a hash.
- Keep tokens cryptographically random, stored as SHA-256 hashes, expiring, single-use, and revocable.
- Approval and rejection must lock the relevant link/step inside a transaction to prevent double action.
- Starting a replacement flow must revoke or cancel old active links and steps.
- Signature inputs must remain restricted to valid PNG/JPEG image content and the configured byte limit. Extension or client MIME alone is not sufficient validation.
- The temporary signed URL for an approved PDF must remain time-limited.
- Preserve approval events, actor metadata, timestamps, IP address, and user agent unless a documented privacy requirement changes them.

### Files, uploads, and PDFs

- Inspection attachments belong on the private/local disk and must be served only through an authorized controller response. Public-disk reads exist for legacy compatibility; do not use them as the default for new attachments.
- Profile photos and approval signature paths currently use the public disk. Do not move existing files without a migration and backward-compatible read plan.
- Validate size, MIME, decoded image content, count, and field association on the server. Client-side upload guards are only usability aids.
- Generate storage paths server-side. Never trust an uploaded filename as a path and never allow path traversal.
- Deleting or restoring submissions must keep database records, attachments, signatures, approval flows, and master-data statuses consistent. Reuse `InspectionSubmissionDeletionService` where applicable.
- PDF templates are operational documents. Verify header data, template snapshot behavior, signatures, page breaks, attachment orientation, and merged output after changes.

### Master and reference data

- Seeders for reference/master data should be idempotent. Prefer stable natural keys plus `updateOrCreate`/upsert semantics.
- Before deactivating or deleting master data, preserve the usage checks implemented by `MasterDataUsageService`.
- Status changes should go through `MasterDataStatusService` or `MasterDataInspectionStatusService` so history snapshots and source metadata are retained.
- Migrations must be forward-safe on populated databases. Do not edit an already-shared migration to change production schema; add a new migration with a viable rollback.

## Implementation Guidelines

- Follow the existing Laravel style and run Pint. Use strict comparisons, typed return values where practical, early returns, and descriptive domain names.
- Prefer constructor injection for services. Existing `app(...)` calls need not be rewritten incidentally, but new business logic should not be hidden in controllers or Blade.
- Use Form Request classes for new or substantially revised validation flows. Existing inline validation may stay when the change is small.
- Use policies or explicit authorization helpers for new resource-level permissions. Middleware protects route families but does not prove ownership of a particular model.
- Keep controllers focused on HTTP orchestration. When changing the very large QC or commissioning form controllers, extract cohesive validation, persistence, attachment, or presentation behavior only when protected by tests.
- Avoid duplicating status strings, role strings, or template field keys. Reuse existing model constants and support classes; introduce enums/constants only as a deliberate, tested refactor.
- Prevent N+1 queries by eager-loading relationships used in loops, dashboards, notifications, or PDFs.
- Do not place queries or state changes in Blade templates.
- Escape user-provided values with Blade's `{{ }}`. Use `{!! !!}` only for trusted, sanitized application-owned markup.
- User-facing text is predominantly Indonesian. Match the existing language and terminology; logs and internal identifiers may remain English.
- Never introduce shared or hard-coded account passwords. If changing user provisioning, prefer a cryptographically generated one-time credential or a password-setup/reset link, and require the user to choose their own password.
- Remove debug calls and temporary copy before finishing. Never commit secrets, `.env`, generated files, `vendor/`, `node_modules/`, `public/build/`, logs, or real uploaded documents.

## Frontend Guidelines

- Reuse the existing Blade partials and design language before adding a new component system.
- Check both desktop and mobile layouts. Several pages have dedicated mobile CSS, and large fixed forms can fail through overflow even when desktop rendering looks correct.
- JavaScript must tolerate missing optional DOM elements because shared assets are loaded by multiple pages.
- Keep CSRF tokens on every state-changing form/request. Do not turn GET routes into mutations.
- When modifying a file in `public/assets/`, check how it is referenced and whether a cache-busting query/version is needed. When modifying Vite inputs, run `npm run build`.
- Prefer bundling third-party browser dependencies through npm/Vite. If a CDN is intentionally retained, pin an exact version and consider Subresource Integrity plus an appropriate Content Security Policy.
- Preserve accessibility basics: associated labels, keyboard-reachable controls, visible focus, semantic buttons/links, and meaningful error messages.

## Testing Expectations

All behavior changes need a regression test. Use `RefreshDatabase`, factories, `Storage::fake()`, and Laravel HTTP assertions as the existing suite does.

Choose tests by impact:

- Authentication, registration, password reset, roles, or access-mode changes: relevant tests under `tests/Feature/Auth/`, plus `MultiAccessModeTest` and `UserRolePagesTest`.
- QC forms, attachments, PDFs, numbering, approval, or deletion: `QcFormSubmissionTest` and relevant PDF unit tests.
- Commissioning forms, attachments, PDFs, numbering, approval, or deletion: `CommissioningFormFlowTest`.
- Admin submission lists, filters, restore/delete, or metrics: `AdminInspectionMetricsTest`.
- Master equipment/status behavior: `AdminMasterDataTest`.
- Template changes: `AdminTemplateFormQcTest` and/or commissioning flow tests.
- User and admin notification changes: both topbar notification test files where shared behavior is involved.
- Authorization changes: include happy path and forbidden cross-user/cross-role cases.
- Concurrency-sensitive changes: test uniqueness/state outcomes and keep the database lock; SQLite tests alone do not validate production lock behavior, so note when MySQL/PostgreSQL verification is still needed.

Before handing off a normal code change, run the narrowest relevant test first, then:

```bash
composer test
./vendor/bin/pint --test
npm run build
```

If only Markdown documentation changed, review the rendered structure and links; the full application suite is optional. If any required command cannot run, state exactly why.

## Change Discipline

- Inspect `git status` before editing and preserve unrelated user changes.
- Keep patches scoped. Do not combine a bug fix with broad formatting, dependency upgrades, or architectural rewrites.
- Do not change public routes, route names, document-number formats, status values, storage paths, database columns, or serialized JSON shapes without tracing all callers, tests, legacy data, and PDF consumers.
- Do not delete fallback/legacy behavior until usage is proven absent and a data migration exists.
- Dependency upgrades require lockfile changes, release-note review, a security check, and the full test/build suite.
- Never use destructive database commands (`migrate:fresh`, blanket truncation, unscoped deletes) against an unknown or shared environment.

## Definition of Done

A change is complete when:

1. The requested behavior works across the affected access modes and submission states.
2. Authorization, validation, audit history, files, and transactional side effects remain correct.
3. A focused regression test exists and passes.
4. The relevant broader tests, Pint check, and frontend build pass, or verification gaps are explicitly reported.
5. No secrets, debug artifacts, generated output, or unrelated changes are included.
6. The handoff summarizes changed files, observable behavior, tests run, and any remaining operational risk.
