# Sinkronisasi Struktur Organisasi WOMS

## Tujuan

Integrasi ini menyalin seluruh master struktur organisasi dari WOMS ke database lokal Overhaul Management. WOMS tetap menjadi _single source of truth_; Overhaul hanya membaca API dan tidak pernah mengubah data WOMS.

Master lokal memungkinkan halaman monitoring dan pengembangan integrasi berikutnya tetap membaca snapshot terakhir yang berhasil ketika WOMS sedang tidak tersedia. Tahap ini tidak mengganti sumber dropdown QC/Commissioning, tidak mengubah Master Equipment, dan tidak mengubah approval magic link.

## Arsitektur

Alur sinkronisasi:

1. Overhaul mengambil satu snapshot lengkap melalui HTTPS.
2. Seluruh respons divalidasi sebelum transaksi database dimulai.
3. Checksum dibandingkan dengan sinkronisasi berhasil terakhir.
4. Jika ada perubahan, personel, departemen, unit kerja, dan seksi disimpan dalam satu transaksi.
5. Record yang hilang dari sumber ditandai `present_in_source = false`, bukan dihapus.
6. Metadata hasil proses dicatat tanpa token dan tanpa raw JSON.

Permintaan HTTP dilakukan sebelum transaksi penulisan agar database tidak terkunci selama koneksi jaringan berlangsung.

## Endpoint WOMS

```text
GET {WOMS_ORGANIZATION_BASE_URL}/api/v1/organization/snapshot
Authorization: Bearer {WOMS_ORGANIZATION_TOKEN}
Accept: application/json
```

Redirect HTTP dinonaktifkan agar Bearer token tidak ikut terkirim ke host lain. Verifikasi TLS tetap aktif. Pada environment production, URL non-HTTPS ditolak.

## Konfigurasi Environment

Tambahkan nilai berikut pada `.env` server Overhaul. Jangan commit token aktual.

```env
WOMS_ORGANIZATION_BASE_URL=https://workshop.dept-pmms.com
WOMS_ORGANIZATION_TOKEN=<TOKEN_DARI_WOMS>
WOMS_ORGANIZATION_SYNC_ENABLED=false
WOMS_ORGANIZATION_SYNC_INTERVAL_MINUTES=30
WOMS_ORGANIZATION_TIMEOUT_SECONDS=15
WOMS_ORGANIZATION_MAX_REDUCTION_PERCENT=25
WOMS_ORGANIZATION_LOCK_SECONDS=300
```

`WOMS_ORGANIZATION_SYNC_ENABLED` harus tetap `false` sampai migration, konfigurasi token, dan sinkronisasi manual telah diverifikasi.

## Struktur Database Lokal

- `woms_personnel`: identitas pejabat WOMS berdasarkan `woms_user_id`; tidak membuat akun login Overhaul.
- `woms_departments`: departemen dan referensi General Manager.
- `woms_units`: unit kerja, parent departemen, dan referensi Senior Manager.
- `woms_sections`: seksi, parent unit kerja, dan referensi Manager.
- `woms_organization_sync_runs`: status, metadata sumber, jumlah perubahan, dan error yang sudah disanitasi.
- `organization_section_mappings`: fondasi pemetaan satu-ke-satu antara seksi WOMS dan `organization_sections` lama.

Semua entitas WOMS menggunakan primary key lokal Overhaul. ID asli sumber disimpan dalam kolom `woms_id` atau `woms_user_id` dengan unique constraint.

Tabel `organization_sections` serta foreign key `master_data_records.organization_section_id` tidak diubah.

## Validasi Snapshot

Sinkronisasi ditolak sebelum database master berubah apabila salah satu kondisi berikut tidak terpenuhi:

- HTTP harus 200 dan root body harus JSON object.
- `success` dan `meta.complete` harus `true`.
- `meta.source` harus `WOMS` dan `schema_version` harus `1`.
- Departemen, unit kerja, dan seksi harus berupa array dengan ID integer positif dan unik.
- Relasi `department_id` dan `unit_work_id` harus sama dengan parent pada hierarki.
- Nama wajib berupa string non-kosong yang dapat disimpan.
- Pejabat harus object valid atau `null`; identitas dengan ID sama tidak boleh bertentangan.
- Jumlah hasil parsing harus sama dengan `meta.counts`.
- Checksum harus berbentuk SHA-256 hexadecimal 64 karakter.
- `generated_at` harus berupa timestamp valid.

