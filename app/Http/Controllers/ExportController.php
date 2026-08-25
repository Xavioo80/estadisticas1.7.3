<?php

namespace App\Http\Controllers;

use App\Models\ExportJob;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OptimizedChunkedExport;

/**
 * Export Controller
 * 
 * Handles all export-related operations including:
 * - Creating new export jobs
 * - Checking export status
 * - Downloading completed exports
 * - Listing user's export history
 */
class ExportController extends Controller
{
    protected ExportService $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Create a new export job
     * 
     * POST /registros/export-async/create
     */
    public function create(Request $request): JsonResponse
    {
        try {
            // Get current user ID (or null for guest exports)
            $userId = $request->user()?->id;

            // Prepare filters
            $filters = [
                'years' => $request->input('years', []),
                'months' => $request->input('months', []),
                'global_search' => $request->input('filters.global_search', ''),
                'columnFilters' => $request->input('filters.columnFilters', []),
            ];

            // Create export job
            $exportJob = $this->exportService->createExport($filters, $userId);

            Log::info("Export job created", [
                'export_id' => $exportJob->id,
                'total_records' => $exportJob->total_records,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Export job created successfully',
                'data' => [
                    'uuid' => $exportJob->id,
                    'status' => $exportJob->status,
                    'total_records' => $exportJob->total_records,
                    'estimated_records' => $exportJob->total_records,
                    'status_url' => '/registros/export-async/' . $exportJob->id . '/status',
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error("Failed to create export job", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create export job: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get export status
     * 
     * GET /registros/export-async/{uuid}/status
     */
    public function status(string $uuid): JsonResponse
    {
        try {
            $exportJob = $this->exportService->getExportByUuid($uuid);

            if (!$exportJob) {
                return response()->json([
                    'success' => false,
                    'message' => 'Export not found',
                ], 404);
            }

            $response = [
                'uuid' => $exportJob->id,
                'status' => $exportJob->status,
                'progress' => $exportJob->progress_percentage,
                'total_records' => $exportJob->total_records,
                'processed_records' => $exportJob->processed_records,
                'created_at' => $exportJob->created_at?->toIso8601String(),
                'started_at' => $exportJob->started_at?->toIso8601String(),
                'completed_at' => $exportJob->completed_at?->toIso8601String(),
            ];

            // Add download URL if completed
            if ($exportJob->isCompleted()) {
                $response['download_url'] = '/registros/export-async/' . $exportJob->id . '/download';
                $response['filename'] = $exportJob->filename;
            }

            // Add error message if failed
            if ($exportJob->isFailed()) {
                $response['error'] = $exportJob->error_message;
            }

            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to get export status", [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get export status: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download completed export
     * 
     * GET /registros/export-async/{uuid}/download
     */
    public function download(string $uuid)
    {
        try {
            $exportJob = $this->exportService->getExportByUuid($uuid);

            if (!$exportJob) {
                abort(404, 'Export not found');
            }

            if (!$exportJob->isCompleted()) {
                abort(400, 'Export is not ready for download');
            }

            if (!$exportJob->filepath || !Storage::disk('local')->exists($exportJob->filepath)) {
                abort(404, 'Export file not found');
            }

            Log::info("Downloading export", [
                'uuid' => $uuid,
                'filename' => $exportJob->filename,
            ]);

            return Storage::disk('local')->download(
                $exportJob->filepath,
                $exportJob->filename
            );
        } catch (\Exception $e) {
            Log::error("Failed to download export", [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Failed to download export: ' . $e->getMessage());
        }
    }

    /**
     * Get user's export history
     * 
     * GET /registros/export-async/list
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()?->id;

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                ], 401);
            }

            $exports = $this->exportService->getUserExports($userId, 20);

            return response()->json([
                'success' => true,
                'data' => $exports->map(function ($export) {
                    return [
                        'uuid' => $export->id,
                        'status' => $export->status,
                        'progress' => $export->progress_percentage,
                        'total_records' => $export->total_records,
                        'filename' => $export->filename,
                        'created_at' => $export->created_at?->toIso8601String(),
                        'completed_at' => $export->completed_at?->toIso8601String(),
                        'download_url' => $export->isCompleted() 
                            ? '/registros/export-async/' . $export->id . '/download'
                            : null,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to get export history", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get export history: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get active exports for current user
     * 
     * GET /registros/export-async/active
     */
    public function active(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()?->id;

            $exports = ExportJob::where('user_id', $userId)
                ->whereIn('status', [ExportJob::STATUS_PENDING, ExportJob::STATUS_PROCESSING])
                ->where('expires_at', '>', now())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $exports->map(function ($export) {
                    return [
                        'uuid' => $export->id,
                        'status' => $export->status,
                        'progress' => $export->progress_percentage,
                        'total_records' => $export->total_records,
                        'processed_records' => $export->processed_records,
                        'created_at' => $export->created_at?->toIso8601String(),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get active exports: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an export job (if still pending)
     * 
     * DELETE /registros/export-async/{uuid}
     */
    public function cancel(string $uuid, Request $request): JsonResponse
    {
        try {
            $exportJob = $this->exportService->getExportByUuid($uuid);

            if (!$exportJob) {
                return response()->json([
                    'success' => false,
                    'message' => 'Export not found',
                ], 404);
            }

            // Check ownership
            if ($exportJob->user_id !== $request->user()?->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Can only cancel pending exports
            if (!$exportJob->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel export that is already processing',
                ], 400);
            }

            $exportJob->update(['status' => ExportJob::STATUS_FAILED, 'error_message' => 'Cancelled by user']);

            return response()->json([
                'success' => true,
                'message' => 'Export cancelled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel export: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Quick sync export for small datasets
     * 
     * POST /registros/export-async/quick
     */
    public function quickExport(Request $request)
    {
        $request->validate([
            'years' => 'nullable|array',
            'months' => 'nullable|array',
            'filters' => 'nullable|array',
        ]);

        try {
            // Configure PHP for export
            ini_set('memory_limit', '512M');
            set_time_limit(300); // 5 minutes max for sync export

            $filters = [
                'years' => $request->input('years', []),
                'months' => $request->input('months', []),
                'global_search' => $request->input('filters.global_search', ''),
                'columnFilters' => $request->input('filters.columnFilters', []),
            ];

            // Check estimated record count
            $recordCount = $this->exportService->getRecordCount($filters);

            // For small exports (< 5000 records), do sync export
            if ($recordCount <= 5000) {
                $filename = 'Registros_AT1_' . date('Y-m-d_His') . '.xlsx';
                
                $export = new OptimizedChunkedExport($filters);
                
                return Excel::download($export, $filename);
            }

            // For larger exports, redirect to async
            $userId = $request->user()?->id;
            $exportJob = $this->exportService->createExport($filters, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Dataset too large for quick export. Using async export instead.',
                'data' => [
                    'uuid' => $exportJob->id,
                    'status' => $exportJob->status,
                    'total_records' => $recordCount,
                    'status_url' => '/registros/export-async/' . $exportJob->id . '/status',
                ],
            ], 202);
        } catch (\Exception $e) {
            Log::error("Quick export failed", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
