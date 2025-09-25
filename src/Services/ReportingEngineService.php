<?php

namespace MarkMatusek74\ReportingEngine\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use MarkMatusek74\ReportingEngine\Models\Report;
use MarkMatusek74\ReportingEngine\Models\ReportModel;
use MarkMatusek74\ReportingEngine\Models\ReportData;

class ReportingEngineService
{
    /**
     * Generate report data.
     */
    public function generateReport(Report $report, array $filters = [], array $options = []): array
    {
        // Check for cached data first
        if ($report->isCached()) {
            $cachedData = $this->getCachedData($report, $filters);
            if ($cachedData) {
                return $cachedData;
            }
        }

        // Generate fresh data
        $data = $this->generateFreshData($report, $filters, $options);

        // Cache the data if caching is enabled
        if ($report->isCached()) {
            $this->cacheData($report, $data, $filters);
        }

        return $data;
    }

    /**
     * Get cached report data.
     */
    protected function getCachedData(Report $report, array $filters = []): ?array
    {
        $filtersHash = hash('sha256', serialize($filters));
        
        $cachedData = $report->cachedData()
            ->valid()
            ->where('data_hash', ReportData::generateHash([], $filters))
            ->latest('generated_at')
            ->first();

        return $cachedData ? $cachedData->getData() : null;
    }

