<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\QcSubmissionController as AdminQcSubmissionController;
use App\Http\Controllers\Admin\TemplateFormCommissioningController;
use App\Http\Controllers\Admin\TemplateFormQcController;
use App\Http\Controllers\Admin\UserPanelController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PublicApprovalController;
use App\Http\Controllers\User\Approval\DashboardController as ApprovalDashboardController;
use App\Http\Controllers\User\Approval\DocumentController as ApprovalDocumentController;
use App\Http\Controllers\User\Approval\HistoryController as ApprovalHistoryController;
use App\Http\Controllers\User\Approval\PendingController as ApprovalPendingController;
use App\Http\Controllers\User\Approval\ProfileController as ApprovalProfileController;
use App\Http\Controllers\User\Approval\ReviewController as ApprovalReviewController;
use App\Http\Controllers\User\Commissioning\DashboardController as CommissioningDashboardController;
use App\Http\Controllers\User\Commissioning\DraftController as CommissioningDraftController;
use App\Http\Controllers\User\Commissioning\FormController as CommissioningFormController;
use App\Http\Controllers\User\Commissioning\HistoryController as CommissioningHistoryController;
use App\Http\Controllers\User\Commissioning\ProfileController as CommissioningProfileController;
use App\Http\Controllers\User\Pgo\DashboardController as PgoDashboardController;
use App\Http\Controllers\User\Pgo\DocumentController as PgoDocumentController;
use App\Http\Controllers\User\Pgo\HistoryController as PgoHistoryController;
use App\Http\Controllers\User\Pgo\MonitoringController as PgoMonitoringController;
use App\Http\Controllers\User\Pgo\ProfileController as PgoProfileController;
use App\Http\Controllers\User\Pgo\TaskController as PgoTaskController;
use App\Http\Controllers\User\Qc\DashboardController as QcDashboardController;
use App\Http\Controllers\User\Qc\DraftController as QcDraftController;
use App\Http\Controllers\User\Qc\FormController as QcFormController;
use App\Http\Controllers\User\Qc\HistoryController as QcHistoryController;
use App\Http\Controllers\User\Qc\ProfileController as QcProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->dashboardRouteName());
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
    // REGISTRATION_DISABLED: akun baru untuk sementara hanya dibuat dari Admin > Userpanel.
    // Aktifkan lagi public register dengan membuka komentar dua route di bawah ini.
    // Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    // Route::post('/register', [RegisterController::class, 'register'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/approval/{token}', [PublicApprovalController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('public.approval.show');
Route::get('/approval/{token}/pdf', [PublicApprovalController::class, 'pdf'])
    ->middleware('throttle:20,1')
    ->name('public.approval.pdf');
Route::get('/approval/signed-pdf/{step}', [PublicApprovalController::class, 'signedPdf'])
    ->middleware(['signed', 'throttle:20,1'])
    ->name('public.approval.signed-pdf');
Route::post('/approval/{token}/approve', [PublicApprovalController::class, 'approve'])
    ->middleware('throttle:10,1')
    ->name('public.approval.approve');
Route::post('/approval/{token}/reject', [PublicApprovalController::class, 'reject'])
    ->middleware('throttle:10,1')
    ->name('public.approval.reject');