Checksum digunakan sebagai identitas versi snapshot. Implementasi ini belum menghitung ulang checksum secara independen karena algoritma kanonisasi JSON milik WOMS belum menjadi bagian kontrak yang tersedia. Struktur, tipe, ID, relasi, kelengkapan, dan counts tetap divalidasi penuh.

## Proses Sinkronisasi

Urutan penulisan adalah personel, departemen, unit kerja, lalu seksi. Upsert menggunakan ID asli WOMS sehingga proses idempotent, perubahan nama dapat diperbarui, dan satu personel yang memegang lebih dari satu jabatan tetap disimpan satu kali.

Jika checksum sama dengan sinkronisasi berhasil terakhir dan jumlah serta relasi lokal konsisten, run dicatat sebagai `no_changes` tanpa menulis ulang seluruh master.

Jika suatu ID tidak lagi ada di snapshot valid, record tetap dipertahankan dan ditandai:

```text
present_in_source = false
source_missing_at = waktu sinkronisasi
```

Jika ID yang sama muncul kembali, record diaktifkan kembali dan `source_missing_at` dikosongkan.

Semua perubahan master terjadi dalam satu transaksi. Kegagalan akan me-rollback perubahan master, sedangkan run gagal dicatat secara terpisah.

## Pencegahan Penghapusan Massal

Snapshot kosong atau penurunan jumlah departemen/unit/seksi yang melampaui `WOMS_ORGANIZATION_MAX_REDUCTION_PERCENT` diblokir dengan status `needs_review`. Tidak ada master yang berubah.

Super Admin dapat memeriksa metadata dan kemudian memakai tombol **Konfirmasi Perubahan Besar**. Sistem akan mengambil dan memvalidasi ulang snapshot serta memastikan checksum masih sama dengan snapshot yang direview sebelum menerapkan perubahan. Konfirmasi ini hanya melewati ambang penurunan; validasi autentikasi, JSON, schema, counts, ID, dan relasi tetap wajib lolos.

## Lock dan Proses Bersamaan

Service menggunakan atomic cache lock `woms-organization-sync`. Scheduler, command, dan tombol admin memakai service yang sama. Jika lock sedang dimiliki proses lain, proses kedua tidak dijalankan dan run dicatat sebagai perlu review.

Cache driver deployment harus mendukung atomic lock dan dipakai bersama oleh seluruh instance aplikasi. Konfigurasi repository saat ini menggunakan database cache secara default.

## Error Handling

- Timeout/koneksi gagal: retry terbatas, master lokal tidak berubah.
- HTTP 401/403: tidak di-retry; ditandai sebagai masalah kredensial.
- HTTP sementara 408/425/429/5xx: dicoba kembali maksimal dua kali.
- Redirect: tidak diikuti.
- JSON/schema/relasi/counts invalid: sinkronisasi dihentikan.
- Error database: transaksi master di-rollback.

Log sinkronisasi tidak menyimpan Bearer token, query string token, atau raw snapshot API.

## Command Manual

Setelah migration dan `.env` disiapkan pada deployment, jalankan secara manual:

```bash
php artisan organization:sync-woms
```

Jika run diblokir karena penurunan besar dan snapshot sudah diperiksa:

```bash
php artisan organization:sync-woms --confirm-large-reduction
```

Command mengembalikan exit code gagal untuk status `failed` atau `needs_review`, dan tidak pernah mencetak token.

## Scheduler

Scheduler didaftarkan melalui `routes/console.php`, tetapi hanya ketika:

```env
WOMS_ORGANIZATION_SYNC_ENABLED=true
```

