<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissioningFormTemplate;
use App\Support\Commissioning\FixedCommissioningTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class TemplateFormCommissioningController extends Controller
{
    private const ERROR_STORE = 'COM-TPL-STORE-FAILED';
    private const ERROR_UPDATE = 'COM-TPL-UPDATE-FAILED';
    private const ERROR_PUBLISH = 'COM-TPL-PUBLISH-FAILED';
    private const ERROR_DUPLICATE = 'COM-TPL-DUPLICATE-FAILED';
    private const ERROR_TOGGLE = 'COM-TPL-TOGGLE-FAILED';
    private const ERROR_DESTROY = 'COM-TPL-DESTROY-FAILED';

    public function index(Request $request): View
    {
        $templates = CommissioningFormTemplate::query()
            ->when(
                in_array($request->query('status'), ['draft', 'active', 'inactive'], true),
                fn ($query) => $query->where('status', $request->query('status'))
            )
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.template-form-commissioning.index', [
            'templates' => $templates,
            'summary' => [
                'total' => CommissioningFormTemplate::count(),
                'active' => CommissioningFormTemplate::where('status', 'active')->count(),
                'draft' => CommissioningFormTemplate::where('status', 'draft')->count(),
                'inactive' => CommissioningFormTemplate::where('status', 'inactive')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.template-form-commissioning.create', [
            'template' => new CommissioningFormTemplate([
                'category' => 'Commissioning',
                'version' => '1.0',
                'status' => 'draft',
                'body_schema' => FixedCommissioningTemplate::defaultSchema(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTemplate($request);
        $validated['created_by'] = $request->user()?->id;

        try {
            $template = CommissioningFormTemplate::create($validated);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_STORE, $exception, ['name' => $validated['name'] ?? null]);

            return back()
                ->withInput()
                ->withErrors(['template' => 'Template Form Commissioning gagal dibuat. Kode error: '.self::ERROR_STORE]);
        }

        $this->logStatus('commissioning_template_created', [
            'template_id' => $template->id,
            'status' => $template->status,
        ]);

        return redirect()
            ->route('admin.template-form-commissioning.edit', $template)
            ->with('success', 'Template Form Commissioning berhasil dibuat.');
    }

    public function edit(CommissioningFormTemplate $template): View
    {
        return view('admin.template-form-commissioning.edit', ['template' => $template]);
    }

    public function preview(CommissioningFormTemplate $template): View
    {
        return view('admin.template-form-commissioning.preview', ['template' => $template]);
    }

    public function show(CommissioningFormTemplate $template): View
    {
        return view('admin.template-form-commissioning.show', ['template' => $template]);
    }

    public function update(Request $request, CommissioningFormTemplate $template): RedirectResponse
    {
        $validated = $this->validateTemplate($request, $template);

        try {
            $template->update($validated);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_UPDATE, $exception, ['template_id' => $template->id]);

            return back()
                ->withInput()
                ->withErrors(['template' => 'Template Form Commissioning gagal diperbarui. Kode error: '.self::ERROR_UPDATE]);
        }

        $this->logStatus('commissioning_template_updated', [
            'template_id' => $template->id,
            'status' => $template->status,
        ]);

        return back()->with('success', 'Template Form Commissioning berhasil diperbarui.');
    }

    public function publish(CommissioningFormTemplate $template): RedirectResponse
    {
        try {
            $template->update(['status' => 'active']);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_PUBLISH, $exception, ['template_id' => $template->id]);

            return back()->withErrors(['template' => 'Template Form Commissioning gagal diaktifkan. Kode error: '.self::ERROR_PUBLISH]);
        }

        $this->logStatus('commissioning_template_published', [
            'template_id' => $template->id,
            'status' => $template->status,
        ]);

        return back()->with('success', 'Template Form Commissioning berhasil diaktifkan.');
    }

    public function duplicate(CommissioningFormTemplate $template): RedirectResponse
    {
        try {
            $copy = $template->replicate(['code', 'status']);
            $copy->name = 'Copy - '.$template->name;
            $copy->code = null;
            $copy->status = 'draft';
            $copy->created_by = auth()->id();
            $copy->save();
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_DUPLICATE, $exception, ['template_id' => $template->id]);

            return back()->withErrors(['template' => 'Template Form Commissioning gagal diduplikasi. Kode error: '.self::ERROR_DUPLICATE]);
        }

        $this->logStatus('commissioning_template_duplicated', [
            'source_template_id' => $template->id,
            'template_id' => $copy->id,
            'status' => $copy->status,
        ]);

        return redirect()
            ->route('admin.template-form-commissioning.edit', $copy)
            ->with('success', 'Template Form Commissioning berhasil diduplikasi sebagai draft.');
    }

    public function toggleStatus(CommissioningFormTemplate $template): RedirectResponse
    {
        try {
            $template->update([
                'status' => $template->status === 'active' ? 'inactive' : 'active',
            ]);
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_TOGGLE, $exception, ['template_id' => $template->id]);

            return back()->withErrors(['template' => 'Status template Form Commissioning gagal diperbarui. Kode error: '.self::ERROR_TOGGLE]);
        }

        $this->logStatus('commissioning_template_status_changed', [
            'template_id' => $template->id,
            'status' => $template->status,
        ]);

        return back()->with('success', 'Status template Form Commissioning berhasil diperbarui.');
    }

    public function destroy(CommissioningFormTemplate $template): RedirectResponse
    {
        $templateId = $template->id;

        try {
            $template->delete();
        } catch (Throwable $exception) {
            $this->logError(self::ERROR_DESTROY, $exception, ['template_id' => $templateId]);

            return back()->withErrors(['template' => 'Template Form Commissioning gagal dihapus. Kode error: '.self::ERROR_DESTROY]);
        }

        $this->logStatus('commissioning_template_deleted', [
            'template_id' => $templateId,
            'status' => 'deleted',
        ]);

        return redirect()
            ->route('admin.template-form-commissioning.index')
            ->with('success', 'Template Form Commissioning berhasil dihapus.');
    }

    private function validateTemplate(Request $request, ?CommissioningFormTemplate $template = null): array
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:255', Rule::unique('commissioning_form_templates', 'code')->ignore($template?->id)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:draft,active,inactive'],
            'description' => ['nullable', 'string'],
            'labels' => ['nullable', 'array'],
            'motor_rating_fields' => ['nullable', 'array'],
            'motor_test_fields' => ['nullable', 'array'],
            'motor_test_rows' => ['nullable', 'array'],
            'gearbox_rating_fields' => ['nullable', 'array'],
            'gearbox_test_fields' => ['nullable', 'array'],
            'gearbox_test_rows' => ['nullable', 'array'],
            'equipment_check_rows' => ['nullable', 'array'],
        ]);

        return [
            'code' => $validated['code'] ?? null,
            'name' => $validated['name'],
            'category' => $validated['category'] ?: 'Commissioning',
            'version' => $validated['version'],
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
            'body_schema' => FixedCommissioningTemplate::normalizeSchema([
                'labels' => $validated['labels'] ?? [],
                'motor_rating_fields' => $validated['motor_rating_fields'] ?? [],
                'motor_test_fields' => $validated['motor_test_fields'] ?? [],
                'motor_test_rows' => $validated['motor_test_rows'] ?? [],
                'gearbox_rating_fields' => $validated['gearbox_rating_fields'] ?? [],
                'gearbox_test_fields' => $validated['gearbox_test_fields'] ?? [],
                'gearbox_test_rows' => $validated['gearbox_test_rows'] ?? [],
                'equipment_check_rows' => $validated['equipment_check_rows'] ?? [],
            ]),
        ];
    }

    private function logStatus(string $event, array $context = []): void
    {
        Log::info($event, $context + [
            'actor_id' => auth()->id(),
            'controller' => self::class,
        ]);
    }

    private function logError(string $code, Throwable $exception, array $context = []): void
    {
        Log::error($code, $context + [
            'actor_id' => auth()->id(),
            'controller' => self::class,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
        ]);
    }
}