Route::middleware(['auth', 'usertype:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/overview', [AdminDashboardController::class, 'overview'])->name('overview');
    Route::get('/procurement', [AdminDashboardController::class, 'procurement'])->name('procurement');
    Route::get('/kalender-overhaul', [AdminDashboardController::class, 'kalenderOverhaul'])->name('kalender-overhaul');
    Route::get('/schedule', [AdminDashboardController::class, 'schedule'])->name('schedule');
    Route::get('/commissioning', [AdminDashboardController::class, 'commissioning'])->name('commissioning');
    Route::get('/commissioning/submissions/{submission}/pdf', [CommissioningFormController::class, 'pdf'])->name('commissioning.submissions.pdf');
    Route::post('/commissioning/submissions/{submission}/approval-link', [CommissioningFormController::class, 'approvalLink'])->name('commissioning.submissions.approval-link');
    Route::get('/qc', [AdminDashboardController::class, 'qc'])->name('qc');
    Route::prefix('qc/submissions')->name('qc.submissions.')->group(function () {
        Route::get('/', [AdminQcSubmissionController::class, 'index'])->name('index');
        Route::get('/{submission}/pdf', [AdminQcSubmissionController::class, 'pdf'])->name('pdf');
        Route::post('/{submission}/approval-link', [QcFormController::class, 'approvalLink'])->name('approval-link');
    });
    Route::prefix('template-form-qc')->name('template-form-qc.')->group(function () {
        Route::get('/', [TemplateFormQcController::class, 'index'])->name('index');
        Route::get('/create', [TemplateFormQcController::class, 'create'])->name('create');
        Route::post('/', [TemplateFormQcController::class, 'store'])->name('store');
        Route::patch('/{template}/publish', [TemplateFormQcController::class, 'publish'])->name('publish');
        Route::get('/{template}', [TemplateFormQcController::class, 'show'])->name('show');
        Route::get('/{template}/edit', [TemplateFormQcController::class, 'edit'])->name('edit');
        Route::put('/{template}', [TemplateFormQcController::class, 'update'])->name('update');
        Route::delete('/{template}', [TemplateFormQcController::class, 'destroy'])->name('destroy');
        Route::get('/{template}/preview', [TemplateFormQcController::class, 'preview'])->name('preview');
        Route::post('/{template}/duplicate', [TemplateFormQcController::class, 'duplicate'])->name('duplicate');
        Route::patch('/{template}/toggle-status', [TemplateFormQcController::class, 'toggleStatus'])->name('toggle-status');
    });
    Route::prefix('template-form-commissioning')->name('template-form-commissioning.')->group(function () {
        Route::get('/', [TemplateFormCommissioningController::class, 'index'])->name('index');
        Route::get('/create', [TemplateFormCommissioningController::class, 'create'])->name('create');
        Route::post('/', [TemplateFormCommissioningController::class, 'store'])->name('store');
        Route::get('/{template}/preview', [TemplateFormCommissioningController::class, 'preview'])->name('preview');
        Route::get('/{template}/edit', [TemplateFormCommissioningController::class, 'edit'])->name('edit');
        Route::put('/{template}', [TemplateFormCommissioningController::class, 'update'])->name('update');
        Route::post('/{template}/duplicate', [TemplateFormCommissioningController::class, 'duplicate'])->name('duplicate');
        Route::patch('/{template}/toggle-status', [TemplateFormCommissioningController::class, 'toggleStatus'])->name('toggle-status');
        Route::patch('/{template}/publish', [TemplateFormCommissioningController::class, 'publish'])->name('publish');
        Route::get('/{template}', [TemplateFormCommissioningController::class, 'show'])->name('show');
        Route::delete('/{template}', [TemplateFormCommissioningController::class, 'destroy'])->name('destroy');
    });
    Route::get('/equipment', [AdminDashboardController::class, 'equipment'])->name('equipment');
    Route::get('/mom', [AdminDashboardController::class, 'mom'])->name('mom');
    Route::redirect('/dokument', '/admin/dokumen');
    Route::get('/dokumen', [AdminDashboardController::class, 'dokumen'])->name('dokumen');
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data');
    Route::post('/master-data', [MasterDataController::class, 'store'])->name('master-data.store');
    Route::patch('/master-data/bulk-status', [MasterDataController::class, 'bulkStatus'])->name('master-data.bulk-status');
    Route::patch('/master-data/bulk-filtered-status', [MasterDataController::class, 'bulkFilteredStatus'])->name('master-data.bulk-filtered-status');
    Route::patch('/master-data/{masterDataRecord}/inspection-status', [MasterDataController::class, 'updateInspectionStatus'])->name('master-data.inspection-status');
    Route::put('/master-data/{masterDataRecord}', [MasterDataController::class, 'update'])->name('master-data.update');
    Route::delete('/master-data/{masterDataRecord}', [MasterDataController::class, 'destroy'])->name('master-data.destroy');
    Route::get('/user-panel', [UserPanelController::class, 'index'])->name('user-panel');
    Route::post('/user-panel', [UserPanelController::class, 'store'])->name('user-panel.store');
    Route::put('/user-panel/{user}', [UserPanelController::class, 'update'])->name('user-panel.update');
});

