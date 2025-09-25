<?php

namespace MarkMatusek74\ReportingEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use MarkMatusek74\ReportingEngine\Database\Factories\ReportFactory;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'type',
        'configuration',
        'filters',
        'sorting',
        'columns',
        'is_public',
        'is_cached',
        'cache_ttl',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'configuration' => 'array',
        'filters' => 'array',
        'sorting' => 'array',
        'columns' => 'array',
        'is_public' => 'boolean',
        'is_cached' => 'boolean',
        'cache_ttl' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->slug)) {
                $report->slug = Str::slug($report->name);
            }
        });

        static::updating(function ($report) {
            if ($report->isDirty('name') && empty($report->slug)) {
                $report->slug = Str::slug($report->name);
            }
        });
    }

    /**
     * Get the report fields.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ReportField::class)->orderBy('sort_order');
    }

    /**
     * Get visible fields only.
     */
    public function visibleFields(): HasMany
    {
        return $this->fields()->where('is_visible', true);
    }

    /**
     * Get filterable fields.
     */
    public function filterableFields(): HasMany
    {
        return $this->fields()->where('is_filterable', true);
    }

    /**
     * Get sortable fields.
     */
    public function sortableFields(): HasMany
    {
        return $this->fields()->where('is_sortable', true);
    }

    /**
     * Get searchable fields.
     */
    public function searchableFields(): HasMany
    {
        return $this->fields()->where('is_searchable', true);
    }

    /**
     * Get cached report data.
     */
    public function cachedData(): HasMany
    {
        return $this->hasMany(ReportData::class);
    }

    /**
     * Get the latest cached data.
     */
    public function latestCachedData()
    {
        return $this->cachedData()
            ->where('expires_at', '>', now())
            ->latest('generated_at')
            ->first();
    }

    /**
     * Check if report is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if report is public.
     */
    public function isPublic(): bool
    {
        return $this->is_public;
    }

    /**
     * Check if report uses caching.
     */
    public function isCached(): bool
    {
        return $this->is_cached;
    }

    /**
     * Get the configuration value by key.
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->configuration, $key, $default);
    }

    /**
     * Set configuration value.
     */
    public function setConfig(string $key, $value): void
    {
        $config = $this->configuration ?? [];
        data_set($config, $key, $value);
        $this->configuration = $config;
    }

    /**
     * Scope for active reports.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for public reports.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for reports by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ReportFactory::new();
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable()
    {
        return config('reporting-engine.database.tables.reports', parent::getTable());
    }
}