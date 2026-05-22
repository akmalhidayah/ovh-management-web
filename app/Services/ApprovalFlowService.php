<?php

namespace App\Services;

use App\Models\ApprovalFlow;
use App\Models\ApprovalLink;
use App\Models\ApprovalStep;
use App\Models\CommissioningFormSubmission;
use App\Models\QcFormSubmission;
use App\Models\TemplateApprovalStep;
use App\Support\Commissioning\FixedCommissioningTemplate;
use App\Support\QcTemplates\FixedQcTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApprovalFlowService
{
    private const LINK_TTL_DAYS = 3;
    private const SIGNATURE_MAX_BYTES = 1048576;

    public function startForSubmission(Model $submission, string $type): ApprovalFlow
    {
        return DB::transaction(function () use ($submission, $type) {
            $this->cancelFlow($submission, 'Starting new approval flow');

            $flow = $submission->approvalFlow()->create([
                'status' => ApprovalFlow::STATUS_PENDING,
                'current_step_order' => null,
            ]);

            foreach ($this->buildTemplateSteps($submission, $type) as $definition) {
                $step = $flow->steps()->create([
                    'step_order' => $definition['step_order'],
                    'label' => $definition['label'],
                    'is_submitter_signature' => (bool) ($definition['is_submitter_signature'] ?? false),
                    'requires_magic_link' => (bool) ($definition['requires_magic_link'] ?? true),
                    'status' => ApprovalStep::STATUS_PENDING,
                ]);

                if ($step->is_submitter_signature) {
                    $this->approveSubmitterStep($step, $submission);
                }
            }

            $this->activateNextStep($flow);
            $this->event($flow, null, 'flow_started', 'Approval flow started.');

            return $flow->fresh(['steps.links']);
        });
    }

    public function buildTemplateSteps(Model $submission, string $type): array
    {
        $template = $submission->template ?? null;
        $templateId = $template?->id;

        if ($templateId) {
            $configured = TemplateApprovalStep::query()
                ->where('template_type', $type)
                ->where('template_id', $templateId)
                ->orderBy('step_order')
                ->get();

            if ($configured->isNotEmpty()) {
                return $configured->map(fn (TemplateApprovalStep $step) => [
                    'step_order' => (int) $step->step_order,
                    'label' => $step->label,
                    'is_submitter_signature' => $step->is_submitter_signature,
                    'requires_magic_link' => $step->requires_magic_link,
                    'is_required' => $step->is_required,
                ])->all();
            }
        }

        return $type === 'commissioning'
            ? $this->defaultCommissioningSteps()
            : $this->defaultQcSteps($submission);
    }

    public function generateLinkForStep(ApprovalStep $step): string
    {
        $token = Str::random(64);

        $step->links()->create([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(self::LINK_TTL_DAYS),
        ]);

        $this->event($step->flow, $step, 'link_generated', "Approval link generated for {$step->label}.");

        return route('public.approval.show', ['token' => $token]);
    }

    public function getActiveLinkForSubmission(Model $submission): ?string
    {
        $flow = $submission->approvalFlow()->with('steps.links')->first();
        if (! $flow || $flow->status !== ApprovalFlow::STATUS_PENDING) {
            return null;
        }

        $step = $flow->steps
            ->first(fn (ApprovalStep $step) => $step->status === ApprovalStep::STATUS_ACTIVE && $step->requires_magic_link);

        if (! $step) {
            return null;
        }

        $step->links()
            ->whereNull('used_at')
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        return $this->generateLinkForStep($step);
    }

    public function activeStepForToken(string $token): ?ApprovalStep
    {
        $link = $this->validLinkForToken($token, false);

        return $link?->step;
    }

    public function linkStatusForToken(string $token): ?string
    {
        $link = ApprovalLink::query()
            ->with('step')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $link) {
            return null;
        }

        if ($link->used_at !== null) {
            return 'used';
        }

        if ($link->revoked_at !== null) {
            return 'revoked';
        }

        if ($link->expires_at?->isPast()) {
            return 'expired';
        }

        if ($link->step?->status !== ApprovalStep::STATUS_ACTIVE) {
            return 'inactive';
        }

        return 'active';
    }

    public function approveStep(string $token, array $data, Request $request): ApprovalStep
    {
        return DB::transaction(function () use ($token, $data, $request) {
            $link = $this->validLinkForToken($token);
            if (! $link) {
                throw ValidationException::withMessages(['token' => 'Link approval tidak valid, sudah dipakai, dicabut, atau expired.']);
            }

            $step = $link->step()->with('flow.approvable')->lockForUpdate()->firstOrFail();
            if ($step->status !== ApprovalStep::STATUS_ACTIVE) {
                throw ValidationException::withMessages(['token' => 'Step approval ini sudah tidak aktif.']);
            }

            $name = trim((string) ($data['approver_name'] ?? ''));
            $position = trim((string) ($data['approver_position'] ?? ''));
            $signature = trim((string) ($data['signature'] ?? ''));
            $signatureFile = $request->file('signature_file') ?: $request->file('signature');
            if (! $signatureFile instanceof UploadedFile) {
                $signatureFile = null;
            }

            $errors = [];
            if ($name === '') {
                $errors['approver_name'] = 'Nama approver wajib diisi.';
            }
            if ($position === '') {
                $errors['approver_position'] = 'Jabatan approver wajib diisi.';
            }
            if (! $signatureFile && $signature === '') {
                $errors['signature'] = 'Tanda tangan wajib diisi.';
            } elseif ($signatureFile && ! $this->isValidSignatureFile($signatureFile)) {
                $errors['signature'] = 'Tanda tangan harus berupa file PNG/JPEG maksimal 1MB.';
            } elseif (! $signatureFile && ! $this->isValidSignatureData($signature)) {
                $errors['signature'] = 'Tanda tangan harus berupa gambar PNG/JPEG maksimal 1MB.';
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            $step->update([
                'status' => ApprovalStep::STATUS_APPROVED,
                'approver_name' => $name,
                'approver_position' => $position,
                'signature_path' => $signatureFile
                    ? $this->storeSignatureFile($signatureFile, $step)
                    : $this->storeSignature($signature, $step),
                'signature_data' => null,
                'acted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);

            $link->update(['used_at' => now()]);
            $this->event($step->flow, $step, 'step_approved', "{$step->label} approved.", $request);

            $flow = $step->flow()->with('steps')->firstOrFail();
            $this->activateNextStep($flow);

            return $step->fresh(['flow.steps.links']);
        });
    }

    public function rejectStep(string $token, string $reason, Request $request): ApprovalStep
    {
        return DB::transaction(function () use ($token, $reason, $request) {
            $link = $this->validLinkForToken($token);
            if (! $link) {
                throw ValidationException::withMessages(['token' => 'Link approval tidak valid, sudah dipakai, dicabut, atau expired.']);
            }

            $reason = trim($reason);
            if ($reason === '') {
                throw ValidationException::withMessages(['reject_reason' => 'Alasan reject wajib diisi.']);
            }

            $step = $link->step()->with('flow.approvable')->lockForUpdate()->firstOrFail();
            if ($step->status !== ApprovalStep::STATUS_ACTIVE) {
                throw ValidationException::withMessages(['token' => 'Step approval ini sudah tidak aktif.']);
            }

            $step->update([
                'status' => ApprovalStep::STATUS_REJECTED,
                'reject_reason' => $reason,
                'acted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);

            $link->update(['used_at' => now()]);

            $flow = $step->flow;
            $flow->steps()
                ->where('status', ApprovalStep::STATUS_PENDING)
                ->update(['status' => ApprovalStep::STATUS_CANCELLED]);
            $flow->update([
                'status' => ApprovalFlow::STATUS_REVISION_REQUIRED,
                'current_step_order' => $step->step_order,
            ]);
            $this->setSubmissionStatus($flow->approvable, 'revision_required');
            $this->event($flow, $step, 'step_rejected', $reason, $request);

            return $step->fresh(['flow.steps.links']);
        });
    }

    public function cancelFlow(Model $submission, string $reason): void
    {
        $flows = $submission->approvalFlow()
            ->whereIn('status', [ApprovalFlow::STATUS_PENDING, ApprovalFlow::STATUS_REVISION_REQUIRED])
            ->with('steps.links')
            ->get();

        foreach ($flows as $flow) {
            $flow->steps()
                ->whereIn('status', [ApprovalStep::STATUS_PENDING, ApprovalStep::STATUS_ACTIVE])
                ->update(['status' => ApprovalStep::STATUS_CANCELLED]);

            ApprovalLink::query()
                ->whereIn('approval_step_id', $flow->steps->pluck('id'))
                ->whereNull('used_at')
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            $flow->update(['status' => ApprovalFlow::STATUS_CANCELLED]);
            $this->event($flow, null, 'flow_cancelled', $reason);
        }
    }

    private function approveSubmitterStep(ApprovalStep $step, Model $submission): void
    {
        $approvalData = $submission->approval_data ?? [];
        $legacy = $this->legacyApprovalForSubmitter($step, $approvalData);
        $legacySignature = $legacy['signature'] ?? null;
        $signaturePath = $this->publicStoragePathFromUrl($legacySignature);

        $step->update([
            'status' => ApprovalStep::STATUS_APPROVED,
            'approver_name' => $legacy['name'] ?? $submission->user?->name ?? 'Submitter',
            'approver_position' => $legacy['role'] ?? $step->label,
            'signature_path' => $signaturePath,
            'signature_data' => $signaturePath ? null : $legacySignature,
            'acted_at' => isset($legacy['signed_at']) ? Carbon::parse($legacy['signed_at']) : now(),
        ]);
    }

    private function activateNextStep(ApprovalFlow $flow): void
    {
        $flow->load('steps');

        $next = $flow->steps
            ->where('status', ApprovalStep::STATUS_PENDING)
            ->sortBy('step_order')
            ->first();

        if (! $next) {
            $flow->update([
                'status' => ApprovalFlow::STATUS_APPROVED,
                'current_step_order' => null,
            ]);
            $this->setSubmissionStatus($flow->approvable, 'approved');
            $this->event($flow, null, 'flow_approved', 'All approval steps approved.');

            return;
        }

        $next->update(['status' => ApprovalStep::STATUS_ACTIVE]);
        $flow->update([
            'status' => ApprovalFlow::STATUS_PENDING,
            'current_step_order' => $next->step_order,
        ]);

        if ($next->requires_magic_link) {
            $this->generateLinkForStep($next);
        }
    }

    private function defaultQcSteps(Model $submission): array
    {
        $templateType = FixedQcTemplate::normalizeType($submission->template?->template_type);
        $columns = FixedQcTemplate::approvalColumns($templateType);

        return collect($columns)
            ->values()
            ->map(function (array $column, int $index) use ($templateType) {
                $isSubmitter = $index === 0;

                return [
                    'step_order' => $index + 1,
                    'label' => $column['label'],
                    'is_submitter_signature' => $isSubmitter,
                    'requires_magic_link' => ! $isSubmitter,
                    'is_required' => true,
                ];
            })
            ->all();
    }

    private function defaultCommissioningSteps(): array
    {
        return collect(FixedCommissioningTemplate::approvalColumns())
            ->values()
            ->map(fn (array $column, int $index) => [
                'step_order' => $index + 1,
                'label' => $column['label'],
                'is_submitter_signature' => false,
                'requires_magic_link' => true,
                'is_required' => true,
            ])
            ->all();
    }

    private function validLinkForToken(string $token, bool $lock = true): ?ApprovalLink
    {
        $query = ApprovalLink::query()
            ->with('step.flow.approvable')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());

        if ($lock) {
            $query->lockForUpdate();
        }

        $link = $query->first();
        if (! $link) {
            return null;
        }

        if ($link->step?->status !== ApprovalStep::STATUS_ACTIVE) {
            return null;
        }

        return $link;
    }

    private function setSubmissionStatus(?Model $submission, string $status): void
    {
        if (! $submission) {
            return;
        }

        $submission->forceFill(['status' => $status])->save();
    }

    private function legacyApprovalForSubmitter(ApprovalStep $step, array $approvalData): array
    {
        foreach ($approvalData as $approval) {
            if (! is_array($approval)) {
                continue;
            }

            $role = $approval['role'] ?? '';
            if (($approval['signature'] ?? null) && (str_contains((string) $role, 'QC') || str_contains((string) $role, 'diisi'))) {
                return $approval;
            }
        }

        return [];
    }

    private function publicStoragePathFromUrl(mixed $source): ?string
    {
        $source = trim((string) $source);

        if ($source === '' || str_starts_with($source, 'data:image/')) {
            return null;
        }

        $path = parse_url($source, PHP_URL_PATH) ?: $source;

        if (! str_starts_with($path, '/storage/')) {
            return null;
        }

        $relative = urldecode(substr($path, strlen('/storage/')));

        return Storage::disk('public')->exists($relative) ? $relative : null;
    }

    private function storeSignature(string $source, ApprovalStep $step): string
    {
        preg_match('/^data:image\/(png|jpeg|jpg);base64,(.+)$/', $source, $matches);
        $extension = $matches[1] === 'png' ? 'png' : 'jpg';
        $binary = base64_decode($matches[2], true);
        $path = 'signatures/approval/approval-step-'.$step->id.'-'.Str::random(16).'.'.$extension;

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function storeSignatureFile(UploadedFile $file, ApprovalStep $step): string
    {
        $extension = $file->getMimeType() === 'image/png' ? 'png' : 'jpg';
        $path = 'signatures/approval/approval-step-'.$step->id.'-'.Str::random(16).'.'.$extension;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    private function isValidSignatureData(string $source): bool
    {
        if (! preg_match('/^data:image\/(png|jpeg|jpg);base64,(.+)$/', $source, $matches)) {
            return false;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) > self::SIGNATURE_MAX_BYTES) {
            return false;
        }

        return @getimagesizefromstring($binary) !== false;
    }

    private function isValidSignatureFile(UploadedFile $file): bool
    {
        if (! $file->isValid() || $file->getSize() > self::SIGNATURE_MAX_BYTES) {
            return false;
        }

        if (! in_array($file->getMimeType(), ['image/png', 'image/jpeg'], true)) {
            return false;
        }

        return @getimagesize($file->getRealPath()) !== false;
    }

    private function event(ApprovalFlow $flow, ?ApprovalStep $step, string $event, ?string $description = null, ?Request $request = null): void
    {
        try {
            $flow->events()->create([
                'approval_step_id' => $step?->id,
                'event' => $event,
                'description' => $description,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? (string) $request->userAgent() : null,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('approval_event_failed', [
                'approval_flow_id' => $flow->id,
                'approval_step_id' => $step?->id,
                'event' => $event,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
