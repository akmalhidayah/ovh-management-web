<?php

namespace App\Support;

use App\Models\CommissioningFormSubmission;
use App\Models\MasterDataRecord;
use App\Models\QcFormSubmission;
use App\Support\MasterDataIdentity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRoleUiData
{
    public static function layout(string $role): array
    {
        $meta = match ($role) {
            'qc' => [
                'role' => 'qc',
                'role_label' => 'QC',
                'brand_title' => 'Quality Control',
                'brand_subtitle' => 'Unit Kerja Overhaul PT. Semen Tonasa',
                'hero_asset' => 'assets/images/illustrations/hero-qc.svg',
                'nav' => [
                    ['label' => 'Dashboard', 'route' => 'user.qc.dashboard', 'icon' => 'bi-grid-1x2'],
                    ['label' => 'Form QC', 'route' => 'user.qc.forms.create', 'icon' => 'bi-ui-checks-grid'],
                    ['label' => 'Draft QC', 'route' => 'user.qc.drafts.index', 'icon' => 'bi-journal-richtext'],
                    ['label' => 'Riwayat QC', 'route' => 'user.qc.history.index', 'icon' => 'bi-clock-history'],
                ],
            ],
            'commissioning' => [
                'role' => 'commissioning',
                'role_label' => 'Commissioning',
                'brand_title' => 'Commissioning',
                'brand_subtitle' => 'Unit Kerja Overhaul PT. Semen Tonasa',
                'hero_asset' => 'assets/images/illustrations/hero-commissioning.svg',
                'nav' => [
                    ['label' => 'Dashboard', 'route' => 'user.commissioning.dashboard', 'icon' => 'bi-grid-1x2'],
                    ['label' => 'Form', 'route' => 'user.commissioning.forms.create', 'icon' => 'bi-ui-checks-grid'],
                    ['label' => 'Draft', 'route' => 'user.commissioning.drafts.index', 'icon' => 'bi-journal-richtext'],
                    ['label' => 'Riwayat', 'route' => 'user.commissioning.history.index', 'icon' => 'bi-clock-history'],
                ],
            ],
            'pgo' => [
                'role' => 'pgo',
                'role_label' => 'PGO',
                'brand_title' => 'PGO',
                'brand_subtitle' => 'OVH Management Workspace',
                'hero_asset' => 'assets/images/illustrations/hero-pgo.svg',
                'nav' => [
                    ['label' => 'Dashboard', 'route' => 'user.pgo.dashboard', 'icon' => 'bi-grid-1x2'],
                    ['label' => 'Tugas PGO', 'route' => 'user.pgo.tasks.index', 'icon' => 'bi-list-check'],
                    ['label' => 'Monitoring', 'route' => 'user.pgo.monitoring.index', 'icon' => 'bi-activity'],
                    ['label' => 'Dokumen', 'route' => 'user.pgo.documents.index', 'icon' => 'bi-folder2-open'],
                    ['label' => 'Riwayat', 'route' => 'user.pgo.history.index', 'icon' => 'bi-clock-history'],
                ],
            ],
            'approval' => [
                'role' => 'approval',
                'role_label' => 'Approval',
                'brand_title' => 'Approval Officer',
                'brand_subtitle' => 'OVH Management Workspace',
                'hero_asset' => 'assets/images/illustrations/hero-approval.svg',
                'nav' => [
                    ['label' => 'Dashboard', 'route' => 'user.approval.dashboard', 'icon' => 'bi-grid-1x2'],
                    ['label' => 'Menunggu Approval', 'route' => 'user.approval.pending.index', 'icon' => 'bi-hourglass-split'],
                    ['label' => 'Review Form', 'route' => 'user.approval.review.index', 'icon' => 'bi-search'],
                    ['label' => 'Riwayat Approval', 'route' => 'user.approval.history.index', 'icon' => 'bi-clock-history'],
                    ['label' => 'Dokumen', 'route' => 'user.approval.documents.index', 'icon' => 'bi-folder2-open'],
                ],
            ],
            default => [
                'role' => 'qc',
                'role_label' => 'QC',
                'brand_title' => 'Quality Control',
                'brand_subtitle' => 'Unit Kerja Overhaul PT. Semen Tonasa',
                'hero_asset' => 'assets/images/illustrations/hero-qc.svg',
                'nav' => [],
            ],
        };

        return array_merge($meta, [
            'logo' => 'assets/images/logo/logo-user.svg',
            'notification_count' => 3,
        ]);
    }

    public static function qcDashboard(): array
    {
        return self::inspectionDashboard('qc', [
            'title' => 'Dashboard QC',
            'subtitle' => 'Pantau draft dan riwayat form Quality Control.',
            'hero_note' => 'Draft dan riwayat QC siap dipantau',
            'actions' => [
                ['label' => 'Buat Form QC', 'route' => 'user.qc.forms.create', 'icon' => 'bi-plus-circle'],
                ['label' => 'Draft QC', 'route' => 'user.qc.drafts.index', 'icon' => 'bi-journal-text'],
                ['label' => 'Riwayat QC', 'route' => 'user.qc.history.index', 'icon' => 'bi-clock-history'],
            ],
            'stats' => [
                ['label' => 'Draft QC', 'value' => '5', 'icon' => 'bi-pencil-square', 'accent' => 'warning'],
                ['label' => 'Perlu Revisi', 'value' => '3', 'icon' => 'bi-arrow-repeat', 'accent' => 'danger'],
                ['label' => 'Menunggu Review', 'value' => '4', 'icon' => 'bi-hourglass-split', 'accent' => 'warning'],
                ['label' => 'Form Terkirim', 'value' => '28', 'icon' => 'bi-send-check', 'accent' => 'success'],
                ['label' => 'Riwayat PDF', 'value' => '14', 'icon' => 'bi-file-earmark-pdf', 'accent' => 'secondary'],
            ],
            'drafts_title' => 'Draft QC Saya',
            'history_title' => 'Riwayat QC Terbaru',
            'drafts' => [
                ['category' => 'QC', 'title' => 'QC - Gearbox GB-301', 'equipment' => 'Gearbox GB-301', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Drive', 'status' => 'Dalam Proses', 'updated_at' => '21 Mei 2025 15:05'],
                ['category' => 'QC', 'title' => 'QC - Vibrating Screen VS-01', 'equipment' => 'Vibrating Screen VS-01', 'plant' => 'Cement Plant 1', 'area' => 'Crusher Area', 'status' => 'Perlu Revisi', 'updated_at' => '20 Mei 2025 16:40'],
                ['category' => 'QC', 'title' => 'QC - Pump P-204', 'equipment' => 'Pump P-204', 'plant' => 'Cement Plant 1', 'area' => 'Raw Mill Area', 'status' => 'Draft', 'updated_at' => '19 Mei 2025 09:20'],
            ],
            'history' => [
                ['submitted_at' => '21 Mei 2025 15:30', 'category' => 'QC', 'type' => 'QC Conveyor Inspection', 'equipment' => 'Conveyor CV-05', 'plant' => 'Cement Plant 1', 'area' => 'Coal Mill Area', 'status' => 'Disetujui'],
                ['submitted_at' => '21 Mei 2025 11:45', 'category' => 'QC', 'type' => 'QC Motor Inspection', 'equipment' => 'Motor M-102', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Area', 'status' => 'Menunggu Review'],
                ['submitted_at' => '20 Mei 2025 13:00', 'category' => 'QC', 'type' => 'QC Valve Inspection', 'equipment' => 'Valve V-120', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Area', 'status' => 'Perlu Revisi'],
            ],
        ]);
    }

    public static function commissioningDashboard(): array
    {
        return self::inspectionDashboard('commissioning', [
            'title' => 'Dashboard Commissioning',
            'subtitle' => 'Pantau draft dan riwayat form Commissioning.',
            'hero_note' => 'Draft dan riwayat commissioning siap dipantau',
            'actions' => [
                ['label' => 'Buat Form Commissioning', 'route' => 'user.commissioning.forms.create', 'icon' => 'bi-plus-circle'],
                ['label' => 'Draft Commissioning', 'route' => 'user.commissioning.drafts.index', 'icon' => 'bi-journal-text'],
                ['label' => 'Riwayat Commissioning', 'route' => 'user.commissioning.history.index', 'icon' => 'bi-clock-history'],
            ],
            'stats' => [
                ['label' => 'Draft Commissioning', 'value' => '4', 'icon' => 'bi-pencil-square', 'accent' => 'warning'],
                ['label' => 'Perlu Revisi', 'value' => '2', 'icon' => 'bi-arrow-repeat', 'accent' => 'danger'],
                ['label' => 'Menunggu Review', 'value' => '5', 'icon' => 'bi-hourglass-split', 'accent' => 'warning'],
                ['label' => 'Form Terkirim', 'value' => '22', 'icon' => 'bi-send-check', 'accent' => 'success'],
                ['label' => 'Riwayat PDF', 'value' => '11', 'icon' => 'bi-file-earmark-pdf', 'accent' => 'secondary'],
            ],
            'drafts_title' => 'Draft Commissioning Saya',
            'history_title' => 'Riwayat Commissioning Terbaru',
            'drafts' => [
                ['category' => 'Commissioning', 'title' => 'Commissioning - ID Fan IF-02', 'equipment' => 'ID Fan IF-02', 'plant' => 'Cement Plant 2', 'area' => 'Raw Mill Area', 'status' => 'Menunggu Data', 'updated_at' => '21 Mei 2025 13:10'],
                ['category' => 'Commissioning', 'title' => 'Commissioning - Cooling Tower CT-01', 'equipment' => 'Cooling Tower CT-01', 'plant' => 'Cement Plant 2', 'area' => 'Utilities Area', 'status' => 'Dalam Proses', 'updated_at' => '21 Mei 2025 09:55'],
                ['category' => 'Commissioning', 'title' => 'Commissioning - Baghouse BH-01', 'equipment' => 'Baghouse BH-01', 'plant' => 'Cement Plant 2', 'area' => 'Raw Mill Area', 'status' => 'Draft', 'updated_at' => '19 Mei 2025 14:20'],
            ],
            'history' => [
                ['submitted_at' => '21 Mei 2025 14:10', 'category' => 'Commissioning', 'type' => 'Commissioning Static Equipment', 'equipment' => 'Baghouse BH-01', 'plant' => 'Cement Plant 2', 'area' => 'Raw Mill Area', 'status' => 'Disetujui'],
                ['submitted_at' => '20 Mei 2025 16:20', 'category' => 'Commissioning', 'type' => 'Commissioning Handover', 'equipment' => 'Silo S-05', 'plant' => 'Cement Plant 2', 'area' => 'Packing Plant', 'status' => 'Disetujui'],
                ['submitted_at' => '19 Mei 2025 10:35', 'category' => 'Commissioning', 'type' => 'Commissioning System Test', 'equipment' => 'Loop Reactor LR-03', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Area', 'status' => 'Menunggu Review'],
            ],
        ]);
    }

    public static function qcForm(): array
    {
        return self::inspectionForm('qc', [
            'page_title' => 'Buat Form Quality Control',
            'page_subtitle' => '',
            'template_category' => 'QC',
            'default_template' => 'Standard QCR Penggantian Wearing Tyre Kiln',
            'date_label' => 'Tanggal Pemeriksaan',
            'form_title' => 'Standard QCR Penggantian Wearing Tyre Kiln',
            'result_title' => 'Hasil Pemeriksaan',
            'result_note' => 'Input hasil utama yang menjadi ringkasan final form QC.',
            'finding_title' => 'Temuan & Rekomendasi',
            'finding_note' => 'Catat isu utama dan langkah tindak lanjut untuk tim maintenance atau inspector berikutnya.',
        ]);
    }

    public static function commissioningForm(): array
    {
        return self::inspectionForm('commissioning', [
            'page_title' => 'Form Commissioning',
            'page_subtitle' => '',
            'template_category' => 'Commissioning',
            'default_template' => 'Form Commissioning Semen Tonasa - Overhaul Tonasa 4',
            'date_label' => 'Tanggal Commissioning',
            'form_title' => 'Form Commissioning Semen Tonasa - Overhaul Tonasa 4',
            'result_title' => 'Hasil Pengujian',
            'result_note' => 'Isi hasil pengujian utama dan kondisi akhir sebelum handover.',
            'finding_title' => 'Catatan Commissioning',
            'finding_note' => 'Catat temuan, kondisi khusus, dan rekomendasi sebelum unit dinyatakan siap.',
        ]);
    }

    public static function qcDrafts(): array
    {
        return self::inspectionDrafts('qc', [
            'title' => 'Draft Form QC',
            'subtitle' => 'Lanjutkan form QC yang belum selesai atau perlu diperbaiki.',
            'filters' => [
                'categories' => ['Semua', 'QC'],
                'form_types' => ['Semua Jenis Form', 'QC Pump Inspection', 'QC Vibration Inspection', 'QC Valve Inspection', 'QC Conveyor Inspection'],
                'plants' => ['Semua Plant', 'Cement Plant 1', 'Cement Plant 2'],
                'statuses' => ['Semua Status', 'Dalam Proses', 'Perlu Revisi', 'Draft'],
            ],
            'rows' => [
                ['no' => 1, 'category' => 'QC', 'form_type' => 'QC Pump Inspection', 'equipment' => 'Pump P-204', 'plant' => 'Cement Plant 1', 'area' => 'Raw Mill Area', 'updated_at' => '21 Mei 2025 14:25', 'status' => 'Dalam Proses'],
                ['no' => 2, 'category' => 'QC', 'form_type' => 'QC Vibration Inspection', 'equipment' => 'Vibrating Screen VS-01', 'plant' => 'Cement Plant 1', 'area' => 'Crusher Area', 'updated_at' => '20 Mei 2025 16:40', 'status' => 'Perlu Revisi'],
                ['no' => 3, 'category' => 'QC', 'form_type' => 'QC Valve Inspection', 'equipment' => 'Valve V-120', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Area', 'updated_at' => '19 Mei 2025 09:10', 'status' => 'Draft'],
            ],
            'continue_route' => 'user.qc.forms.create',
        ]);
    }

    public static function commissioningDrafts(): array
    {
        return self::inspectionDrafts('commissioning', [
            'title' => 'Draft Form Commissioning',
            'subtitle' => 'Lanjutkan form commissioning yang belum selesai atau perlu dilengkapi.',
            'filters' => [
                'categories' => ['Semua', 'Commissioning'],
                'form_types' => ['Semua Jenis Form', 'Commissioning Motor', 'Commissioning Mechanical', 'Commissioning System Test', 'Commissioning Handover'],
                'plants' => ['Semua Plant', 'Cement Plant 1', 'Cement Plant 2'],
                'statuses' => ['Semua Status', 'Dalam Proses', 'Menunggu Data', 'Draft'],
            ],
            'rows' => [
                ['no' => 1, 'category' => 'Commissioning', 'form_type' => 'Commissioning Motor', 'equipment' => 'ID Fan IF-02', 'plant' => 'Cement Plant 2', 'area' => 'Raw Mill Area', 'updated_at' => '21 Mei 2025 10:15', 'status' => 'Menunggu Data'],
                ['no' => 2, 'category' => 'Commissioning', 'form_type' => 'Commissioning Mechanical', 'equipment' => 'Cooling Tower CT-01', 'plant' => 'Cement Plant 2', 'area' => 'Utilities Area', 'updated_at' => '20 Mei 2025 16:40', 'status' => 'Dalam Proses'],
                ['no' => 3, 'category' => 'Commissioning', 'form_type' => 'Commissioning Final Inspection', 'equipment' => 'Baghouse BH-01', 'plant' => 'Cement Plant 2', 'area' => 'Raw Mill Area', 'updated_at' => '19 Mei 2025 09:10', 'status' => 'Draft'],
            ],
            'continue_route' => 'user.commissioning.forms.create',
        ]);
    }

    public static function qcHistory(): array
    {
        return self::inspectionHistory('qc', [
            'title' => 'Riwayat QC',
            'subtitle' => 'Daftar form QC yang sudah dikirim dan siap dibuka sebagai PDF.',
            'rows' => [
                ['no' => 1, 'submitted_at' => '21 Mei 2025 15:30', 'form_no' => 'QC-250521-001', 'category' => 'QC', 'form_type' => 'QC Conveyor Inspection', 'equipment' => 'Conveyor CV-05', 'plant' => 'Cement Plant 1', 'area' => 'Coal Mill Area', 'status' => 'Disetujui'],
                ['no' => 2, 'submitted_at' => '21 Mei 2025 11:45', 'form_no' => 'QC-250521-003', 'category' => 'QC', 'form_type' => 'QC Motor Inspection', 'equipment' => 'Motor M-102', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Area', 'status' => 'Menunggu Review'],
                ['no' => 3, 'submitted_at' => '20 Mei 2025 13:00', 'form_no' => 'QC-250520-005', 'category' => 'QC', 'form_type' => 'QC Valve Inspection', 'equipment' => 'Valve V-120', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Area', 'status' => 'Perlu Revisi'],
            ],
        ]);
    }

    public static function commissioningHistory(): array
    {
        return self::inspectionHistory('commissioning', [
            'title' => 'Riwayat Commissioning',
            'subtitle' => 'Daftar form commissioning yang sudah dikirim.',
            'rows' => [
                ['no' => 1, 'submitted_at' => '21 Mei 2025 14:10', 'form_no' => 'COM-250521-002', 'category' => 'Commissioning', 'form_type' => 'Commissioning Static Equipment', 'equipment' => 'Baghouse BH-01', 'plant' => 'Cement Plant 2', 'area' => 'Raw Mill Area', 'status' => 'Disetujui'],
                ['no' => 2, 'submitted_at' => '20 Mei 2025 16:20', 'form_no' => 'COM-250520-004', 'category' => 'Commissioning', 'form_type' => 'Commissioning Handover', 'equipment' => 'Silo S-05', 'plant' => 'Cement Plant 2', 'area' => 'Packing Plant', 'status' => 'Disetujui'],
                ['no' => 3, 'submitted_at' => '20 Mei 2025 13:00', 'form_no' => 'COM-250520-005', 'category' => 'Commissioning', 'form_type' => 'Commissioning System Test', 'equipment' => 'Cooling Tower CT-01', 'plant' => 'Cement Plant 2', 'area' => 'Utilities Area', 'status' => 'Menunggu Review'],
            ],
        ]);
    }

    public static function qcDocuments(): array
    {
        return self::documentPage('qc', [
            'title' => 'Dokumen QC',
            'subtitle' => 'Daftar dokumen dan foto pendukung form QC.',
            'rows' => [
                ['no' => 1, 'name' => 'QC Air Compressor 01.pdf', 'form' => 'QC-250521-001', 'equipment' => 'Air Compressor AC-02', 'category' => 'QC', 'uploaded_at' => '21 Mei 2025', 'size' => '1.2 MB', 'status' => 'Tersimpan', 'file' => 'assets/docs/dummy-inspection-report.pdf'],
                ['no' => 2, 'name' => 'Foto Valve V-120.jpg', 'form' => 'QC-250520-005', 'equipment' => 'Valve V-120', 'category' => 'QC', 'uploaded_at' => '20 Mei 2025', 'size' => '850 KB', 'status' => 'Tersimpan', 'file' => 'assets/docs/dummy-inspection-report.pdf'],
            ],
        ]);
    }

    public static function commissioningDocuments(): array
    {
        return self::documentPage('commissioning', [
            'title' => 'Dokumen Commissioning',
            'subtitle' => 'Daftar dokumen dan foto pendukung form commissioning.',
            'rows' => [
                ['no' => 1, 'name' => 'Commissioning Boiler Unit 02.docx', 'form' => 'COM-250521-002', 'equipment' => 'Boiler Unit 02', 'category' => 'Commissioning', 'uploaded_at' => '21 Mei 2025', 'size' => '2.6 MB', 'status' => 'Tersimpan', 'file' => 'assets/docs/dummy-inspection-report.pdf'],
                ['no' => 2, 'name' => 'Checklist Cooling Tower.xlsx', 'form' => 'COM-250520-006', 'equipment' => 'Cooling Tower CT-01', 'category' => 'Commissioning', 'uploaded_at' => '20 Mei 2025', 'size' => '512 KB', 'status' => 'Tersimpan', 'file' => 'assets/docs/dummy-inspection-report.pdf'],
            ],
        ]);
    }

    public static function qcProfile(): array
    {
        return self::profilePage('qc');
    }

    public static function commissioningProfile(): array
    {
        return self::profilePage('commissioning');
    }

    public static function pgoDashboard(): array
    {
        return [
            'roleUi' => self::layout('pgo'),
            'hero' => [
                'title' => 'Dashboard PGO',
                'subtitle' => 'Pantau tugas dan dokumen PGO terkait pekerjaan overhaul.',
                'note' => '7 tugas aktif dan 3 item menunggu tindak lanjut',
                'actions' => [
                    ['label' => 'Lihat Tugas PGO', 'route' => 'user.pgo.tasks.index', 'icon' => 'bi-list-check'],
                    ['label' => 'Monitoring Pekerjaan', 'route' => 'user.pgo.monitoring.index', 'icon' => 'bi-activity'],
                    ['label' => 'Dokumen', 'route' => 'user.pgo.documents.index', 'icon' => 'bi-folder2-open'],
                ],
            ],
            'stats' => [
                ['label' => 'Tugas Aktif', 'value' => '7', 'icon' => 'bi-kanban', 'accent' => 'primary'],
                ['label' => 'Menunggu Tindak Lanjut', 'value' => '3', 'icon' => 'bi-hourglass-split', 'accent' => 'warning'],
                ['label' => 'Selesai', 'value' => '12', 'icon' => 'bi-check2-circle', 'accent' => 'success'],
                ['label' => 'Dokumen Masuk', 'value' => '8', 'icon' => 'bi-folder-check', 'accent' => 'secondary'],
            ],
            'tasks' => [
                ['no' => 1, 'date' => '22 Mei 2025', 'job' => 'Review kesiapan area Raw Mill', 'plant' => 'Cement Plant 1', 'area' => 'Raw Mill', 'pic' => 'PGO Andi', 'status' => 'Menunggu'],
                ['no' => 2, 'date' => '22 Mei 2025', 'job' => 'Verifikasi pekerjaan Kiln Shutdown', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Area', 'pic' => 'PGO Rudi', 'status' => 'Dalam Proses'],
                ['no' => 3, 'date' => '22 Mei 2025', 'job' => 'Cek dokumen pekerjaan Utilities', 'plant' => 'Cement Plant 2', 'area' => 'Utilities', 'pic' => 'PGO Sinta', 'status' => 'Selesai'],
            ],
            'monitoring' => [
                ['job' => 'Raw Mill Area Preparation', 'plant' => 'Cement Plant 1', 'area' => 'Raw Mill', 'progress' => '72%', 'status' => 'Dalam Proses'],
                ['job' => 'Kiln Shutdown Readiness', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Area', 'progress' => '46%', 'status' => 'Menunggu'],
                ['job' => 'Utilities Document Control', 'plant' => 'Cement Plant 2', 'area' => 'Utilities', 'progress' => '100%', 'status' => 'Selesai'],
            ],
            'history' => [
                ['time' => '21 Mei 2025 16:10', 'activity' => 'Menyetujui checklist kesiapan area Utilities', 'status' => 'Selesai'],
                ['time' => '21 Mei 2025 14:40', 'activity' => 'Meminta tindak lanjut dokumen Kiln Shutdown', 'status' => 'Menunggu Review'],
                ['time' => '21 Mei 2025 10:15', 'activity' => 'Memverifikasi pekerjaan Raw Mill Area', 'status' => 'Dalam Proses'],
            ],
        ];
    }

    public static function pgoTasks(): array
    {
        return [
            'roleUi' => self::layout('pgo'),
            'title' => 'Tugas PGO',
            'subtitle' => 'Pantau daftar tugas operasional PGO dan tindak lanjut lapangan.',
            'filters' => [
                'years' => ['2025', '2024', '2023'],
                'plants' => ['Semua Plant', 'Cement Plant 1', 'Cement Plant 2'],
                'areas' => ['Semua Area', 'Raw Mill', 'Kiln Area', 'Utilities'],
                'statuses' => ['Semua Status', 'Menunggu', 'Dalam Proses', 'Selesai'],
            ],
            'rows' => [
                ['no' => 1, 'date' => '22 Mei 2025', 'job' => 'Review kesiapan area Raw Mill', 'plant' => 'Cement Plant 1', 'area' => 'Raw Mill', 'pic' => 'PGO Andi', 'status' => 'Menunggu'],
                ['no' => 2, 'date' => '22 Mei 2025', 'job' => 'Verifikasi pekerjaan Kiln Shutdown', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Area', 'pic' => 'PGO Rudi', 'status' => 'Dalam Proses'],
                ['no' => 3, 'date' => '22 Mei 2025', 'job' => 'Cek dokumen pekerjaan Utilities', 'plant' => 'Cement Plant 2', 'area' => 'Utilities', 'pic' => 'PGO Sinta', 'status' => 'Selesai'],
            ],
        ];
    }

    public static function pgoMonitoring(): array
    {
        return [
            'roleUi' => self::layout('pgo'),
            'title' => 'Monitoring Pekerjaan',
            'subtitle' => 'Lihat progres pekerjaan overhaul dari perspektif PGO.',
            'cards' => [
                ['title' => 'Pekerjaan On Track', 'value' => '5', 'description' => 'Mayoritas pekerjaan berjalan sesuai target minggu ini.'],
                ['title' => 'Butuh Koordinasi', 'value' => '2', 'description' => 'Ada pekerjaan yang menunggu dokumen atau approval tambahan.'],
                ['title' => 'Dokumen Terbaru', 'value' => '8', 'description' => 'Dokumen baru masuk dan siap direview oleh tim PGO.'],
            ],
            'rows' => [
                ['job' => 'Raw Mill Area Preparation', 'plant' => 'Cement Plant 1', 'area' => 'Raw Mill', 'progress' => '72%', 'status' => 'Dalam Proses'],
                ['job' => 'Kiln Shutdown Readiness', 'plant' => 'Cement Plant 1', 'area' => 'Kiln Area', 'progress' => '46%', 'status' => 'Menunggu'],
                ['job' => 'Utilities Document Control', 'plant' => 'Cement Plant 2', 'area' => 'Utilities', 'progress' => '100%', 'status' => 'Selesai'],
            ],
        ];
    }

    public static function pgoHistory(): array
    {
        return [
            'roleUi' => self::layout('pgo'),
            'title' => 'Riwayat PGO',
            'subtitle' => 'Ringkasan aktivitas dan tindak lanjut yang sudah dilakukan oleh PGO.',
            'rows' => [
                ['time' => '21 Mei 2025 16:10', 'activity' => 'Menyetujui checklist kesiapan area Utilities', 'status' => 'Selesai'],
                ['time' => '21 Mei 2025 14:40', 'activity' => 'Meminta tindak lanjut dokumen Kiln Shutdown', 'status' => 'Menunggu Review'],
                ['time' => '21 Mei 2025 10:15', 'activity' => 'Memverifikasi pekerjaan Raw Mill Area', 'status' => 'Dalam Proses'],
            ],
        ];
    }

    public static function pgoDocuments(): array
    {
        return self::documentPage('pgo', [
            'title' => 'Dokumen PGO',
            'subtitle' => 'Daftar dokumen kerja dan lampiran operasional untuk tim PGO.',
            'rows' => [
                ['no' => 1, 'name' => 'Checklist Raw Mill Area.pdf', 'form' => 'PGO-250521-001', 'equipment' => 'Raw Mill Area', 'category' => 'PGO', 'uploaded_at' => '21 Mei 2025', 'size' => '1.1 MB', 'status' => 'Tersimpan', 'file' => 'assets/docs/dummy-inspection-report.pdf'],
                ['no' => 2, 'name' => 'Kiln Shutdown Note.docx', 'form' => 'PGO-250521-002', 'equipment' => 'Kiln Shutdown', 'category' => 'PGO', 'uploaded_at' => '21 Mei 2025', 'size' => '900 KB', 'status' => 'Tersimpan', 'file' => 'assets/docs/dummy-inspection-report.pdf'],
            ],
        ]);
    }

    public static function pgoProfile(): array
    {
        return self::profilePage('pgo');
    }

    public static function approvalDashboard(): array
    {
        return [
            'roleUi' => self::layout('approval'),
            'hero' => [
                'title' => 'Dashboard Approval',
                'subtitle' => 'Review dan tindak lanjuti form yang menunggu persetujuan.',
                'note' => '9 form menunggu approval aktif hari ini',
                'actions' => [
                    ['label' => 'Menunggu Approval', 'route' => 'user.approval.pending.index', 'icon' => 'bi-hourglass-split'],
                    ['label' => 'Riwayat Approval', 'route' => 'user.approval.history.index', 'icon' => 'bi-clock-history'],
                    ['label' => 'Dokumen', 'route' => 'user.approval.documents.index', 'icon' => 'bi-folder2-open'],
                ],
            ],
            'stats' => [
                ['label' => 'Menunggu Approval', 'value' => '9', 'icon' => 'bi-hourglass-split', 'accent' => 'warning'],
                ['label' => 'Disetujui', 'value' => '18', 'icon' => 'bi-check2-circle', 'accent' => 'success'],
                ['label' => 'Perlu Revisi', 'value' => '4', 'icon' => 'bi-arrow-repeat', 'accent' => 'danger'],
                ['label' => 'Ditolak', 'value' => '1', 'icon' => 'bi-x-circle', 'accent' => 'secondary'],
            ],
            'pending' => self::approvalPending()['rows'],
            'history' => self::approvalHistory()['rows'],
            'documents' => self::approvalDocuments()['rows'],
        ];
    }

    public static function approvalPending(): array
    {
        return [
            'roleUi' => self::layout('approval'),
            'title' => 'Menunggu Approval',
            'subtitle' => 'Daftar form yang menunggu review dan keputusan approval.',
            'filters' => [
                'years' => ['2025', '2024', '2023'],
                'categories' => ['Semua', 'QC', 'Commissioning', 'PGO'],
                'form_types' => ['Semua Jenis Form', 'QC Conveyor Inspection', 'Commissioning Static Equipment', 'QC Motor Inspection'],
                'plants' => ['Semua Plant', 'Cement Plant 1', 'Cement Plant 2'],
                'statuses' => ['Semua Status', 'Menunggu Approval', 'Perlu Review'],
            ],
            'rows' => [
                ['no' => 1, 'submitted_at' => '21 Mei 2025 15:30', 'form_no' => 'QC-250521-001', 'sender' => 'User QC', 'category' => 'QC', 'form_type' => 'QC Conveyor Inspection', 'equipment' => 'Conveyor CV-05', 'plant' => 'Cement Plant 1', 'status' => 'Menunggu Approval'],
                ['no' => 2, 'submitted_at' => '21 Mei 2025 14:10', 'form_no' => 'COM-250521-002', 'sender' => 'User Commissioning', 'category' => 'Commissioning', 'form_type' => 'Commissioning Static Equipment', 'equipment' => 'Baghouse BH-01', 'plant' => 'Cement Plant 2', 'status' => 'Menunggu Approval'],
                ['no' => 3, 'submitted_at' => '21 Mei 2025 11:45', 'form_no' => 'QC-250521-003', 'sender' => 'User QC', 'category' => 'QC', 'form_type' => 'QC Motor Inspection', 'equipment' => 'Motor M-102', 'plant' => 'Cement Plant 1', 'status' => 'Perlu Review'],
            ],
        ];
    }

    public static function approvalReview(): array
    {
        return [
            'roleUi' => self::layout('approval'),
            'title' => 'Review Form',
            'subtitle' => 'Contoh halaman review detail form sebelum approve, revisi, atau reject.',
            'form_info' => [
                'No Form' => 'QC-250521-001',
                'Kategori' => 'QC',
                'Jenis Form' => 'QC Conveyor Inspection',
                'Equipment' => 'Conveyor CV-05',
                'Plant' => 'Cement Plant 1',
                'Area' => 'Coal Mill Area',
                'Tanggal Submit' => '21 Mei 2025 15:30',
            ],
            'sender_info' => [
                'Pengirim' => 'User QC',
                'Email' => 'qc@ovh.test',
                'Role' => 'QC',
                'Status Saat Ini' => 'Menunggu Approval',
            ],
            'checklist' => [
                ['no' => 1, 'parameter' => 'Kebersihan Unit', 'standard' => 'Bebas debu dan material', 'result' => 'Sesuai', 'status' => 'OK', 'notes' => 'Unit bersih saat inspeksi.'],
                ['no' => 2, 'parameter' => 'Kondisi Struktur', 'standard' => 'Tidak ada retak/deformasi', 'result' => 'Tidak Sesuai', 'status' => 'Not OK', 'notes' => 'Perlu pengecekan support frame.'],
                ['no' => 3, 'parameter' => 'Instrumentasi', 'standard' => 'Berfungsi normal', 'result' => 'Perlu Verifikasi', 'status' => 'Follow Up', 'notes' => 'Gauge perlu kalibrasi ulang.'],
            ],
            'documents' => [
                ['name' => 'QC Air Compressor 01.pdf', 'type' => 'PDF', 'size' => '1.2 MB', 'file' => 'assets/docs/dummy-inspection-report.pdf'],
                ['name' => 'Foto Valve V-120.jpg', 'type' => 'JPG', 'size' => '850 KB', 'file' => 'assets/docs/dummy-inspection-report.pdf'],
            ],
        ];
    }

    public static function approvalHistory(): array
    {
        return [
            'roleUi' => self::layout('approval'),
            'title' => 'Riwayat Approval',
            'subtitle' => 'Riwayat keputusan approval yang telah diberikan.',
            'rows' => [
                ['time' => '21 Mei 2025 16:40', 'activity' => 'Menyetujui QC Conveyor Inspection - Conveyor CV-05', 'status' => 'Disetujui'],
                ['time' => '21 Mei 2025 15:20', 'activity' => 'Meminta revisi Commissioning Static Equipment - Baghouse BH-01', 'status' => 'Perlu Revisi'],
                ['time' => '20 Mei 2025 11:05', 'activity' => 'Menolak QC Valve Inspection - Valve V-120', 'status' => 'Ditolak'],
            ],
        ];
    }

    public static function approvalDocuments(): array
    {
        return self::documentPage('approval', [
            'title' => 'Dokumen Approval',
            'subtitle' => 'Dokumen pendukung yang digunakan dalam proses review dan approval.',
            'rows' => [
                ['no' => 1, 'name' => 'Approval Review Package.pdf', 'form' => 'APR-250521-001', 'equipment' => 'Conveyor CV-05', 'category' => 'Approval', 'uploaded_at' => '21 Mei 2025', 'size' => '1.5 MB', 'status' => 'Tersimpan', 'file' => 'assets/docs/dummy-inspection-report.pdf'],
                ['no' => 2, 'name' => 'Commissioning Review Note.docx', 'form' => 'APR-250521-002', 'equipment' => 'Baghouse BH-01', 'category' => 'Approval', 'uploaded_at' => '21 Mei 2025', 'size' => '740 KB', 'status' => 'Tersimpan', 'file' => 'assets/docs/dummy-inspection-report.pdf'],
            ],
        ]);
    }

    public static function approvalProfile(): array
    {
        return self::profilePage('approval');
    }

    private static function inspectionDashboard(string $role, array $config): array
    {
        $realData = self::realInspectionDashboardData($role);
        $equipmentData = in_array($role, ['qc', 'commissioning'], true) ? self::inspectionDashboardEquipmentData($role) : null;
        $equipmentRows = $equipmentData['rows'] ?? null;
        $stats = $realData['stats'] ?? $config['stats'];

        if ($equipmentRows !== null) {
            $stats = self::statsWithEquipmentSummary($stats, $equipmentData['summary'], $role);
        }

        return [
            'roleUi' => self::layout($role),
            'hero' => [
                'title' => $config['title'],
                'subtitle' => $config['subtitle'],
                'note' => $config['hero_note'],
                'actions' => $config['actions'],
            ],
            'stats' => $stats,
            'draftsTitle' => $config['drafts_title'],
            'historyTitle' => $config['history_title'],
            'drafts' => $realData['drafts'] ?? $config['drafts'],
            'history' => $realData['history'] ?? $config['history'],
            'equipmentRows' => $equipmentRows,
            'equipmentFilters' => $equipmentData['filters'] ?? null,
        ];
    }

    private static function statsWithEquipmentSummary(array $stats, array $summary, string $role): array
    {
        $notStartedLabel = $role === 'qc' ? 'Belum QC' : 'Belum Commissioning';
        $equipmentLabel = $role === 'qc' ? 'Equipment QC' : 'Equipment Commissioning';

        $equipmentStat = [
            'label' => $equipmentLabel,
            'value' => (string) ($summary['total'] ?? 0),
            'icon' => $role === 'qc' ? 'bi-shield-check' : 'bi-tools',
            'accent' => 'info',
            'meta' => sprintf(
                'Close %d | On Going %d | %s %d',
                (int) ($summary['close'] ?? 0),
                (int) ($summary['ongoing'] ?? 0),
                $notStartedLabel,
                (int) ($summary['not_started'] ?? 0)
            ),
        ];

        if (isset($stats[1])) {
            $stats[1] = $equipmentStat;

            return $stats;
        }

        $stats[] = $equipmentStat;

        return $stats;
    }

    private static function realInspectionDashboardData(string $role): ?array
    {
        $userId = auth()->id();

        if (! $userId || ! in_array($role, ['qc', 'commissioning'], true)) {
            return null;
        }

        $model = $role === 'qc' ? QcFormSubmission::class : CommissioningFormSubmission::class;
        $category = $role === 'qc' ? 'QC' : 'Commissioning';
        $draftLabel = $role === 'qc' ? 'Draft QC' : 'Draft Commissioning';
        $baseQuery = $model::query()->where('user_id', $userId);
        $historyStatuses = ['submitted', 'pending_approval', 'approved', 'revision', 'revision_required', 'rejected', 'cancelled'];

        $drafts = (clone $baseQuery)
            ->with('template')
            ->whereIn('status', ['draft', 'revision_required', 'revision'])
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Model $submission) => self::dashboardDraftRow($submission, $category))
            ->all();

        $history = (clone $baseQuery)
            ->with('template')
            ->whereIn('status', $historyStatuses)
            ->latest('submitted_at')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Model $submission) => self::dashboardHistoryRow($submission, $category))
            ->all();

        return [
            'stats' => [
                ['label' => $draftLabel, 'value' => (string) (clone $baseQuery)->where('status', 'draft')->count(), 'icon' => 'bi-pencil-square', 'accent' => 'warning'],
                ['label' => 'Perlu Revisi', 'value' => (string) (clone $baseQuery)->whereIn('status', ['revision', 'revision_required'])->count(), 'icon' => 'bi-arrow-repeat', 'accent' => 'danger'],
                ['label' => 'Menunggu Review', 'value' => (string) (clone $baseQuery)->whereIn('status', ['submitted', 'pending_approval'])->count(), 'icon' => 'bi-hourglass-split', 'accent' => 'warning'],
                ['label' => 'Form Terkirim', 'value' => (string) (clone $baseQuery)->whereIn('status', $historyStatuses)->count(), 'icon' => 'bi-send-check', 'accent' => 'success'],
                ['label' => 'Riwayat PDF', 'value' => (string) (clone $baseQuery)->whereIn('status', $historyStatuses)->count(), 'icon' => 'bi-file-earmark-pdf', 'accent' => 'secondary'],
            ],
            'drafts' => $drafts,
            'history' => $history,
        ];
    }

    private static function dashboardDraftRow(Model $submission, string $category): array
    {
        return [
            'category' => $category,
            'title' => $category.' - '.self::submissionEquipment($submission),
            'equipment' => self::submissionEquipment($submission),
            'plant' => self::submissionPlant($submission),
            'area' => self::submissionArea($submission),
            'status' => self::inspectionStatusLabel((string) $submission->getAttribute('status')),
            'updated_at' => self::dashboardDateTime($submission->getAttribute('updated_at')),
        ];
    }

    private static function dashboardHistoryRow(Model $submission, string $category): array
    {
        return [
            'submitted_at' => self::dashboardDateTime($submission->getAttribute('submitted_at') ?: $submission->getAttribute('updated_at')),
            'category' => $category,
            'type' => self::submissionTemplateName($submission, $category),
            'equipment' => self::submissionEquipment($submission),
            'plant' => self::submissionPlant($submission),
            'area' => self::submissionArea($submission),
            'status' => self::inspectionStatusLabel((string) $submission->getAttribute('status')),
        ];
    }

    private static function inspectionDashboardEquipmentData(string $role): array
    {
        $model = $role === 'qc' ? QcFormSubmission::class : CommissioningFormSubmission::class;
        $category = $role === 'qc' ? MasterDataRecord::CATEGORY_QC : MasterDataRecord::CATEGORY_COMMISSIONING;
        $createRoute = $role === 'qc' ? 'user.qc.forms.create' : 'user.commissioning.forms.create';
        $request = request();
        $filters = [
            'plant' => trim((string) $request->query('equipment_plant', '')),
            'area' => trim((string) $request->query('equipment_area', '')),
            'status' => trim((string) $request->query('equipment_status', '')),
        ];
        $perPage = 10;

        $submissions = $model::query()
            ->with('user')
            ->where(function ($query): void {
                $query->where('status', '!=', 'draft')
                    ->orWhereNotNull('user_id');
            })
            ->latest('submitted_at')
            ->latest()
            ->get();
        $orphanDrafts = $model::query()
            ->whereNull('user_id')
            ->where('status', 'draft')
            ->get();

        $baseRecordsQuery = MasterDataRecord::query()
            ->where('document_category', $category)
            ->where('status', 'active')
            ->when(self::preferredProfileAreas(), fn ($query, array $areas) => $query->whereIn('area', $areas));

        $plantOptions = (clone $baseRecordsQuery)
            ->whereNotNull('plant')
            ->distinct()
            ->orderBy('plant')
            ->pluck('plant')
            ->filter()
            ->values()
            ->all();
        $areaOptions = (clone $baseRecordsQuery)
            ->whereNotNull('area')
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->filter()
            ->values()
            ->all();

        $records = (clone $baseRecordsQuery)
            ->when($filters['plant'] !== '', fn ($query) => $query->where('plant', $filters['plant']))
            ->when($filters['area'] !== '', fn ($query) => $query->where('area', $filters['area']))
            ->orderBy('area')
            ->orderBy('section_no')
            ->orderBy('equipment_no')
            ->get()
            ->map(function (MasterDataRecord $record) use ($submissions, $orphanDrafts, $createRoute, $role): array {
                $submission = $submissions->first(fn (Model $submission) => self::inspectionSubmissionMatchesMasterRecord($submission, $record));
                $hasOrphanDraft = $orphanDrafts->contains(fn (Model $submission) => self::inspectionSubmissionMatchesMasterRecord($submission, $record));
                $workStatus = self::inspectionDashboardWorkStatus($record, $submission, $hasOrphanDraft);

                return [
                    'section_no' => $record->section_no ?: '-',
                    'equipment' => $record->description ?: '-',
                    'equipment_no' => $record->equipment_no ?: '-',
                    'functional_location' => $record->func_location ?: '-',
                    'plant' => $record->plant ?: '-',
                    'area' => $record->area ?: '-',
                    'status' => $workStatus,
                    'status_label' => self::inspectionDashboardWorkStatusLabel($workStatus, $role),
                    'status_accent' => self::inspectionDashboardWorkStatusAccent($workStatus),
                    'form_type' => $role === 'qc' && $submission
                        ? self::submissionTemplateName($submission, 'QC')
                        : null,
                    'form_number' => $submission?->form_number,
                    'submitted_by' => $submission?->user?->name,
                    'submitted_at' => $submission ? self::dashboardDateTime($submission->submitted_at ?: $submission->updated_at) : null,
                    'create_url' => $workStatus === 'not_started'
                        ? route($createRoute, [
                            'master_data_record_id' => $record->id,
                            'area' => $record->area,
                        ])
                        : null,
                ];
            })
            ->filter(fn (array $row) => $filters['status'] === '' || $row['status'] === $filters['status'])
            ->sortBy(fn (array $row): string => self::inspectionDashboardWorkStatusRank($row['status']).'|'.$row['area'].'|'.$row['section_no'])
            ->values();

        $summary = $records->countBy('status')->all();
        $summary['total'] = $records->count();
        $page = LengthAwarePaginator::resolveCurrentPage('equipment_page');
        $rows = new LengthAwarePaginator(
            $records->forPage($page, $perPage)->values(),
            $records->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => 'equipment_page',
                'query' => $request->query(),
            ]
        );

        return [
            'rows' => $rows,
            'summary' => $summary,
            'filters' => [
                'plant' => $filters['plant'],
                'area' => $filters['area'],
                'status' => $filters['status'],
                'plants' => $plantOptions,
                'areas' => $areaOptions,
                'statuses' => [
                    ['value' => 'close', 'label' => 'Close'],
                    ['value' => 'ongoing', 'label' => 'On Going'],
                    ['value' => 'not_started', 'label' => $role === 'qc' ? 'Belum QC' : 'Belum Commissioning'],
                ],
            ],
        ];
    }

    private static function inspectionSubmissionMatchesMasterRecord(Model $submission, MasterDataRecord $record): bool
    {
        $header = $submission instanceof QcFormSubmission
            ? ($submission->general_info ?? [])
            : ($submission->header_data ?? []);
        $functionalLocation = $submission instanceof QcFormSubmission
            ? ($header['functional_location'] ?? null)
            : $submission->functional_location;
        $equipmentNo = $submission instanceof QcFormSubmission
            ? ($header['id_equipment'] ?? null)
            : $submission->equipment_no;

        return (filled($header['master_data_record_id'] ?? null) && (string) $header['master_data_record_id'] === (string) $record->id)
            || (filled($functionalLocation) && (string) $functionalLocation === (string) $record->func_location)
            || MasterDataIdentity::equipmentNumbersMatch($equipmentNo, $record->equipment_no);
    }

    private static function inspectionDashboardWorkStatus(
        MasterDataRecord $record,
        ?Model $submission,
        bool $hasOrphanDraft = false
    ): string
    {
        if ($record->inspection_status === 'close') {
            return 'close';
        }

        if ($record->inspection_status === 'ongoing' && ! $hasOrphanDraft) {
            return $record->inspection_status;
        }

        if (! $submission) {
            return 'not_started';
        }

        return $submission->status === 'draft' ? 'ongoing' : 'close';
    }

    private static function inspectionDashboardWorkStatusLabel(string $status, string $role): string
    {
        return [
            'close' => 'Close',
            'ongoing' => 'On Going',
            'not_started' => $role === 'qc' ? 'Belum QC' : 'Belum Commissioning',
        ][$status] ?? str($status)->replace('_', ' ')->headline()->toString();
    }

    private static function inspectionDashboardWorkStatusAccent(string $status): string
    {
        return [
            'close' => 'success',
            'ongoing' => 'warning',
            'not_started' => 'secondary',
        ][$status] ?? 'secondary';
    }

    private static function inspectionDashboardWorkStatusRank(string $status): int
    {
        return [
            'close' => 0,
            'ongoing' => 1,
            'not_started' => 2,
        ][$status] ?? 3;
    }

    private static function submissionTemplateName(Model $submission, string $fallback): string
    {
        return (string) (
            $submission->getAttribute('template_name')
            ?: data_get($submission->getAttribute('template_snapshot'), 'name')
            ?: $submission->getRelationValue('template')?->name
            ?: $fallback
        );
    }

    private static function submissionEquipment(Model $submission): string
    {
        return (string) (
            $submission->getAttribute('equipment')
            ?: data_get($submission->getAttribute('general_info'), 'name_equipment')
            ?: data_get($submission->getAttribute('general_info'), 'alat')
            ?: data_get($submission->getAttribute('header_data'), 'name_equipment')
            ?: $submission->getAttribute('form_number')
            ?: '-'
        );
    }

    private static function submissionPlant(Model $submission): string
    {
        return (string) (
            $submission->getAttribute('plant')
            ?: data_get($submission->getAttribute('general_info'), 'plant')
            ?: data_get($submission->getAttribute('general_info'), 'ovh_plant')
            ?: data_get($submission->getAttribute('header_data'), 'plant')
            ?: '-'
        );
    }

    private static function submissionArea(Model $submission): string
    {
        return (string) (
            $submission->getAttribute('area')
            ?: data_get($submission->getAttribute('general_info'), 'area')
            ?: data_get($submission->getAttribute('header_data'), 'area')
            ?: '-'
        );
    }

    private static function inspectionStatusLabel(string $status): string
    {
        return [
            'draft' => 'Draft',
            'submitted' => 'Menunggu Review',
            'pending_approval' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'revision' => 'Perlu Revisi',
            'revision_required' => 'Perlu Revisi',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ][$status] ?? str($status)->replace('_', ' ')->headline()->toString();
    }

    private static function dashboardDateTime(mixed $value): string
    {
        if (! $value) {
            return '-';
        }

        $date = $value instanceof \DateTimeInterface
            ? \Illuminate\Support\Carbon::instance($value)
            : \Illuminate\Support\Carbon::parse($value);

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        return $date->format('d').' '.$months[(int) $date->format('n')].' '.$date->format('Y H:i');
    }

    private static function inspectionForm(string $role, array $config): array
    {
        $templates = self::templateCatalog();
        $category = $config['template_category'];

        return [
            'roleUi' => self::layout($role),
            'pageTitle' => $config['page_title'],
            'pageSubtitle' => $config['page_subtitle'],
            'templateCatalog' => [$category => $templates[$category]],
            'defaultCategory' => $category,
            'defaultTemplate' => $config['default_template'],
            'templateSummary' => [
                'total' => count($templates[$category]),
                'category' => count($templates[$category]),
                'label' => $category,
            ],
            'generalInfo' => [
                'year' => '2025',
                'plant' => 'Cement Plant 1',
                'area' => 'Raw Mill Area',
                'equipment' => $role === 'qc' ? 'Pump P-204' : 'Cooling Tower CT-01',
                'inspection_date' => '2025-05-22',
                'inspector' => $role === 'qc' ? 'User QC' : 'User Commissioning',
                'date_label' => $config['date_label'],
            ],
            'steps' => ['Identitas', 'Checklist', 'Hasil', 'Dokumentasi', 'Review'],
            'checklist' => [
                ['no' => 1, 'parameter' => 'Kebersihan Unit', 'standard' => 'Bebas debu dan material', 'result' => 'Sesuai', 'status' => 'OK', 'notes' => 'Area kerja bersih sebelum inspeksi dimulai.'],
                ['no' => 2, 'parameter' => 'Kondisi Struktur', 'standard' => 'Tidak ada retak/deformasi', 'result' => 'Tidak Sesuai', 'status' => 'Not OK', 'notes' => 'Ditemukan deformasi ringan pada support frame sisi utara.'],
                ['no' => 3, 'parameter' => 'Kondisi Valve', 'standard' => 'Tidak bocor, berfungsi baik', 'result' => 'Sesuai', 'status' => 'OK', 'notes' => 'Valve utama responsif dan tidak ditemukan kebocoran.'],
                ['no' => 4, 'parameter' => 'Instrumentasi', 'standard' => 'Berfungsi normal & akurat', 'result' => 'Perlu Verifikasi', 'status' => 'Follow Up', 'notes' => 'Pressure gauge perlu kalibrasi ulang sebelum start-up.'],
                ['no' => 5, 'parameter' => 'Pengencangan Bolt', 'standard' => 'Torsi sesuai spesifikasi', 'result' => 'Sesuai', 'status' => 'OK', 'notes' => 'Random check bolt utama memenuhi spesifikasi.'],
            ],
            'guides' => [
                'Pilih template yang paling sesuai dengan pekerjaan lapangan hari ini.',
                'Lengkapi identitas umum sebelum mengisi checklist agar draft mudah ditelusuri.',
                'Gunakan status Follow Up jika item perlu verifikasi ulang atau menunggu bukti tambahan.',
                'Upload minimal satu foto overview unit dan satu bukti close-up untuk item kritis.',
            ],
            'uploads' => [
                ['name' => 'pump-overview.jpg', 'type' => 'JPG', 'size' => '1.8 MB'],
                ['name' => 'valve-closeup.png', 'type' => 'PNG', 'size' => '2.1 MB'],
                ['name' => 'inspection-note.pdf', 'type' => 'PDF', 'size' => '650 KB'],
            ],
            'qcRecord' => $role === 'qc' ? [
                'title' => 'Quality Control Record',
                'report_no' => 'QCR-CR-250522-001',
                'plant' => 'Cement Plant 1',
                'year' => '2025',
                'unit' => 'Crusher Area',
                'tag_num' => 'CR-ROT-01',
                'job' => 'Penggantian Hammer Set, Grate Basket',
                'equipment' => 'Crusher Rotor',
                'start_date' => '22 Mei 2025',
                'duration' => '8 Jam',
                'rows' => [
                    ['category' => 'System Penggerak', 'item' => 'Motor', 'standard' => 'Ampere normal', 'status' => '', 'notes' => ''],
                    ['category' => 'System Penggerak', 'item' => 'Motor', 'standard' => 'Vibrasi normal', 'status' => '', 'notes' => ''],
                    ['category' => 'Crusher', 'item' => 'Baut Liner', 'standard' => 'Tidak longgar', 'status' => '', 'notes' => ''],
                ],
            ] : null,
            'commissioningRecord' => $role === 'commissioning' ? [
                'title' => 'Form Commissioning Semen Tonasa - Overhaul Tonasa 4',
                'document' => [
                    'doc_number' => 'COM-T4-EI-001',
                    'process_area' => 'Overhaul Tonasa 4',
                    'drwg_reference' => 'Electrical / Instrumentation',
                    'date' => '2025-05-22',
                    'time' => '08:00',
                    'discipline' => 'Electrical / Instrumentation',
                ],
                'motor_data' => [
                    'equipment_name' => 'Main Drive Motor',
                    'model_type' => 'TEFC Induction Motor',
                    'tag_number' => 'MTR-RM-01',
                    'ip' => 'IP55',
                    'function_of' => 'Raw Mill Drive',
                    'brand' => 'Siemens',
                ],
                'motor_rating' => [
                    'tag_number' => 'MTR-RM-01',
                    'power_kw' => '250',
                    'current_a' => '420',
                    'voltage_v' => '400',
                    'freq_hz' => '50',
                    'remarks' => 'Name plate sesuai',
                ],
                'checks' => [
                    'Bolt and nut tightness at motor junction box',
                    'Check grounding connections',
                    'Check section number motor equipment',
                    'Check tag & wire number',
                    'Check isolation power cable',
                    'Cable glands, clamping and termination',
                    'Inspect interlock & control correctly operate',
                    'Megger test',
                    'Connection check (for I/O)',
                    'Loop test',
                    'Test & setting for relay',
                    'Penutup kabel lampu penerangan',
                    'Final housekeeping area motor',
                ],
                'motor_test_rows' => 4,
                'vibration_points' => ['Horizontal (X)', 'Horizontal (Y)', 'Vertical (Z)'],
                'prepared_by' => 'OVERHAUL MANAGEMENT',
                'checked_by' => 'COMMISSIONING LEADER',
                'area_leader' => 'AREA LEADER',
                'approved_by' => 'AREA OWNER',
                'rms_standard' => [
                    'Power <= 15 kW : < 4.5 mm/s',
                    '15 kW < Power <= 300 kW : < 7.1 mm/s',
                    '300 kW < Power <= 10 MW : < 11.2 mm/s',
                ],
            ] : null,
            'formTitle' => $config['form_title'],
            'resultTitle' => $config['result_title'],
            'resultNote' => $config['result_note'],
            'findingTitle' => $config['finding_title'],
            'findingNote' => $config['finding_note'],
        ];
    }

    private static function inspectionDrafts(string $role, array $config): array
    {
        return [
            'roleUi' => self::layout($role),
            'title' => $config['title'],
            'subtitle' => $config['subtitle'],
            'filters' => $config['filters'],
            'rows' => $config['rows'],
            'continueRoute' => $config['continue_route'],
        ];
    }

    private static function inspectionHistory(string $role, array $config): array
    {
        return [
            'roleUi' => self::layout($role),
            'title' => $config['title'],
            'subtitle' => $config['subtitle'],
            'filters' => [
                'years' => ['2025', '2024', '2023'],
                'categories' => ['Semua', strtoupper($role === 'qc' ? 'qc' : 'commissioning')],
                'form_types' => ['Semua Jenis Form'],
                'plants' => ['Semua Plant', 'Cement Plant 1', 'Cement Plant 2'],
                'areas' => ['Semua Area', 'Coal Mill Area', 'Raw Mill Area', 'Kiln Area', 'Packing Plant', 'Utilities Area'],
                'statuses' => ['Semua Status', 'Disetujui', 'Menunggu Review', 'Perlu Revisi'],
            ],
            'rows' => $config['rows'],
            'pdfAsset' => 'assets/docs/dummy-inspection-report.pdf',
        ];
    }

    private static function documentPage(string $role, array $config): array
    {
        return [
            'roleUi' => self::layout($role),
            'title' => $config['title'],
            'subtitle' => $config['subtitle'],
            'filters' => [
                'years' => ['2025', '2024', '2023'],
                'categories' => ['Semua', strtoupper($role)],
                'form_types' => ['Semua Jenis Form'],
                'equipment' => ['Semua Equipment', 'Conveyor CV-05', 'Cooling Tower CT-01', 'Raw Mill Area'],
                'plants' => ['Semua Plant', 'Cement Plant 1', 'Cement Plant 2'],
                'document_types' => ['Semua Tipe', 'PDF', 'DOCX', 'JPG', 'XLSX'],
            ],
            'rows' => $config['rows'],
        ];
    }

    private static function profilePage(string $role): array
    {
        $user = auth()->user();
        $profile = self::profileData($role);

        return [
            'roleUi' => self::layout($role),
            'profile' => $profile,
            'stats' => self::profileStats($role, $user?->getKey()),
            'security' => [
                'last_change' => $user?->updated_at?->format('d M Y') ?? '-',
                'last_login' => '-',
                'devices' => '-',
            ],
        ];
    }

    private static function profileData(string $role): array
    {
        $user = auth()->user();
        $category = match ($role) {
            'qc' => MasterDataRecord::CATEGORY_QC,
            'commissioning' => MasterDataRecord::CATEGORY_COMMISSIONING,
            default => null,
        };

        return [
            'name' => $user?->name ?? '-',
            'email' => $user?->email ?? '-',
            'phone' => $user?->phone,
            'photo_url' => $user?->profilePhotoUrl(),
            'usertype' => self::profileUsertypeLabel($user?->usertype),
            'role' => self::layout($role)['role_label'],
            'plants' => self::profilePlants($category),
            'areas' => self::profileAreas($category),
            'area_options' => self::profileMasterDataOptions('area', $category),
            'position' => self::profilePositionLabel($role),
        ];
    }

    private static function profileStats(string $role, mixed $userId): array
    {
        if (! in_array($role, ['qc', 'commissioning'], true)) {
            return self::profileStatsPayload(0, 0, 0, 0);
        }

        $model = $role === 'qc'
            ? QcFormSubmission::class
            : CommissioningFormSubmission::class;

        if (! $userId) {
            return self::profileStatsPayload(0, 0, 0, 0);
        }

        $baseQuery = $model::query()->where('user_id', $userId);
        $attachmentCount = (clone $baseQuery)
            ->withCount('attachments')
            ->get()
            ->sum('attachments_count');

        return self::profileStatsPayload(
            (clone $baseQuery)->whereNotNull('submitted_at')->where('status', '<>', 'draft')->count(),
            (clone $baseQuery)->where('status', 'draft')->count(),
            (clone $baseQuery)->whereIn('status', ['revision', 'revision_required'])->count(),
            $attachmentCount,
        );
    }

    private static function profileStatsPayload(int $submitted, int $drafts, int $revisions, int $attachments): array
    {
        return [
            ['label' => 'Total Form Terkirim', 'value' => (string) $submitted, 'icon' => 'bi-send-check', 'accent' => 'success'],
            ['label' => 'Draft', 'value' => (string) $drafts, 'icon' => 'bi-journal-richtext', 'accent' => 'warning'],
            ['label' => 'Perlu Revisi', 'value' => (string) $revisions, 'icon' => 'bi-arrow-repeat', 'accent' => 'danger'],
            ['label' => 'Dokumen Uploaded', 'value' => (string) $attachments, 'icon' => 'bi-file-earmark-arrow-up', 'accent' => 'info'],
        ];
    }

    private static function profileMasterDataOptions(string $column, ?string $category = null): array
    {
        return MasterDataRecord::query()
            ->when($category, fn ($query) => $query->where('document_category', $category))
            ->where('status', 'active')
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

    private static function profileAreas(?string $category): array
    {
        $areas = self::preferredProfileAreas();

        return $areas ?: self::profileMasterDataOptions('area', $category);
    }

    private static function profilePlants(?string $category): array
    {
        $user = auth()->user();
        $plants = collect($user?->profile_plants ?? [])->filter()->values()->all();

        if ($plants) {
            return $plants;
        }

        $areas = self::preferredProfileAreas();

        if (! $areas) {
            return self::profileMasterDataOptions('plant', $category);
        }

        return MasterDataRecord::query()
            ->when($category, fn ($query) => $query->where('document_category', $category))
            ->where('status', 'active')
            ->whereIn('area', $areas)
            ->whereNotNull('plant')
            ->where('plant', '<>', '')
            ->distinct()
            ->orderBy('plant')
            ->pluck('plant')
            ->all();
    }

    private static function preferredProfileAreas(): array
    {
        return collect(auth()->user()?->profile_areas ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private static function profileUsertypeLabel(?string $usertype): string
    {
        return match ($usertype) {
            'admin' => 'Admin',
            'user' => 'User',
            default => '-',
        };
    }

    private static function profilePositionLabel(string $role): string
    {
        return match ($role) {
            'qc' => 'QC Inspector',
            'commissioning' => 'Commissioning Inspector',
            'pgo' => 'PGO',
            'approval' => 'Approval',
            default => '-',
        };
    }

    private static function templateCatalog(): array
    {
        return [
            'QC' => [
                'Standard QCR Penggantian Crusher Rotor; Hammer Set, Grate Basket',
                'Standard QCR Penggantian Belt Conveyor',
                'Standard QCR Penggantian Crusher Rotor; Segment Teeth Rotor',
                'Standard QCR Penggantian MMC Cooler; Drive Plate',
                'Standard QCR Penggantian Coal Mill; Split Seal Hub Roller',
                'Standard QCR Penggantian Wearing Tyre Kiln',
                'Standard QCR Inspeksi Separator Atox Mill; Guide Vane Separator',
                'Standard QCR Inspeksi Blower RBS 126 & RBS 155',
                'Standard QCR Penggantian Firebrick',
                'Standard QCR Penggantian Rotary Kiln; Castable Casting Burner gun',
                'Standard QCR Penggantian Rotary Kiln; Castable Overhaul',
                'Standard QCR Inspeksi Raw Mill; Maag Gear WPU-302; Inner Part Gear Box V.1',
                'Standard QCR Inspeksi Raw Mill; Maag Gear WPU-302; Inner Part Gear Box V.2-V.7',
                'Standard QCR Penggantian Raw Mill; Bearing ke Hub Roller NU Bearing & Spherical B.',
                'Standard QCR Penggantian Roller Assembly; Roller 1 ke Center Piece',
                'Standard QCR Penggantian Roller Assembly; Tension Pull Rod',
                'Standard QCR Penggantian Wear Segment Roller; Hub Roller',
                'Standard QCR Penggantian Wear Segment Roller; Torsi Baut',
                'Standard QCR Inspeksi Limestone Crusher;Magnetic Separator',
                'Standard QCR Inspeksi Grate Cooler; Instrument Field Crossbar',
                'Standard QCR Inspeksi Kiln Feed; Instrument Field',
                'Standard QCR Inspeksi Coal Feeder; Rotor',
                'Standard QCR Inspeksi Gas Analyzer; Inlet',
                'Standard QCR Inspeksi Gas Analyzer; Top',
                'Standard QCR Inspeksi ESP; Air Load Test',
                'Standard QCR Inspeksi ESP; Test Tegangan Tembus Inner Part Raw Mill 533EP01',
                'Standard QCR Inspeksi Inner Part EP02',
                'Standard QCR Penggantian Bag Filter; Bag Cloth',
                'Standard QCR Penggantian MBF Coal Mill; Dinding',
                'Standard QCR Penggantian ESP Grate Cooler; Dinding',
            ],
            'Commissioning' => [
                'Commissioning Mechanical',
                'Commissioning Electrical',
                'Commissioning Instrument',
                'Commissioning Pump',
                'Commissioning Motor',
                'Commissioning Conveyor',
                'Commissioning System Test',
                'Commissioning Loop Test',
                'Commissioning Functional Test',
                'Commissioning Performance Test',
                'Commissioning Safety Interlock',
                'Commissioning Control System',
                'Commissioning Utility System',
                'Commissioning Rotating Equipment',
                'Commissioning Static Equipment',
                'Commissioning Final Inspection',
                'Commissioning Handover',
            ],
        ];
    }
}