Route::prefix('user')->name('user.')->middleware(['auth', 'usertype:user'])->group(function () {
    Route::prefix('qc')->name('qc.')->middleware('role:qc')->group(function () {
        Route::get('/', fn () => redirect()->route('user.qc.dashboard'));
        Route::get('/dashboard', [QcDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/forms/create', [QcFormController::class, 'create'])->name('forms.create');
        Route::post('/forms', [QcFormController::class, 'store'])->name('forms.store');
        Route::get('/drafts', [QcDraftController::class, 'index'])->name('drafts.index');
        Route::get('/history', [QcHistoryController::class, 'index'])->name('history.index');
        Route::redirect('/documents', '/user/qc/history')->name('documents.index');
        Route::get('/submissions/{submission}/edit', [QcFormController::class, 'edit'])->name('submissions.edit');
        Route::patch('/submissions/{submission}', [QcFormController::class, 'update'])->name('submissions.update');
        Route::get('/submissions/{submission}', [QcFormController::class, 'show'])->name('submissions.show');
        Route::post('/submissions/{submission}/approval-link', [QcFormController::class, 'approvalLink'])->name('submissions.approval-link');
        Route::get('/submissions/{submission}/pdf', [QcFormController::class, 'pdf'])->name('submissions.pdf');
        Route::get('/attachments/{attachment}', [QcFormController::class, 'attachment'])->name('attachments.show');
        Route::delete('/submissions/{submission}', [QcFormController::class, 'destroy'])->name('submissions.destroy');
        Route::get('/profile', [QcProfileController::class, 'show'])->name('profile');
        Route::patch('/profile', [QcProfileController::class, 'update'])->name('profile.update');
    });

    Route::prefix('commissioning')->name('commissioning.')->middleware('role:commissioning')->group(function () {
        Route::get('/', fn () => redirect()->route('user.commissioning.dashboard'));
        Route::get('/dashboard', [CommissioningDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/forms/create', [CommissioningFormController::class, 'create'])->name('forms.create');
        Route::post('/forms', [CommissioningFormController::class, 'store'])->name('forms.store');
        Route::get('/drafts', [CommissioningDraftController::class, 'index'])->name('drafts.index');
        Route::get('/history', [CommissioningHistoryController::class, 'index'])->name('history.index');
        Route::redirect('/documents', '/user/commissioning/history')->name('documents.index');
        Route::get('/submissions/{submission}/edit', [CommissioningFormController::class, 'edit'])->name('submissions.edit');
        Route::patch('/submissions/{submission}', [CommissioningFormController::class, 'update'])->name('submissions.update');
        Route::get('/submissions/{submission}', [CommissioningFormController::class, 'show'])->name('submissions.show');
        Route::post('/submissions/{submission}/approval-link', [CommissioningFormController::class, 'approvalLink'])->name('submissions.approval-link');
        Route::get('/submissions/{submission}/pdf', [CommissioningFormController::class, 'pdf'])->name('submissions.pdf');
        Route::get('/attachments/{attachment}', [CommissioningFormController::class, 'attachment'])->name('attachments.show');
        Route::delete('/submissions/{submission}', [CommissioningFormController::class, 'destroy'])->name('submissions.destroy');
        Route::get('/profile', [CommissioningProfileController::class, 'show'])->name('profile');
        Route::patch('/profile', [CommissioningProfileController::class, 'update'])->name('profile.update');
    });

    Route::prefix('pgo')->name('pgo.')->middleware('role:pgo')->group(function () {
        Route::get('/', fn () => redirect()->route('user.pgo.dashboard'));
        Route::get('/dashboard', [PgoDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/tasks', [PgoTaskController::class, 'index'])->name('tasks.index');
        Route::get('/monitoring', [PgoMonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/documents', [PgoDocumentController::class, 'index'])->name('documents.index');
        Route::get('/history', [PgoHistoryController::class, 'index'])->name('history.index');
        Route::get('/profile', [PgoProfileController::class, 'show'])->name('profile');
        Route::patch('/profile', [PgoProfileController::class, 'update'])->name('profile.update');
    });

    Route::prefix('approval')->name('approval.')->middleware('role:approval')->group(function () {
        Route::get('/', fn () => redirect()->route('user.approval.dashboard'));
        Route::get('/dashboard', [ApprovalDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/pending', [ApprovalPendingController::class, 'index'])->name('pending.index');
        Route::get('/review', [ApprovalReviewController::class, 'index'])->name('review.index');
        Route::get('/history', [ApprovalHistoryController::class, 'index'])->name('history.index');
        Route::get('/documents', [ApprovalDocumentController::class, 'index'])->name('documents.index');
        Route::get('/profile', [ApprovalProfileController::class, 'show'])->name('profile');
        Route::patch('/profile', [ApprovalProfileController::class, 'update'])->name('profile.update');
    });

    Route::redirect('/dashboard', '/user/qc/dashboard');
    Route::redirect('/overview', '/user/qc/dashboard');
    Route::redirect('/procurement', '/user/qc/dashboard');
    Route::redirect('/kalender-overhaul', '/user/qc/dashboard');
    Route::redirect('/schedule', '/user/qc/dashboard');
    Route::redirect('/equipment', '/user/qc/history');
    Route::redirect('/mom', '/user/qc/history');
    Route::redirect('/dokument', '/user/qc/history');
    Route::redirect('/dokumen', '/user/qc/history');
});

Route::prefix('inspector')->group(function () {
    Route::redirect('/dashboard', '/user/qc/dashboard');
    Route::redirect('/forms/create', '/user/qc/forms/create');
    Route::redirect('/drafts', '/user/qc/drafts');
    Route::redirect('/history', '/user/qc/history');
    Route::redirect('/documents', '/user/qc/history');
    Route::redirect('/profile', '/user/qc/profile');
    Route::redirect('/commissioning', '/user/qc/forms/create');
    Route::redirect('/qc', '/user/qc/forms/create');
    Route::redirect('/dokumen', '/user/qc/history');
    Route::redirect('/dokument', '/user/qc/history');
    Route::get('/{any}', fn () => redirect('/user/qc/dashboard'))->where('any', '.*');
});
