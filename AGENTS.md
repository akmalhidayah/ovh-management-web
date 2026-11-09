# AGENTS.md

Laravel 12 (PHP 8.2+, Blade, PHPUnit 11, Vite 7/Tailwind 4 + Bootstrap & `public/assets/` legacy) untuk manajemen overhaul: QC, Commissioning, multi-step approval, master equipment, seksi organisasi, notifikasi. Dokumen submission, approval, tanda tangan, dan lampiran adalah **catatan bisnis yang diaudit**.

Aturan lengkap (versi asli): [docs/panduan.md](docs/panduan.md). Baca bagian terkait sebelum mengubah area sensitif.

## Perintah

```bash
composer test            # suite penuh
php artisan test --filter=<NamaTest>
./vendor/bin/pint --test
npm run build            # wajib bila Vite input berubah
```
Jangan klaim lulus bila PHP/Composer/Node/`vendor/` tidak tersedia; laporkan gap verifikasi.

## Peta Kode

| Area | Lokasi |
|---|---|
| Route (semua, ada closure → jangan `route:cache`) | `routes/web.php` (admin L120, user qc L215, commissioning L252, pgo L289, approval L300, inspector L322) |
| Middleware | `app/Http/Middleware/` → `CheckUserType`, `CheckRole`, `CheckAdminMenuPermission` |
| Auth & access mode | `Controllers/Auth/` (`AccessModeController`, `RegisterController`), `Support/PublicRegistrationAccess` |
| Admin | `Controllers/Admin/` (Qc/CommissioningSubmission, MasterData, TemplateFormQc/Commissioning, UserPanel, OrganizationSection, WomsOrganization, Notification, Dashboard) |
| User QC | `Controllers/User/Qc/FormController` (±1970 baris), Draft, History, Document |
| User Commissioning | `Controllers/User/Commissioning/FormController` (±1160 baris), Draft, History, Document |
| User Approval / PGO | `Controllers/User/Approval/`, `Controllers/User/Pgo/` |
| Magic-link approval (publik, sensitif) | `Controllers/PublicApprovalController.php`, `Services/ApprovalFlowService.php`, models `ApprovalFlow/Step/Link/Event` |
| Nomor dokumen | `Services/DocumentNumberGenerator.php`, `Models/DocumentNumberSequence` |
| Hapus/restore submission | `Services/InspectionSubmissionDeletionService.php` |
| Master data | `Services/MasterData{Usage,Status,InspectionStatus}Service.php`, `Models/MasterData*`, `Support/MasterDataIdentity` |
| Template form | `Support/QcTemplates/` (`FixedQcTemplate`, `QcTemplateRegistry`, `TemplateBuilder`, `PresetBlocks`), `Support/Commissioning/FixedCommissioningTemplate`, `Support/TemplateSnapshot` |
| PDF | `resources/views/pdf/` (Dompdf, CSS konservatif), `Services/QcPdfAttachmentMerger`, `Support/Pdf/SignatureImage` |
| Organisasi/WOMS | `Services/WomsOrganizationResolver`, `Services/Integrations/`, `Support/OrganizationSections`, `docs/integrations/woms-organization-sync.md` |
| Rencana Kerja (frontend dummy, 3 modul mandiri, jangan saling berbagi file) | `resources/views/modules/schedule-{barang,jasa,capex}/`, `Support/Schedule{Barang,Jasa,Capex}PageData`, `public/assets/{css,js}/schedule-{barang,jasa,capex}.*` (prefix `sb-`/`sj-`/`sc-`); pintu masuk `modules/schedule-content` |
| Notifikasi | `Support/AdminTopbarNotifications`, `Support/UserTopbarNotifications` |
| Data halaman | `Support/UserRoleUiData.php` (±1280 baris, berdampak besar), `AdminInspectionSubmissionPageData`, `QcSubmissionPageData`, `AdminMenuPermissions` |
| View | `resources/views/{admin,user,modules,partials,pdf,approvals,auth}` |
| Aset langsung | `public/assets/{css,js}` (`user-role`, `ovh-dashboard`, `global-upload-guard`, `*-mobile.css`) |
| DB | `database/migrations/`, `database/seeders/` |

## Peta Test

