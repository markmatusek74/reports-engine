<?php

namespace MarkMatusek74\ReportingEngine\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use MarkMatusek74\ReportingEngine\Models\Report;

class ReportManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $typeFilter = '';
    public bool $showCreateForm = false;
    
    // Form properties
    public string $name = '';
    public string $description = '';
    public string $status = 'draft';
    public string $type = 'table';
    public bool $isPublic = false;
    public bool $isCached = true;
    public int $cacheTtl = 3600;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:active,inactive,draft',
        'type' => 'required|in:table,chart,dashboard',
        'isPublic' => 'boolean',
        'isCached' => 'boolean',
        'cacheTtl' => 'integer|min:60',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function showCreateForm()
    {
        $this->showCreateForm = true;
        $this->resetForm();
    }

    public function hideCreateForm()
    {
        $this->showCreateForm = false;
        $this->resetForm();
    }

    public function createReport()
    {
        $this->validate();

        $report = Report::create([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'type' => $this->type,
            'is_public' => $this->isPublic,
            'is_cached' => $this->isCached,
            'cache_ttl' => $this->cacheTtl,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('report-created', ['reportId' => $report->id]);
        $this->hideCreateForm();
        
        session()->flash('message', 'Report created successfully!');
    }

    public function deleteReport(Report $report)
    {
        $report->delete();
        
        $this->dispatch('report-deleted', ['reportId' => $report->id]);
        session()->flash('message', 'Report deleted successfully!');
    }

    public function duplicateReport(Report $report)
    {
        $newReport = $report->replicate();
        $newReport->name = $report->name . ' (Copy)';
        $newReport->slug = null; // Will be auto-generated
        $newReport->status = 'draft';
        $newReport->created_by = auth()->id();
        $newReport->save();

        // Duplicate fields
        foreach ($report->fields as $field) {
            $newField = $field->replicate();
            $newField->report_id = $newReport->id;
            $newField->save();
        }

        $this->dispatch('report-duplicated', ['reportId' => $newReport->id]);
        session()->flash('message', 'Report duplicated successfully!');
    }

    public function toggleStatus(Report $report)
    {
        $newStatus = $report->status === 'active' ? 'inactive' : 'active';
        $report->update(['status' => $newStatus]);
        
        $this->dispatch('report-status-changed', [
            'reportId' => $report->id,
            'status' => $newStatus,
        ]);
    }

    protected function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->status = 'draft';
        $this->type = 'table';
        $this->isPublic = false;
        $this->isCached = true;
        $this->cacheTtl = 3600;
    }

    public function render()
    {
        $query = Report::query();

        // Apply search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // Apply type filter
        if (!empty($this->typeFilter)) {
            $query->where('type', $this->typeFilter);
        }

        $reports = $query->withCount('fields')
                        ->latest()
                        ->paginate(15);

        $statusOptions = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'draft' => 'Draft',
        ];

        $typeOptions = [
            'table' => 'Table',
            'chart' => 'Chart',
            'dashboard' => 'Dashboard',
        ];

        return view('reporting-engine::livewire.report-manager', [
            'reports' => $reports,
            'statusOptions' => $statusOptions,
            'typeOptions' => $typeOptions,
        ]);
    }
}