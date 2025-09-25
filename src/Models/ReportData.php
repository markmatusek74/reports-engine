<?php

namespace MarkMatusek74\ReportingEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MarkMatusek74\ReportingEngine\Database\Factories\ReportDataFactory;

class ReportData extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'data',
        'filters_applied',
        'data_hash',
        'generated_at',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'filters_applied' => 'array',
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the report that owns this data.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Check if cached data is still valid.
     */
    public function isValid(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /**
     * Check if cached data has expired.
     */
    public function hasExpired(): bool
    {
        return !$this->isValid();
    }

    /**
     * Get the cached data.
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }

    /**
     * Get the filters that were applied.
     */
    public function getFiltersApplied(): array
    {
        return $this->filters_applied ?? [];
    }

    /**
     * Generate hash for the current data.
     */
    public static function generateHash(array $data, array $filters = []): string
    {
        return hash('sha256', serialize(['data' => $data, 'filters' => $filters]));
    }

    /**
     * Scope for valid (non-expired) data.
     */
    public function scopeValid($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for expired data.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ReportDataFactory::new();
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable()
    {
        return config('reporting-engine.database.tables.report_data', parent::getTable());
    }
}