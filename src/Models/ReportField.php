<?php

namespace MarkMatusek74\ReportingEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MarkMatusek74\ReportingEngine\Database\Factories\ReportFieldFactory;

class ReportField extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'name',
        'label',
        'type',
        'source_column',
        'source_table',
        'description',
        'is_sortable',
        'is_searchable',
        'is_filterable',
        'is_visible',
        'is_required',
        'sort_order',
        'format',
        'validation_rules',
        'filter_options',
        'aggregation_functions',
        'relationship',
        'related_model',
        'related_field',
    ];

    protected $casts = [
        'is_sortable' => 'boolean',
        'is_searchable' => 'boolean',
        'is_filterable' => 'boolean',
        'is_visible' => 'boolean',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'validation_rules' => 'array',
        'filter_options' => 'array',
        'aggregation_functions' => 'array',
    ];

    /**
     * Get the report that owns the field.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Check if field is sortable.
     */
    public function isSortable(): bool
    {
        return $this->is_sortable;
    }

    /**
     * Check if field is searchable.
     */
    public function isSearchable(): bool
    {
        return $this->is_searchable;
    }

    /**
     * Check if field is filterable.
     */
    public function isFilterable(): bool
    {
        return $this->is_filterable;
    }

    /**
     * Check if field is visible.
     */
    public function isVisible(): bool
    {
        return $this->is_visible;
    }

    /**
     * Check if field is required.
     */
    public function isRequired(): bool
    {
        return $this->is_required;
    }

    /**
     * Get validation rules as Laravel validation array.
     */
    public function getValidationRules(): array
    {
        $rules = $this->validation_rules ?? [];

        if ($this->is_required) {
            $rules[] = 'required';
        }

        return $rules;
    }

    /**
     * Get filter options for the field.
     */
    public function getFilterOptions(): array
    {
        return $this->filter_options ?? [];
    }

    /**
     * Check if field supports aggregation.
     */
    public function supportsAggregation(): bool
    {
        return !empty($this->aggregation_functions);
    }

    /**
     * Get available aggregation functions.
     */
    public function getAggregationFunctions(): array
    {
        return $this->aggregation_functions ?? [];
    }

    /**
     * Check if field is a relationship field.
     */
    public function isRelationship(): bool
    {
        return !empty($this->relationship);
    }

    /**
     * Format value for display.
     */
    public function formatValue($value)
    {
        if (is_null($value)) {
            return null;
        }

        return match ($this->type) {
            'date' => $this->formatDate($value),
            'datetime' => $this->formatDateTime($value),
            'decimal' => $this->formatDecimal($value),
            'boolean' => $this->formatBoolean($value),
            default => $value,
        };
    }

    /**
     * Format date value.
     */
    protected function formatDate($value): string
    {
        $format = $this->format ?? config('reporting-engine.ui.date_format', 'Y-m-d');
        return \Carbon\Carbon::parse($value)->format($format);
    }

    /**
     * Format datetime value.
     */
    protected function formatDateTime($value): string
    {
        $format = $this->format ?? config('reporting-engine.ui.datetime_format', 'Y-m-d H:i:s');
        return \Carbon\Carbon::parse($value)->format($format);
    }

    /**
     * Format decimal value.
     */
    protected function formatDecimal($value): string
    {
        $precision = $this->getFormatOption('precision', 2);
        return number_format((float) $value, $precision);
    }

    /**
     * Format boolean value.
     */
    protected function formatBoolean($value): string
    {
        return $value ? 'Yes' : 'No';
    }

    /**
     * Get format option.
     */
    protected function getFormatOption(string $key, $default = null)
    {
        if (empty($this->format)) {
            return $default;
        }

        $options = json_decode($this->format, true);
        return $options[$key] ?? $default;
    }

    /**
     * Scope for visible fields.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope for sortable fields.
     */
    public function scopeSortable($query)
    {
        return $query->where('is_sortable', true);
    }

    /**
     * Scope for filterable fields.
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope for searchable fields.
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ReportFieldFactory::new();
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable()
    {
        return config('reporting-engine.database.tables.report_fields', parent::getTable());
    }
}