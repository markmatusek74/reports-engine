<?php

namespace MarkMatusek74\ReportingEngine\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use MarkMatusek74\ReportingEngine\Models\Report;
use MarkMatusek74\ReportingEngine\Services\ReportingEngineService;

class ReportViewer extends Component
{
    use WithPagination;

    public Report $report;
    public array $filters = [];
    public array $sorting = [];
    public int $perPage = 25;
    public string $search = '';
    
    protected $queryString = [
        'filters' => ['except' => []],
        'sorting' => ['except' => []],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(Report $report)
    {
        $this->report = $report;
        $this->perPage = config('reporting-engine.reports.default_per_page', 25);
        
        // Initialize default sorting if set
        if ($report->sorting) {
            $this->sorting = $report->sorting;
        }
        
        // Initialize default filters if set
        if ($report->filters) {
            $this->filters = $report->filters;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function applyFilter($field, $value)
    {
        if (empty($value)) {
            unset($this->filters[$field]);
        } else {
            $this->filters[$field] = $value;
        }
        
        $this->resetPage();
    }

    public function clearFilter($field)
    {
        unset($this->filters[$field]);
        $this->resetPage();
    }

    public function clearAllFilters()
    {
        $this->filters = [];
        $this->search = '';
        $this->resetPage();
    }

    public function sortBy($field, $direction = 'asc')
    {
        $this->sorting = [
            'field' => $field,
            'direction' => $direction,
        ];
        
        $this->resetPage();
    }

    public function toggleSort($field)
    {
        if (isset($this->sorting['field']) && $this->sorting['field'] === $field) {
            $currentDirection = $this->sorting['direction'] ?? 'asc';
            $newDirection = $currentDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $newDirection = 'asc';
        }
        
        $this->sortBy($field, $newDirection);
    }

    public function exportReport($format = 'csv')
    {
        $this->dispatch('export-report', [
            'reportId' => $this->report->id,
            'format' => $format,
            'filters' => $this->filters,
            'search' => $this->search,
        ]);
    }

    public function render()
    {
        $reportingService = app(ReportingEngineService::class);
        
        // Prepare filters with search
        $allFilters = $this->filters;
        if (!empty($this->search)) {
            $searchableFields = $this->report->searchableFields;
            foreach ($searchableFields as $field) {
                $allFilters[$field->name] = [
                    'operator' => 'like',
                    'value' => $this->search,
                ];
            }
        }
        
        // Add sorting to options
        $options = [
            'per_page' => $this->perPage,
            'page' => $this->getPage(),
        ];
        
        if (!empty($this->sorting)) {
            $options['sorting'] = [$this->sorting];
        }

        $reportData = $reportingService->generateReport($this->report, $allFilters, $options);

        return view('reporting-engine::livewire.report-viewer', [
            'reportData' => $reportData,
            'fields' => $this->report->visibleFields,
            'filterableFields' => $this->report->filterableFields,
            'sortableFields' => $this->report->sortableFields,
        ]);
    }
}