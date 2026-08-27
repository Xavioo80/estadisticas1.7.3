<?php

namespace App\Jobs;

use App\Models\ExportJob;
use App\Services\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Process Large Export Job
 * 
 * This job handles the asynchronous processing of large data exports.
 * It uses the ExportService to process exports in chunks to avoid
 * memory issues and timeouts.
 * 
 * Features:
 * - Progress tracking
 * - Automatic retry on failure
 * - Detailed logging
 * - Cleanup of old exports
 */
class ProcessLargeExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * The maximum amount of memory to use (in MB)
     */
    public int $memoryLimit = 512;

    /**
     * The export job model
     */
    protected ExportJob $exportJob;

    /**
     * Create a new job instance.
     */
    public function __construct(ExportJob $exportJob)
    {
        $this->exportJob = $exportJob;
        $this->onQueue('exports');
        $this->onConnection('database');
    }

    /**
     * Execute the job.
     */
    public function handle(ExportService $exportService): void
    {
        // Configure PHP for large export
        $this->configurePHP();

        Log::info("Starting large export job", [
            'export_id' => $this->exportJob->id,
            'total_records' => $this->exportJob->total_records,
            'filters' => $this->exportJob->filters,
        ]);

        try {
            // Check if export still exists
            $exportJob = ExportJob::find($this->exportJob->id);

            if (!$exportJob) {
                Log::warning("Export job not found, aborting", ['export_id' => $this->exportJob->id]);
                return;
            }

            // Check if already completed or failed
            if ($exportJob->isCompleted() || $exportJob->isFailed()) {
                Log::info("Export already processed", [
                    'export_id' => $exportJob->id,
                    'status' => $exportJob->status,
                ]);
                return;
            }

            // Determine export method based on record count
            if ($exportJob->total_records > 100000) {
                // For very large exports, use generator method
                $filename = $exportService->exportWithGenerator($exportJob);
            }
            else {
                // For normal large exports, use chunked method
                $filename = $exportService->exportInChunks($exportJob);
            }

            Log::info("Export completed successfully", [
                'export_id' => $exportJob->id,
                'filename' => $filename,
            ]);

        }
        catch (\Exception $e) {
            Log::error("Export job failed", [
                'export_id' => $this->exportJob->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark as failed
            $this->exportJob->markAsFailed($e->getMessage());

            throw $e;
        }
    }

    /**
     * Configure PHP settings for large export
     */
    protected function configurePHP(): void
    {
        // Set memory limit
        ini_set('memory_limit', $this->memoryLimit . 'M');

        // Set execution time to unlimited
        set_time_limit(0);

        // Increase PHP timeout
        ini_set('max_execution_time', '0');

        // Disable script timeout
        ini_set('default_socket_timeout', '600');
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessLargeExportJob permanently failed", [
            'export_id' => $this->exportJob->id,
            'error' => $exception->getMessage(),
        ]);

        // Ensure the export job is marked as failed
        try {
            $exportJob = ExportJob::find($this->exportJob->id);
            if ($exportJob && !$exportJob->isCompleted()) {
                $exportJob->markAsFailed($exception->getMessage());
            }
        }
        catch (\Exception $e) {
            Log::error("Failed to update export job status", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the unique ID for this job
     */
    public function uniqueId(): string
    {
        return 'export_' . $this->exportJob->id;
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'export',
            'export_type_' . $this->exportJob->type,
            'export_id_' . $this->exportJob->id,
        ];
    }
}
