<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportJob extends Model
{
    protected $table = 'export_jobs';

    public $timestamps = true;

    public $keyType = 'string';

    public $incrementing = false;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    const TYPE_REGISTROS = 'registros';
    const TYPE_ATENCIONES = 'atenciones';
    const TYPE_INFORMES = 'informes';

    protected $fillable = [
        'id',
        'user_id',
        'type',
        'filename',
        'filepath',
        'filters',
        'status',
        'total_records',
        'processed_records',
        'progress_percentage',
        'error_message',
        'started_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'total_records' => 'integer',
        'processed_records' => 'integer',
        'progress_percentage' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function updateProgress(int $processedRecords, int $totalRecords = null): void
    {
        $this->processed_records = $processedRecords;

        if ($totalRecords !== null) {
            $this->total_records = $totalRecords;
        }

        if ($this->total_records > 0) {
            $this->progress_percentage = (int)round(($this->processed_records / $this->total_records) * 100);
        }

        $this->save();
    }

    public function markAsProcessing(): void
    {
        $this->status = self::STATUS_PROCESSING;
        $this->started_at = now();
        $this->save();
    }

    public function markAsCompleted(string $filename, string $filepath): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->filename = $filename;
        $this->filepath = $filepath;
        $this->completed_at = now();
        $this->progress_percentage = 100;
        $this->save();
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->completed_at = now();
        $this->save();
    }

    public function getDownloadUrl(): ?string
    {
        if (!$this->isCompleted() || !$this->filepath) {
            return null;
        }

        return '/registros/export-async/' . $this->id . '/download';
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING])
            ->where('expires_at', '>', now());
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
