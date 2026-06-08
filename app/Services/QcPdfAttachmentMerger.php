<?php

namespace App\Services;

use App\Models\QcFormSubmissionAttachment;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class QcPdfAttachmentMerger
{
    private const TEMP_DIRECTORY = 'tmp/pdf-compatible';

    /**
     * @param  iterable<QcFormSubmissionAttachment>  $attachments
     */
    public function merge(string $mainPdf, iterable $attachments, int|string|null $submissionId): string
    {
        Storage::disk('local')->makeDirectory(self::TEMP_DIRECTORY);

        $mergedPdf = $mainPdf;
        $temporaryFiles = [];

        try {
            foreach ($attachments as $attachment) {
                $mergedPdf = $this->safeMergePdfAttachment(
                    $mergedPdf,
                    $attachment,
                    $submissionId,
                    $temporaryFiles,
                );
            }

            return $mergedPdf;
        } finally {
            foreach (array_unique($temporaryFiles) as $temporaryFile) {
                if (is_string($temporaryFile) && is_file($temporaryFile)) {
                    @unlink($temporaryFile);
                }
            }
        }
    }

    protected function resolveGhostscriptBinary(): ?string
    {
        foreach (['/usr/bin/gs', '/bin/gs'] as $binary) {
            if (is_file($binary) && is_executable($binary)) {
                return $binary;
            }
        }

        return null;
    }

    protected function convertPdfToCompatibleVersionWithGhostscript(string $inputPath): string
    {
        $binary = $this->resolveGhostscriptBinary();

        if ($binary === null) {
            throw new RuntimeException('Ghostscript binary tidak tersedia di /usr/bin/gs atau /bin/gs.');
        }

        $outputPath = $this->temporaryPdfPath('qc-gs-compatible');
        $process = new Process([
            $binary,
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/prepress',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-sOutputFile='.$outputPath,
            $inputPath,
        ]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($outputPath) || filesize($outputPath) === 0) {
            @unlink($outputPath);

            throw new RuntimeException(
                trim($process->getErrorOutput()) ?: 'Ghostscript gagal membuat PDF kompatibel.'
            );
        }

        return $outputPath;
    }

    /**
     * @param  array<int, string>  $temporaryFiles
     */
    protected function safeMergePdfAttachment(
        string $currentPdf,
        QcFormSubmissionAttachment $attachment,
        int|string|null $submissionId,
        array &$temporaryFiles,
    ): string {
        $attachmentPath = $this->storedAttachmentPath($attachment);

        if ($attachmentPath === null) {
            $this->logSkippedAttachment($attachment, $submissionId, 'File lampiran tidak ditemukan.');

            return $currentPdf;
        }

        try {
            return $this->mergePdfWithAttachment($currentPdf, $attachmentPath);
        } catch (Throwable $originalException) {
            try {
                $convertedPath = $this->convertPdfToCompatibleVersionWithGhostscript($attachmentPath);
                $temporaryFiles[] = $convertedPath;

                Log::info('QC-PDF-ATTACHMENT-CONVERTED-WITH-GS', $this->attachmentLogContext(
                    $attachment,
                    $submissionId,
                    $originalException->getMessage(),
                ));

                return $this->mergePdfWithAttachment($currentPdf, $convertedPath);
            } catch (Throwable $fallbackException) {
                $this->logSkippedAttachment($attachment, $submissionId, $fallbackException->getMessage());

                return $currentPdf;
            }
        }
    }

    protected function mergePdfWithAttachment(string $currentPdf, string $attachmentPath): string
    {
        $merger = new Merger();
        $merger->addRaw($currentPdf);
        $merger->addFile($attachmentPath);

        return $merger->merge();
    }

    private function storedAttachmentPath(QcFormSubmissionAttachment $attachment): ?string
    {
        if (Storage::disk('local')->exists($attachment->file_path)) {
            return Storage::disk('local')->path($attachment->file_path);
        }

        if (Storage::disk('public')->exists($attachment->file_path)) {
            return Storage::disk('public')->path($attachment->file_path);
        }

        return null;
    }

    private function temporaryPdfPath(string $prefix): string
    {
        return Storage::disk('local')->path(
            self::TEMP_DIRECTORY.'/'.$prefix.'-'.Str::uuid().'.pdf'
        );
    }

    private function logSkippedAttachment(
        QcFormSubmissionAttachment $attachment,
        int|string|null $submissionId,
        string $error,
    ): void {
        Log::warning('QC-PDF-ATTACHMENT-MERGE-SKIPPED', $this->attachmentLogContext(
            $attachment,
            $submissionId,
            $error,
        ));
    }

    /**
     * @return array<string, int|string|null>
     */
    private function attachmentLogContext(
        QcFormSubmissionAttachment $attachment,
        int|string|null $submissionId,
        string $error,
    ): array {
        return [
            'submission_id' => $submissionId,
            'attachment_id' => $attachment->getKey(),
            'original_filename' => $attachment->original_name,
            'original_path' => $attachment->file_path,
            'error' => $error,
        ];
    }
}
