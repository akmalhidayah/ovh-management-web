<?php

namespace App\Http\Controllers;

use App\Http\Controllers\User\Commissioning\FormController as CommissioningFormController;
use App\Http\Controllers\User\Qc\FormController as QcFormController;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\CommissioningFormSubmission;
use App\Models\QcFormSubmission;
use App\Services\ApprovalFlowService;
use App\Support\AreaOwnerLabel;
use App\Support\Commissioning\FixedCommissioningTemplate;
use App\Support\QcTemplates\FixedQcTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicApprovalController extends Controller
{
    private const APPROVAL_SUCCESS_TITLE = 'Approval berhasil';
    private const APPROVAL_SUCCESS_MESSAGE = 'Approval berhasil disimpan. Dokumen sudah diteruskan ke tahap approval berikutnya jika masih ada.';
    private const FINAL_OVERHAUL_APPROVAL_TITLE = 'Approval final berhasil';
    private const FINAL_OVERHAUL_APPROVAL_MESSAGE = 'Jangan Lupa Pak dih Mwehehehhehe:V.';
    private const FINAL_OVERHAUL_APPROVAL_CONFIRM_BUTTON = 'Selesai';

    public function __construct(private readonly ApprovalFlowService $approvalFlowService)
    {
    }

    public function show(string $token): View|Response
    {
        $step = $this->approvalFlowService->activeStepForToken($token);
        if (! $step) {
            $linkStatus = $this->approvalFlowService->linkStatusForToken($token);
            if ($linkStatus !== null) {
                return $this->invalidApprovalLinkResponse($linkStatus);
            }

            abort(404);
        }

        $step->load('flow.approvable');
        $submission = $step->flow->approvable;
        $submission?->loadMissing('attachments');

        return view('public.approval.show', [
            'token' => $token,
            'step' => $step,
            'submission' => $submission,
            'document' => $this->documentSummary($step),
            'attachmentPreview' => $this->attachmentPreview($submission),
            'suggestedApproverName' => $this->suggestedApproverName($step),
            'suggestedApproverPosition' => $this->suggestedApproverPosition($step),
        ]);
    }

    public function pdf(string $token)
    {
        $step = $this->approvalFlowService->activeStepForToken($token);
        abort_unless($step, 404);

        return $this->streamSubmissionPdf($step->flow->approvable, $token);
    }

    public function signedPdf(ApprovalStep $step)
    {
        abort_unless($step->status === ApprovalStep::STATUS_APPROVED, 404);

        $step->loadMissing('flow.approvable');

        return $this->streamSubmissionPdf($step->flow->approvable);
    }

    private function streamSubmissionPdf(mixed $submission, ?string $token = null)
    {
        try {
            if ($submission instanceof QcFormSubmission) {
                return QcFormController::streamPdf($submission);
            }

            if ($submission instanceof CommissioningFormSubmission) {
                return CommissioningFormController::streamPdf($submission);
            }
        } catch (\Throwable $exception) {
            Log::error('PUBLIC-APPROVAL-PDF-FAILED', [
                'token_hash' => $token ? hash('sha256', $token) : null,
                'submission_type' => $submission ? $submission::class : null,
                'submission_id' => $submission?->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return response('PDF approval gagal dibuka.', 500);
        }

        abort(404);
    }

    public function approve(Request $request, string $token): View|Response|RedirectResponse
    {
        try {
            $step = $this->approvalFlowService->approveStep($token, $request->all(), $request);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('PUBLIC-APPROVAL-APPROVE-FAILED', [
                'token_hash' => hash('sha256', $token),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return response()->view('public.approval.done', [
                'title' => 'Approval gagal',
                'message' => 'Approval gagal diproses. Silakan coba lagi.',
                'icon' => 'error',
            ], 500);
        }

        $signedPdfUrl = URL::temporarySignedRoute(
            'public.approval.signed-pdf',
            now()->addMinutes(10),
            ['step' => $step],
        );

        return response()->view('public.approval.done', $this->approvalSuccessViewData($step, $signedPdfUrl));
    }

    public function reject(Request $request, string $token): View|Response
    {
        try {
            $this->approvalFlowService->rejectStep($token, (string) $request->input('reject_reason'), $request);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('PUBLIC-APPROVAL-REJECT-FAILED', [
                'token_hash' => hash('sha256', $token),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return response()->view('public.approval.done', [
                'title' => 'Reject gagal',
                'message' => 'Reject gagal diproses. Silakan coba lagi.',
                'icon' => 'error',
            ], 500);
        }

        return view('public.approval.done', [
            'title' => 'Reject berhasil',
            'message' => 'Reject berhasil disimpan. Link ini sudah tidak dapat dipakai lagi.',
            'icon' => 'success',
        ]);
    }

    private function documentSummary(ApprovalStep $step): array
    {
        $submission = $step->flow->approvable;

        if ($submission instanceof QcFormSubmission) {
            $generalInfo = $submission->general_info ?? [];

            return [
                'type' => 'QC',
                'number' => $submission->form_number,
                'template' => $submission->template_name ?: $submission->template?->name,
                'equipment' => $submission->equipment ?: ($generalInfo['name_equipment'] ?? '-'),
                'work_description' => $submission->pekerjaan ?: ($generalInfo['pekerjaan'] ?? ($submission->template_name ?: $submission->template?->name ?: '-')),
                'section_no' => $submission->tag_num ?: ($generalInfo['tag_num'] ?? '-'),
                'functional_location' => $generalInfo['functional_location'] ?? '-',
                'plant' => $submission->plant ?: ($generalInfo['plant'] ?? '-'),
                'area' => $submission->area ?: ($generalInfo['area'] ?? '-'),
                'status' => $submission->status,
            ];
        }

        if ($submission instanceof CommissioningFormSubmission) {
            $header = $submission->header_data ?? [];

            return [
                'type' => 'Commissioning',
                'number' => $submission->form_number,
                'template' => $submission->template_name ?: $submission->template?->name,
                'equipment' => $submission->equipment ?: ($header['name_equipment'] ?? '-'),
                'work_description' => $submission->template_name ?: $submission->template?->name ?: '-',
                'section_no' => $submission->tag_num ?: ($header['tag_num'] ?? '-'),
                'functional_location' => $submission->functional_location ?: ($header['functional_location'] ?? '-'),
                'plant' => $header['plant'] ?? '-',
                'area' => $submission->area ?: ($header['area'] ?? '-'),
                'status' => $submission->status,
            ];
        }

        return [
            'type' => 'Dokumen',
            'number' => '-',
            'template' => '-',
            'equipment' => '-',
            'work_description' => '-',
            'section_no' => '-',
            'functional_location' => '-',
            'plant' => '-',
            'area' => '-',
            'status' => '-',
        ];
    }

    private function attachmentPreview(mixed $submission): array
    {
        if ($submission instanceof QcFormSubmission) {
            $attachments = $submission->attachments->groupBy('field_key');

            return [
                'type' => 'qc',
                'before' => $this->imageAttachmentItems($attachments->get('foto_before', collect())),
                'after' => $this->imageAttachmentItems($attachments->get('foto_after', collect())),
            ];
        }

        if ($submission instanceof CommissioningFormSubmission) {
            return [
                'type' => 'commissioning',
                'items' => $this->imageAttachmentItems($submission->attachments),
            ];
        }

        return ['type' => null];
    }

    private function imageAttachmentItems(iterable $attachments, int $limit = 6): \Illuminate\Support\Collection
    {
        return collect($attachments)
            ->filter(fn ($attachment) => ($attachment->type ?? null) === 'image')
            ->take($limit)
            ->map(function ($attachment) {
                $source = $this->attachmentDataUri($attachment);

                if (! $source) {
                    return null;
                }

                return [
                    'name' => $attachment->original_name ?: ($attachment->label ?: 'Lampiran'),
                    'label' => $attachment->label ?: 'Lampiran',
                    'source' => $source,
                ];
            })
            ->filter()
            ->values();
    }

    private function attachmentDataUri(mixed $attachment): ?string
    {
        $path = $attachment->file_path ?? null;

        if (! $path || ! str_starts_with((string) ($attachment->mime_type ?? ''), 'image/')) {
            return null;
        }

        $absolutePath = null;

        if (Storage::disk('local')->exists($path)) {
            $absolutePath = Storage::disk('local')->path($path);
        } elseif (Storage::disk('public')->exists($path)) {
            $absolutePath = Storage::disk('public')->path($path);
        } else {
            $candidate = storage_path('app/public/'.$path);
            $absolutePath = file_exists($candidate) ? $candidate : null;
        }

        if (! $absolutePath || ! file_exists($absolutePath)) {
            return null;
        }

        return 'data:'.$attachment->mime_type.';base64,'.base64_encode((string) file_get_contents($absolutePath));
    }

    private function suggestedApproverName(ApprovalStep $step): string
    {
        $submission = $step->flow->approvable;

        if ($submission instanceof CommissioningFormSubmission) {
            $column = array_values(FixedCommissioningTemplate::approvalColumns())[$step->step_order - 1] ?? null;
            if (! $column) {
                return '';
            }

            $approvalDataName = trim((string) data_get($submission->approval_data ?? [], $column['key'].'.name', ''));
            if ($approvalDataName !== '') {
                return $approvalDataName;
            }

            $templateSnapshot = $submission->template_snapshot ?? [];
            $schema = FixedCommissioningTemplate::normalizeSchema(
                $templateSnapshot['body_schema'] ?? $submission->template?->body_schema ?? [],
            );

            return trim((string) data_get($schema, 'approval_defaults.'.$column['key'].'.name', ''));
        }

        if ($submission instanceof QcFormSubmission) {
            $templateSnapshot = $submission->template_snapshot ?? [];
            $templateType = $templateSnapshot['template_type'] ?? $submission->template?->template_type;
            $column = array_values(FixedQcTemplate::approvalColumns($templateType))[$step->step_order - 1] ?? null;
            if (! $column) {
                return '';
            }

            $approvalDataName = trim((string) data_get($submission->approval_data ?? [], $column['key'].'.name', ''));
            if ($approvalDataName !== '') {
                return $approvalDataName;
            }

            $schema = FixedQcTemplate::normalizeSchema(
                $templateType,
                $templateSnapshot['body_schema'] ?? $submission->template?->body_schema ?? [],
            );

            return trim((string) data_get($schema, 'approval_defaults.'.$column['key'].'.name', ''));
        }

        return '';
    }

    private function approvalSuccessViewData(ApprovalStep $step, string $signedPdfUrl): array
    {
        $isFinalOverhaulManagement = $this->isFinalOverhaulManagementApproval($step);

        return [
            'title' => $isFinalOverhaulManagement
                ? self::FINAL_OVERHAUL_APPROVAL_TITLE
                : self::APPROVAL_SUCCESS_TITLE,
            'message' => $isFinalOverhaulManagement
                ? self::FINAL_OVERHAUL_APPROVAL_MESSAGE
                : self::APPROVAL_SUCCESS_MESSAGE,
            'icon' => 'success',
            'confirmButtonText' => $isFinalOverhaulManagement
                ? self::FINAL_OVERHAUL_APPROVAL_CONFIRM_BUTTON
                : 'Mengerti',
            'signedPdfUrl' => $signedPdfUrl,
            'signedPdfLabel' => 'Lihat PDF',
        ];
    }

    private function isFinalOverhaulManagementApproval(ApprovalStep $step): bool
    {
        $step->loadMissing('flow.steps');

        if ($step->flow?->status !== ApprovalFlow::STATUS_APPROVED) {
            return false;
        }

        $normalizedLabel = str($step->label)->lower()->replace([' ', '/', '_', '-'], '')->toString();

        return str_contains($normalizedLabel, 'overhaulmanagement');
    }

    private function suggestedApproverPosition(ApprovalStep $step): string
    {
        $submission = $step->flow->approvable;

        if ($submission instanceof QcFormSubmission) {
            $templateSnapshot = $submission->template_snapshot ?? [];
            $templateType = FixedQcTemplate::normalizeType($templateSnapshot['template_type'] ?? $submission->template?->template_type);
            $column = array_values(FixedQcTemplate::approvalColumns($templateType))[$step->step_order - 1] ?? null;

            if (! $column) {
                return '';
            }

            $key = $column['key'];

            if (FixedQcTemplate::approvalGroupIsEditable($templateType, $key)) {
                return FixedQcTemplate::approvalEditableValue(
                    $templateType,
                    $key,
                    data_get($submission->approval_data ?? [], $key.'.group', '')
                );
            }

            if (FixedQcTemplate::approvalLabelIsEditable($templateType, $key)) {
                return FixedQcTemplate::approvalEditableValue(
                    $templateType,
                    $key,
                    data_get($submission->approval_data ?? [], $key.'.label', '')
                );
            }

            if (
                in_array($templateType, [FixedQcTemplate::TYPE_GENERAL, FixedQcTemplate::TYPE_WELDING], true)
                && ($column['role'] ?? null) === 'Unit Kerja'
            ) {
                return AreaOwnerLabel::approvalLabel(
                    data_get($submission->approval_data ?? [], $key.'.label', $step->label),
                    data_get($submission->general_info ?? [], 'unit_kerja', $step->label)
                );
            }
        }

        if ($submission instanceof CommissioningFormSubmission && $step->step_order === 3) {
            return AreaOwnerLabel::approvalLabel(
                data_get($submission->approval_data ?? [], 'unit_kerja.label', $step->label),
                data_get($submission->header_data ?? [], 'unit_kerja', $step->label)
            );
        }

        return $step->label;
    }

    private function invalidApprovalLinkResponse(string $status): Response
    {
        [$title, $message] = match ($status) {
            'used' => [
                'Link approval sudah digunakan',
                'Tanda tangan untuk link ini sudah tersimpan. Link yang sama tidak bisa dipakai dua kali.',
            ],
            'expired' => [
                'Link approval sudah kedaluwarsa',
                'Masa berlaku link ini sudah habis. Minta link approval baru dari halaman dokumen.',
            ],
            'revoked' => [
                'Link approval sudah dicabut',
                'Link ini sudah diganti atau dibatalkan sehingga tidak dapat digunakan lagi.',
            ],
            default => [
                'Link approval sudah tidak aktif',
                'Tahap approval ini sudah berubah sehingga link tidak dapat digunakan lagi.',
            ],
        };

        return response()->view('errors.approval-link', [
            'title' => $title,
            'message' => $message,
        ], 404);
    }
}