Interval default adalah 30 menit dan proses memakai `withoutOverlapping` ditambah lock pada service. Cron cPanel tidak dibuat otomatis. Setelah verifikasi manual, cron server perlu menjalankan scheduler Laravel, misalnya sesuai path PHP dan repository hosting:

```cron
* * * * * cd /path/to/overhaul && /path/to/php artisan schedule:run >> /dev/null 2>&1
```

Sesuaikan path secara manual dan pastikan hanya satu konfigurasi scheduler aktif.

## Halaman Admin

Menu **Master Data → Struktur Organisasi WOMS** menyediakan:

- status sinkronisasi berdasarkan run aktual;
- jumlah departemen, unit, seksi, personel, record hilang, dan mapping terverifikasi;
- checksum serta waktu sinkronisasi terakhir;
- hierarki lengkap termasuk unit tanpa seksi dan jabatan kosong;
- filter departemen, unit, seksi, personel, dan status presence;
- pagination departemen;
- sepuluh riwayat sinkronisasi terbaru.

Route halaman dilindungi `auth`, `usertype:admin`, dan `adminmenu`. Menu mempunyai key `master_data.woms-organization`. Tombol POST memakai CSRF dan hanya dapat dijalankan oleh Super Admin; akses tampilan saja tidak memberi hak menjalankan sinkronisasi.

## Mapping Master Lama

`organization_section_mappings` disiapkan untuk menghubungkan `organization_sections.id` dengan `woms_sections.id`. Mapping default berstatus `pending` dan baru dianggap final pada status `verified` beserta admin verifikator dan waktu verifikasi.

Tahap ini tidak membuat mapping otomatis berdasarkan nama dan tidak mengubah foreign key equipment/dokumen. Nama seksi yang sama pada unit berbeda harus diperlakukan sebagai entitas berbeda. Pembuatan saran dan layar verifikasi mapping menjadi bagian tahap berikutnya.

Seeder organisasi lama tetap dipertahankan dan tidak boleh dijalankan untuk menimpa mapping yang telah diverifikasi.

## Identitas WOMS dan Login Overhaul

`woms_user_id` hanya merupakan ID referensi personel dari WOMS. Nilai itu bukan `users.id` Overhaul. Sinkronisasi tidak membuat akun, menyalin password, mengubah role, membuat SSO, atau menghubungkan session kedua aplikasi.

## Kompatibilitas QC, Commissioning, dan Approval

Tahap ini sengaja tidak mengubah:

- dropdown Area Owner QC/Commissioning;
- `header[unit_kerja]` atau `header[organization_section_id]`;
- snapshot dokumen dan PDF;
- `master_data_records.organization_section_id`;
- `ApprovalFlowService` atau public magic-link approval;
- token, TTL, tanda tangan, dan akun login.

Master lokal WOMS belum menjadi sumber form operasional sampai mapping diverifikasi pada tahap berikutnya.

## Langkah Deployment Manual di cPanel

1. Backup database dan source aktif.
2. Deploy source tanpa mengubah `.env` yang sudah ada.
3. Isi konfigurasi WOMS dengan token yang diberikan secara aman.
4. Pastikan `WOMS_ORGANIZATION_SYNC_ENABLED=false`.
5. Jalankan migration baru sesuai prosedur deployment aplikasi.
6. Jalankan `php artisan organization:sync-woms` satu kali.
7. Periksa halaman monitoring, counts, checksum, hierarki, jabatan kosong, dan riwayat run.
8. Periksa kembali fungsi QC, Commissioning, approval, dan Master Equipment.
9. Setelah hasil manual disetujui, aktifkan scheduler dan cron cPanel.
10. Pantau beberapa run awal dan jangan konfirmasi penurunan besar tanpa membandingkan data sumber.

## Batasan Tahap 2 dan Tahap Berikutnya

Tahap 2 hanya membangun sinkronisasi dan monitoring master lokal. Tahap 3 dapat menambahkan saran mapping berbasis hierarki, verifikasi manual mapping, lalu migrasi bertahap penggunaan master WOMS pada form. Penggunaan master baru pada dokumen operasional tidak boleh dilakukan sebelum aturan fallback, snapshot historis, dan mapping ambigu disepakati.
