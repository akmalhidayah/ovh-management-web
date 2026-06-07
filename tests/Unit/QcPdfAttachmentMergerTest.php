<?php

namespace Tests\Unit;

use App\Models\QcFormSubmissionAttachment;
use App\Services\QcPdfAttachmentMerger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class QcPdfAttachmentMergerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_incompatible_attachment_is_converted_with_ghostscript_then_merged(): void
    {
        $attachment = $this->storedAttachment();

        Log::shouldReceive('info')
            ->once()
            ->with('QC-PDF-ATTACHMENT-CONVERTED-WITH-GS', Mockery::on(
                fn (array $context): bool => $context['submission_id'] === 44
                    && $context['attachment_id'] === 12
                    && $context['original_filename'] === 'lampiran.pdf'
            ));
        Log::shouldReceive('warning')->never();

        $service = new class extends QcPdfAttachmentMerger
        {
            private int $mergeAttempts = 0;

            protected function mergePdfWithAttachment(string $currentPdf, string $attachmentPath): string
            {
                $this->mergeAttempts++;

                if ($this->mergeAttempts === 1) {
                    throw new RuntimeException('FPDI parser failed.');
                }

                return $currentPdf.'+converted';
            }

            protected function convertPdfToCompatibleVersionWithGhostscript(string $inputPath): string
            {
                $path = Storage::disk('local')->path('tmp/pdf-compatible/converted.pdf');
                file_put_contents($path, 'compatible-pdf');

                return $path;
            }
        };

        $this->assertSame('main-pdf+converted', $service->merge('main-pdf', [$attachment], 44));
        Storage::disk('local')->assertMissing('tmp/pdf-compatible/converted.pdf');
    }

    public function test_attachment_is_skipped_when_ghostscript_fallback_fails(): void
    {
        $attachment = $this->storedAttachment();

        Log::shouldReceive('info')->never();
        Log::shouldReceive('warning')
            ->once()
            ->with('QC-PDF-ATTACHMENT-MERGE-SKIPPED', Mockery::on(
                fn (array $context): bool => $context['submission_id'] === 45
                    && $context['attachment_id'] === 12
                    && $context['original_path'] === 'qc-submissions/44/lampiran.pdf'
                    && $context['error'] === 'Ghostscript tidak tersedia.'
            ));

        $service = new class extends QcPdfAttachmentMerger
        {
            protected function mergePdfWithAttachment(string $currentPdf, string $attachmentPath): string
            {
                throw new RuntimeException('FPDI parser failed.');
            }

            protected function convertPdfToCompatibleVersionWithGhostscript(string $inputPath): string
            {
                throw new RuntimeException('Ghostscript tidak tersedia.');
            }
        };

        $this->assertSame('main-pdf', $service->merge('main-pdf', [$attachment], 45));
    }

    private function storedAttachment(): QcFormSubmissionAttachment
    {
        $path = 'qc-submissions/44/lampiran.pdf';
        Storage::disk('local')->put($path, 'incompatible-pdf');

        $attachment = new QcFormSubmissionAttachment([
            'qc_form_submission_id' => 44,
            'field_key' => 'dokumen_pendukung',
            'label' => 'Dokumen Pendukung',
            'file_path' => $path,
            'original_name' => 'lampiran.pdf',
            'mime_type' => 'application/pdf',
            'size' => 16,
            'type' => 'document',
        ]);
        $attachment->setAttribute('id', 12);

        return $attachment;
    }
}
