<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\QcSubmissionController as AdminQcSubmissionController;
use App\Http\Controllers\Admin\TemplateFormCommissioningController;
use App\Http\Controllers\Admin\TemplateFormQcController;
use App\Http\Controllers\Auth\LoginController;
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
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'usertype:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/overview', [AdminDashboardController::class, 'overview'])->name('overview');
    Route::get('/procurement', [AdminDashboardController::class, 'procurement'])->name('procurement');
    Route::get('/kalender-overhaul', [AdminDashboardController::class, 'kalenderOverhaul'])->name('kalender-overhaul');
    Route::get('/schedule', [AdminDashboardController::class, 'schedule'])->name('schedule');
    Route::get('/commissioning', [AdminDashboardController::class, 'commissioning'])->name('commissioning');
    Route::get('/qc', [AdminDashboardController::class, 'qc'])->name('qc');
    Route::prefix('qc/submissions')->name('qc.submissions.')->group(function () {
        Route::get('/', [AdminQcSubmissionController::class, 'index'])->name('index');
        Route::get('/{submission}/pdf', [AdminQcSubmissionController::class, 'pdf'])->name('pdf');
    });
    Route::prefix('template-form-qc')->name('template-form-qc.')->group(function () {
        Route::get('/', [TemplateFormQcController::class, 'index'])->name('index');
        Route::get('/create', [TemplateFormQcController::class, 'create'])->name('create');
        Route::post('/', [TemplateFormQcController::class, 'store'])->name('store');
        Route::get('/import', [TemplateFormQcController::class, 'import'])->name('import');
        Route::post('/import', [TemplateFormQcController::class, 'processImport'])->name('import.process');
        Route::patch('/{template}/publish', [TemplateFormQcController::class, 'publish'])->name('publish');
        Route::get('/{template}', [TemplateFormQcController::class, 'show'])->name('show');
        Route::get('/{template}/edit', [TemplateFormQcController::class, 'edit'])->name('edit');
        Route::put('/{template}', [TemplateFormQcController::class, 'update'])->name('update');
        Route::delete('/{template}', [TemplateFormQcController::class, 'destroy'])->name('destroy');
        Route::get('/{template}/preview', [TemplateFormQcController::class, 'preview'])->name('preview');
        Route::post('/{template}/duplicate', [TemplateFormQcController::class, 'duplicate'])->name('duplicate');
        Route::patch('/{template}/toggle-status', [TemplateFormQcController::class, 'toggleStatus'])->name('toggle-status');
    });
    Route::get('/template-form-commissioning', [TemplateFormCommissioningController::class, 'index'])->name('template-form-commissioning.index');
    Route::get('/equipment', [AdminDashboardController::class, 'equipment'])->name('equipment');
    Route::get('/mom', [AdminDashboardController::class, 'mom'])->name('mom');
    Route::redirect('/dokument', '/admin/dokumen');
    Route::get('/dokumen', [AdminDashboardController::class, 'dokumen'])->name('dokumen');
    Route::get('/master-data', [AdminDashboardController::class, 'masterData'])->name('master-data');
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
        Route::get('/submissions/{submission}', [QcFormController::class, 'show'])->name('submissions.show');
        Route::get('/submissions/{submission}/pdf', [QcFormController::class, 'pdf'])->name('submissions.pdf');
        Route::delete('/submissions/{submission}', [QcFormController::class, 'destroy'])->name('submissions.destroy');
        Route::get('/profile', [QcProfileController::class, 'show'])->name('profile');
        Route::patch('/profile', [QcProfileController::class, 'update'])->name('profile.update');
    });

    Route::prefix('commissioning')->name('commissioning.')->middleware('role:commissioning')->group(function () {
        Route::get('/', fn () => redirect()->route('user.commissioning.dashboard'));
        Route::get('/dashboard', [CommissioningDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/forms/create', [CommissioningFormController::class, 'create'])->name('forms.create');
        Route::get('/drafts', [CommissioningDraftController::class, 'index'])->name('drafts.index');
        Route::get('/history', [CommissioningHistoryController::class, 'index'])->name('history.index');
        Route::redirect('/documents', '/user/commissioning/history')->name('documents.index');
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
