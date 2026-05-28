<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationSectionController extends Controller
{
    public function index(): View
    {
        $sections = OrganizationSection::query()
            ->orderBy('department')
            ->orderBy('unit_kerja')
            ->orderBy('section')
            ->get();

        return view('admin.organization-sections.index', compact('sections'));
    }

    public function store(Request $request): RedirectResponse
    {
        OrganizationSection::create($this->validatedData($request));

        return redirect()
            ->route('admin.organization-sections.index')
            ->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    public function update(Request $request, OrganizationSection $organizationSection): RedirectResponse
    {
        $organizationSection->update($this->validatedData($request, $organizationSection));

        return redirect()
            ->route('admin.organization-sections.index')
            ->with('success', 'Unit kerja berhasil diperbarui.');
    }

    public function destroy(OrganizationSection $organizationSection): RedirectResponse
    {
        $organizationSection->delete();

        return redirect()
            ->route('admin.organization-sections.index')
            ->with('success', 'Unit kerja berhasil dihapus.');
    }

    private function validatedData(Request $request, ?OrganizationSection $section = null): array
    {
        $validated = $request->validate([
            'department' => ['required', 'string', 'max:255'],
            'unit_kerja' => ['required', 'string', 'max:255'],
            'section' => [
                'required',
                'string',
                'max:255',
                Rule::unique('organization_sections', 'section')
                    ->where(fn ($query) => $query
                        ->where('department', $request->input('department'))
                        ->where('unit_kerja', $request->input('unit_kerja')))
                    ->ignore($section?->id),
            ],
        ]);

        $validated['status'] = $section?->status ?? 'active';

        return $validated;
    }
}
