<?php

namespace App\Http\Controllers\User\Concerns;

use App\Models\MasterDataRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

trait UpdatesUserProfile
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'profile_areas' => ['nullable', 'array'],
            'profile_areas.*' => ['string', Rule::in($this->profileAreaOptions($request))],
        ], [
            'profile_photo.image' => 'Foto profil harus berupa gambar.',
            'profile_photo.mimes' => 'Foto profil hanya boleh format JPG atau PNG.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 2 MB.',
        ]);

        $user = $request->user();

        $payload = [
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ];

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $payload['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        if (in_array($user->role, ['qc', 'commissioning'], true)) {
            $areas = collect($data['profile_areas'] ?? [])
                ->filter()
                ->unique()
                ->values()
                ->all();

            $payload['profile_areas'] = $areas ?: null;
            $payload['profile_plants'] = $areas
                ? $this->profilePlantsForAreas($request, $areas)
                : null;
        }

        $user->forceFill($payload)->save();

        return back()->with('status', 'Profil berhasil diperbarui.');
    }

    private function profileAreaOptions(Request $request): array
    {
        $category = $this->profileMasterDataCategory($request);

        if (! $category) {
            return [];
        }

        return MasterDataRecord::query()
            ->where('document_category', $category)
            ->where('status', 'active')
            ->whereNotNull('area')
            ->where('area', '<>', '')
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->all();
    }

    private function profilePlantsForAreas(Request $request, array $areas): array
    {
        $category = $this->profileMasterDataCategory($request);

        if (! $category) {
            return [];
        }

        return MasterDataRecord::query()
            ->where('document_category', $category)
            ->where('status', 'active')
            ->whereIn('area', $areas)
            ->whereNotNull('plant')
            ->where('plant', '<>', '')
            ->distinct()
            ->orderBy('plant')
            ->pluck('plant')
            ->all();
    }

    private function profileMasterDataCategory(Request $request): ?string
    {
        return match ($request->user()?->role) {
            'qc' => MasterDataRecord::CATEGORY_QC,
            'commissioning' => MasterDataRecord::CATEGORY_COMMISSIONING,
            default => null,
        };
    }
}