| Perubahan | Test |
|---|---|
| Auth, role, access mode | `tests/Feature/Auth/`, `MultiAccessModeTest`, `UserRolePagesTest` |
| QC (form, lampiran, PDF, nomor, approval, hapus) | `QcFormSubmissionTest`, `Unit/QcPdfAttachmentMergerTest` |
| Commissioning | `CommissioningFormFlowTest` |
| Daftar/filter/restore/metrik admin | `AdminInspectionMetricsTest` |
| Master data | `AdminMasterDataTest` |
| Template | `AdminTemplateFormQcTest`, `CommissioningFormFlowTest` |
| Notifikasi | `AdminTopbarNotificationTest`, `UserTopbarNotificationTest` |
| User panel / organisasi | `AdminUserPanelTest`, `AdminOrganizationSectionTest`, `WomsOrganizationCutoverTest` |

## Aturan Inti (ringkas)

**Akses**
- Pertahankan rantai middleware `auth` → `usertype` → `role` → `adminmenu`; menyembunyikan tombol di Blade bukan otorisasi.
- Jaga `active_access_mode`/`active_user_role` konsisten dengan `User::availableAccessModes()`.
- Cek kepemilikan resource; lampiran diotorisasi lewat submission induknya.

**Submission**
- Hanya `draft` dan `revision_required` yang boleh diedit; jaga transisi status & efek sampingnya.
- Render dokumen historis dari snapshot template, bukan template terbaru.
- Nomor dokumen hanya via `DocumentNumberGenerator` (transaksi + `lockForUpdate`, jangan `max+1`). Jaga kompatibilitas redirect URL legacy.
- Mutasi terkait → transaksi; bersihkan file baru secara manual bila gagal (rollback DB tidak menghapus file).
- QC dan Commissioning mirip tapi terpisah; perbaikan di satu sisi cek sisi lainnya, jangan dipaksa abstraksi.

**Approval**
- Token = kredensial: acak kriptografis, simpan hash SHA-256, kedaluwarsa, sekali pakai, bisa dicabut; jangan log/simpan token mentah.
- Approve/reject: kunci link/step dalam transaksi. Flow pengganti mencabut link/step lama.
- Tanda tangan: validasi isi PNG/JPEG + batas byte (bukan hanya ekstensi/MIME). URL PDF bertanda tangan tetap berbatas waktu.
- Pertahankan event approval (aktor, waktu, IP, user agent).

**File**
- Lampiran inspeksi di disk private/local, disajikan via controller terotorisasi. Foto profil & tanda tangan tetap di disk public (jangan dipindah tanpa migrasi).
- Validasi ukuran/MIME/isi/jumlah di server; path dibuat server, tolak path traversal.
- Hapus/restore lewat `InspectionSubmissionDeletionService`.

**Data & migrasi**
- Seeder idempotent (`updateOrCreate`). Cek `MasterDataUsageService` sebelum nonaktif/hapus; ubah status via `MasterData*StatusService`.
- Migrasi baru untuk perubahan skema (jangan edit migrasi yang sudah dibagikan), sertakan rollback.
- Jangan pakai `migrate:fresh`/truncate di lingkungan tak dikenal.

**Kode & UI**
- Gaya Laravel + Pint; perbandingan strict; service via constructor injection; logika bisnis baru tidak di controller/Blade; Form Request untuk validasi baru.
- Eager load untuk menghindari N+1. Tidak ada query/mutasi di Blade. Escape dengan `{{ }}`.
- Teks UI berbahasa Indonesia. Jangan hardcode password/akun bersama.
- CSRF di semua form mutasi; GET tidak boleh mengubah data. JS toleran terhadap elemen DOM yang tidak ada. Cek mobile & desktop.
- Ubah `public/assets/` → cek cache-busting; ubah Vite input → `npm run build`.

## Disiplin Perubahan

- Cek `git status` dulu; jaga perubahan user lain; patch tetap sempit.
- Jangan ubah route/nama route, format nomor dokumen, nilai status, path storage, kolom DB, atau bentuk JSON tanpa menelusuri semua pemanggil, test, data legacy, dan PDF.
- Perubahan perilaku wajib punya regression test (`RefreshDatabase`, factory, `Storage::fake()`); untuk otorisasi sertakan kasus lintas user/role. Catat bila lock perlu verifikasi MySQL/PostgreSQL (SQLite tidak cukup).
- Hapus debug sebelum selesai; jangan commit secret, `.env`, `vendor/`, `node_modules/`, `public/build/`, log, atau dokumen upload asli.

## Definition of Done

Test fokus lulus → `composer test` → `./vendor/bin/pint --test` → `npm run build` (atau laporkan yang tak bisa dijalankan). Ringkasan akhir: file diubah, perilaku, test dijalankan, risiko operasional tersisa.
