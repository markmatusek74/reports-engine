<?php

namespace MarkMatusek74\ReportingEngine\Http\Livewire;

use Livewire\Component;
use MarkMatusek74\ReportingEngine\Models\Report;
use MarkMatusek74\ReportingEngine\Models\ReportField;
use MarkMatusek74\ReportingEngine\Models\ReportModel;

class ReportBuilder extends Component
{
    public ?Report $report = null;
    public bool $isEditing = false;
    
    // Report properties
    public string $name = '';
    public string $description = '';
    public string $status = 'draft';
    public string $type = 'table';
    public bool $isPublic = false;
    public bool $isCached = true;
    public int $cacheTtl = 3600;
    
    // Field being edited
    public ?ReportField $editingField = null;
    public bool $showFieldForm = false;
    
    // Field form properties
    public string $fieldName = '';
    public string $fieldLabel = '';
    public string $fieldType = 'string';
    public string $sourceColumn = '';
    public string $sourceTable = '';
    public string $fieldDescription = '';
    public bool $isSortable = true;
    public bool $isSearchable = true;
    public bool $isFilterable = true;
    public bool $isVisible = true;
    public bool $isRequired = false;
    public int $sortOrder = 0;
    public string $format = '';
    public array $validationRules = [];
    public array $filterOptions = [];
    public string $relationship = '';
    public string $relatedModel = '';
    public string $relatedField = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:active,inactive,draft',
        'type' => 'required|in:table,chart,dashboard',
        'isPublic' => 'boolean',
        'isCached' => 'boolean',
        'cacheTtl' => 'integer|min:60',
    ];

    protected $fieldRules = [
        'fieldName' => 'required|string|max:255',
        'fieldLabel' => 'required|string|max:255',
        'fieldType' => 'required|string',
        'sourceColumn' => 'required|string|max:255',
        'sourceTable' => 'required|string|max:255',
        'isSortable' => 'boolean',
        'isSearchable' => 'boolean',
        'isFilterable' => 'boolean',
        'isVisible' => 'boolean',
        'isRequired' => 'boolean',
        'sortOrder' => 'integer|min:0',
    ];

    public function mount(?Report $report = null)
    {
        if ($report) {
            $this->report = $report;
            $this->isEditing = true;
            $this->loadReportData();
        }
    }

    protected function loadReportData()
    {
        if ($this->report) {
            $this->name = $this->report->name;
            $this->description = $this->report->description ?? '';
            $this->status = $this->report->status;
            $this->type = $this->report->type;
            $this->isPublic = $this->report->is_public;
            $this->isCached = $this->report->is_cached;
            $this->cacheTtl = $this->report->cache_ttl;
        }
    }

    public function saveReport()
    {
        $this->validate();

        if ($this->isEditing) {
            $this->report->update([
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'type' => $this->type,
                'is_public' => $this->isPublic,
                'is_cached' => $this->isCached,
                'cache_ttl' => $this->cacheTtl,
                'updated_by' => auth()->id(),
            ]);
            
            session()->flash('message', 'Report updated successfully!');
        } else {
            $this->report = Report::create([
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'type' => $this->type,
                'is_public' => $this->isPublic,
                'is_cached' => $this->isCached,
                'cache_ttl' => $this->cacheTtl,
                'created_by' => auth()->id(),
            ]);
            
            $this->isEditing = true;
            session()->flash('message', 'Report created successfully!');
        }

        $this->dispatch('report-saved', ['reportId' => $this->report->id]);
    }

    public function showFieldForm(?ReportField $field = null)
    {
        $this->editingField = $field;
        $this->showFieldForm = true;
        
        if ($field) {
            $this->loadFieldData($field);
        } else {
            $this->resetFieldForm();
            $this->sortOrder = $this->report->fields()->count();
        }
    }

    public function hideFieldForm()
    {
        $this->showFieldForm = false;
        $this->editingField = null;
        $this->resetFieldForm();
    }

    protected function loadFieldData(ReportField $field)
    {
        $this->fieldName = $field->name;
        $this->fieldLabel = $field->label;
        $this->fieldType = $field->type;
        $this->sourceColumn = $field->source_column;
        $this->sourceTable = $field->source_table;
        $this->fieldDescription = $field->description ?? '';
        $this->isSortable = $field->is_sortable;
        $this->isSearchable = $field->is_searchable;
        $this->isFilterable = $field->is_filterable;
        $this->isVisible = $field->is_visible;
        $this->isRequired = $field->is_required;
        $this->sortOrder = $field->sort_order;
        $this->format = $field->format ?? '';
        $this->validationRules = $field->validation_rules ?? [];
        $this->filterOptions = $field->filter_options ?? [];
        $this->relationship = $field->relationship ?? '';
        $this->relatedModel = $field->related_model ?? '';
        $this->relatedField = $field->related_field ?? '';
    }

    protected function resetFieldForm()
    {
        $this->fieldName = '';
        $this->fieldLabel = '';
        $this->fieldType = 'string';
        $this->sourceColumn = '';
        $this->sourceTable = '';
        $this->fieldDescription = '';
        $this->isSortable = true;
        $this->isSearchable = true;
        $this->isFilterable = true;
        $this->isVisible = true;
        $this->isRequired = false;
        $this->sortOrder = 0;
        $this->format = '';
        $this->validationRules = [];
        $this->filterOptions = [];
        $this->relationship = '';
        $this->relatedModel = '';
        $this->relatedField = '';
    }

    public function saveField()
    {
        $this->validate($this->fieldRules);

        if (!$this->report) {
            session()->flash('error', 'Please save the report first before adding fields.');
            return;
        }

        $fieldData = [
            'report_id' => $this->report->id,
            'name' => $this->fieldName,
            'label' => $this->fieldLabel,
            'type' => $this->fieldType,
            'source_column' => $this->sourceColumn,
            'source_table' => $this->sourceTable,
            'description' => $this->fieldDescription,
            'is_sortable' => $this->isSortable,
            'is_searchable' => $this->isSearchable,
            'is_filterable' => $this->isFilterable,
            'is_visible' => $this->isVisible,
            'is_required' => $this->isRequired,
            'sort_order' => $this->sortOrder,
            'format' => $this->format,
            'validation_rules' => $this->validationRules,
            'filter_options' => $this->filterOptions,
            'relationship' => $this->relationship ?: null,
            'related_model' => $this->relatedModel ?: null,
            'related_field' => $this->relatedField ?: null,
        ];

        if ($this->editingField) {
            $this->editingField->update($fieldData);
            session()->flash('message', 'Field updated successfully!');
        } else {
            ReportField::create($fieldData);
            session()->flash('message', 'Field created successfully!');
        }

        $this->hideFieldForm();
        $this->dispatch('field-saved');
    }

    public function deleteField(ReportField $field)
    {
        $field->delete();
        session()->flash('message', 'Field deleted successfully!');
        $this->dispatch('field-deleted');
    }

    public function moveFieldUp(ReportField $field)
    {
        $previousField = $this->report->fields()
            ->where('sort_order', '<', $field->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($previousField) {
            $temp = $field->sort_order;
            $field->update(['sort_order' => $previousField->sort_order]);
            $previousField->update(['sort_order' => $temp]);
        }
    }

    public function moveFieldDown(ReportField $field)
    {
        $nextField = $this->report->fields()
            ->where('sort_order', '>', $field->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($nextField) {
            $temp = $field->sort_order;
            $field->update(['sort_order' => $nextField->sort_order]);
            $nextField->update(['sort_order' => $temp]);
        }
    }

    public function render()
    {
        $availableModels = ReportModel::active()->get();
        $fieldTypes = config('reporting-engine.reports.allowed_field_types', [
            'string', 'integer', 'decimal', 'boolean', 'date', 'datetime', 'text', 'json'
        ]);

        $fields = $this->report ? $this->report->fields()->orderBy('sort_order')->get() : collect();

        return view('reporting-engine::livewire.report-builder', [
            'availableModels' => $availableModels,
            'fieldTypes' => $fieldTypes,
            'fields' => $fields,
        ]);
    }
}