    /**
     * Generate fresh report data.
     */
    protected function generateFreshData(Report $report, array $filters = [], array $options = []): array
    {
        $fields = $report->visibleFields;
        
        if ($fields->isEmpty()) {
            return ['data' => [], 'meta' => ['total' => 0]];
        }

        // Build the query based on report configuration
        $query = $this->buildQuery($report, $fields, $filters);

        // Apply pagination
        $perPage = $options['per_page'] ?? config('reporting-engine.reports.default_per_page', 25);
        $page = $options['page'] ?? 1;

        // Get total count before pagination
        $total = $query->count();

        // Apply pagination
        $data = $query->skip(($page - 1) * $perPage)
                     ->take($perPage)
                     ->get()
                     ->map(function ($item) use ($fields) {
                         return $this->formatRowData($item, $fields);
                     })
                     ->toArray();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Build query for report.
     */
    protected function buildQuery(Report $report, Collection $fields, array $filters = []): Builder
    {
        // Get the primary model for this report
        $primaryModel = $this->getPrimaryModel($report);
        
        if (!$primaryModel) {
            throw new \Exception('No primary model found for report');
        }

        $modelInstance = $primaryModel->getModelInstance();
        $query = $modelInstance->newQuery();

        // Select fields
        $selectFields = [];
        foreach ($fields as $field) {
            $selectFields[] = $this->buildSelectExpression($field);
        }
        
        $query->select($selectFields);

        // Apply joins for relationships
        $this->applyJoins($query, $fields);

        // Apply filters
        $this->applyFilters($query, $fields, $filters);

        // Apply sorting
        $this->applySorting($query, $report, $fields);

        return $query;
    }

    /**
     * Get primary model for report.
     */
    protected function getPrimaryModel(Report $report): ?ReportModel
    {
        // For now, we'll determine the primary model from the first field
        $firstField = $report->fields()->first();
        
        if (!$firstField) {
            return null;
        }

        return ReportModel::active()
            ->where('table_name', $firstField->source_table)
            ->first();
    }

    /**
     * Build select expression for field.
     */
    protected function buildSelectExpression($field): string
    {
        $expression = $field->source_table . '.' . $field->source_column;
        
        // Apply aggregation if specified
        if ($field->supportsAggregation()) {
            $functions = $field->getAggregationFunctions();
            if (!empty($functions)) {
                $function = $functions[0]; // Use first function for now
                $expression = strtoupper($function) . '(' . $expression . ')';
            }
        }

        return $expression . ' as ' . $field->name;
    }

    /**
     * Apply joins for relationships.
     */
    protected function applyJoins(Builder $query, Collection $fields): void
    {
        $joinedTables = [];
        
        foreach ($fields as $field) {
            if ($field->isRelationship()) {
                $this->applyRelationshipJoin($query, $field, $joinedTables);
            }
        }
    }

    /**
     * Apply relationship join.
     */
    protected function applyRelationshipJoin(Builder $query, $field, array &$joinedTables): void
    {
        $relatedTable = $field->source_table;
        
        if (in_array($relatedTable, $joinedTables)) {
            return; // Already joined
        }

        $relationshipType = $field->relationship;
        $relatedModel = $field->related_model;
        
        if ($relationshipType === 'belongsTo') {
            $query->leftJoin(
                $relatedTable,
                $query->getModel()->getTable() . '.' . $field->related_field,
                '=',
                $relatedTable . '.id'
            );
        } elseif ($relationshipType === 'hasMany') {
            $query->leftJoin(
                $relatedTable,
                $query->getModel()->getTable() . '.id',
                '=',
                $relatedTable . '.' . $field->related_field
            );
        }

        $joinedTables[] = $relatedTable;
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters(Builder $query, Collection $fields, array $filters): void
    {
        foreach ($filters as $fieldName => $filterValue) {
            $field = $fields->firstWhere('name', $fieldName);
            
            if (!$field || !$field->isFilterable()) {
                continue;
            }

            $this->applyFieldFilter($query, $field, $filterValue);
        }
    }

    /**
     * Apply filter for specific field.
     */
    protected function applyFieldFilter(Builder $query, $field, $filterValue): void
    {
        $column = $field->source_table . '.' . $field->source_column;

        if (is_array($filterValue)) {
            // Handle array filters (IN, BETWEEN, etc.)
            if (isset($filterValue['operator'])) {
                $operator = $filterValue['operator'];
                $value = $filterValue['value'];

                switch ($operator) {
                    case 'in':
                        $query->whereIn($column, $value);
                        break;
                    case 'not_in':
                        $query->whereNotIn($column, $value);
                        break;
                    case 'between':
                        $query->whereBetween($column, $value);
                        break;
                    case 'not_between':
                        $query->whereNotBetween($column, $value);
                        break;
                    case 'like':
                        $query->where($column, 'like', '%' . $value . '%');
                        break;
                    case 'not_like':
                        $query->where($column, 'not like', '%' . $value . '%');
                        break;
                    default:
                        $query->where($column, $operator, $value);
                }
            }
        } else {
            // Simple value filter
            if ($field->type === 'string' || $field->type === 'text') {
                $query->where($column, 'like', '%' . $filterValue . '%');
            } else {
                $query->where($column, $filterValue);
            }
        }
    }

    /**
     * Apply sorting to query.
     */
    protected function applySorting(Builder $query, Report $report, Collection $fields): void
    {
        $sorting = $report->sorting ?? [];
        
        foreach ($sorting as $sort) {
            $fieldName = $sort['field'] ?? null;
            $direction = $sort['direction'] ?? 'asc';
            
            if (!$fieldName) {
                continue;
            }

            $field = $fields->firstWhere('name', $fieldName);
            
            if (!$field || !$field->isSortable()) {
                continue;
            }

            $column = $field->source_table . '.' . $field->source_column;
            $query->orderBy($column, $direction);
        }
    }

    /**
     * Format row data for output.
     */
    protected function formatRowData($item, Collection $fields): array
    {
        $formattedData = [];

        foreach ($fields as $field) {
            $value = $item->{$field->name} ?? null;
            $formattedData[$field->name] = $field->formatValue($value);
        }

        return $formattedData;
    }

    /**
     * Cache report data.
     */
    protected function cacheData(Report $report, array $data, array $filters = []): void
    {
        $hash = ReportData::generateHash($data, $filters);
        $expiresAt = now()->addSeconds($report->cache_ttl);

        ReportData::create([
            'report_id' => $report->id,
            'data' => $data,
            'filters_applied' => $filters,
            'data_hash' => $hash,
            'generated_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        // Clean up old cached data
        $this->cleanupOldCache($report);
    }

    /**
     * Clean up old cached data.
     */
    protected function cleanupOldCache(Report $report): void
    {
        // Keep only the latest 10 cached entries per report
        $report->cachedData()
               ->expired()
               ->oldest('generated_at')
               ->skip(10)
               ->delete();
    }

    /**
     * Register a model for reporting.
     */
    public function registerModel(string $modelClass, string $name = null): ReportModel
    {
        if (!class_exists($modelClass)) {
            throw new \Exception("Model class {$modelClass} does not exist");
        }

        $instance = new $modelClass;
        $name = $name ?? class_basename($modelClass);

        $reportModel = ReportModel::updateOrCreate(
            ['model_class' => $modelClass],
            [
                'name' => $name,
                'table_name' => $instance->getTable(),
                'primary_key' => $instance->getKeyName(),
                'is_active' => true,
            ]
        );

        // Refresh model information
        $reportModel->refreshModelInfo();

        return $reportModel;
    }

    /**
     * Get available models for reporting.
     */
    public function getAvailableModels(): Collection
    {
        return ReportModel::active()->get();
    }
}