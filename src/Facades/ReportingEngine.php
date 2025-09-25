<?php

namespace MarkMatusek74\ReportingEngine\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \MarkMatusek74\ReportingEngine\Models\Report createReport(array $data)
 * @method static array generateReport(\MarkMatusek74\ReportingEngine\Models\Report $report, array $filters = [], array $options = [])
 * @method static \MarkMatusek74\ReportingEngine\Models\ReportModel registerModel(string $modelClass, string $name = null)
 * @method static \Illuminate\Support\Collection getAvailableModels()
 * 
 * @see \MarkMatusek74\ReportingEngine\Services\ReportingEngineService
 */
class ReportingEngine extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'reporting-engine';
    }
}