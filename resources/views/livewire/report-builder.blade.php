<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                @if($isEditing)
                    Edit Report: {{ $report->name }}
                @else
                    Create New Report
                @endif
            </h1>
            <p class="mt-2 text-sm text-gray-700">
                @if($isEditing)
                    Modify report settings and fields
                @else
                    Build your custom report with dynamic fields
                @endif
            </p>
        </div>
        @if($isEditing)
            <div class="mt-4 sm:mt-0">
                <a 
                    href="/reporting/reports/{{ $report->slug }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview Report
                </a>
            </div>
        @endif
    </div>

    <!-- Report Settings -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Report Settings</h2>
        </div>
        <div class="px-6 py-4">
            <form wire:submit="saveReport" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input 
                            type="text" 
                            id="name"
                            wire:model="name" 
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter report name">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                            <select 
                                id="type"
                                wire:model="type" 
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="table">Table</option>
                                <option value="chart">Chart</option>
                                <option value="dashboard">Dashboard</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select 
                                id="status"
                                wire:model="status" 
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea 
                        id="description"
                        wire:model="description" 
                        rows="3"
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter report description"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="isPublic" id="is_public" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_public" class="ml-2 block text-sm text-gray-900">Public Report</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" wire:model="isCached" id="is_cached" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_cached" class="ml-2 block text-sm text-gray-900">Enable Caching</label>
                    </div>
                    
                    <div>
                        <label for="cache_ttl" class="block text-sm font-medium text-gray-700">Cache TTL (seconds)</label>
                        <input 
                            type="number" 
                            id="cache_ttl"
                            wire:model="cacheTtl" 
                            min="60"
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        {{ $isEditing ? 'Update Report' : 'Save Report' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Fields -->
    @if($isEditing)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium text-gray-900">Report Fields</h2>
                    <button 
                        wire:click="showFieldForm"
                        type="button" 
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Field
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-4">
                @if($fields->count() > 0)
                    <div class="space-y-4">
                        @foreach($fields as $field)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $field->label }}</h4>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $field->type }}
                                            </span>
                                            @if($field->is_required)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Required
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500">
                                            {{ $field->source_table }}.{{ $field->source_column }}
                                            @if($field->description)
                                                - {{ $field->description }}
                                            @endif
                                        </div>
                                        <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                            @if($field->is_sortable)
                                                <span class="flex items-center">
                                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
                                                    </svg>
                                                    Sortable
                                                </span>
                                            @endif
                                            @if($field->is_searchable)
                                                <span class="flex items-center">
                                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                                    </svg>
                                                    Searchable
                                                </span>
                                            @endif
                                            @if($field->is_filterable)
                                                <span class="flex items-center">
                                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                                                    </svg>
                                                    Filterable
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <button 
                                            wire:click="moveFieldUp({{ $field->id }})"
                                            class="text-gray-400 hover:text-gray-600">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <button 
                                            wire:click="moveFieldDown({{ $field->id }})"
                                            class="text-gray-400 hover:text-gray-600">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <button 
                                            wire:click="showFieldForm({{ $field->id }})"
                                            class="text-blue-600 hover:text-blue-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button 
                                            wire:click="deleteField({{ $field->id }})"
                                            wire:confirm="Are you sure you want to delete this field?"
                                            class="text-red-600 hover:text-red-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No fields configured</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding your first field.</p>
                        <div class="mt-6">
                            <button 
                                wire:click="showFieldForm"
                                type="button" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Field
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Field Form Modal -->
    @if($showFieldForm)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ $editingField ? 'Edit Field' : 'Add New Field' }}
                        </h3>
                        <button wire:click="hideFieldForm" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form wire:submit="saveField" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="field_name" class="block text-sm font-medium text-gray-700">Field Name</label>
                                <input 
                                    type="text" 
                                    id="field_name"
                                    wire:model="fieldName" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g., user_name">
                                @error('fieldName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label for="field_label" class="block text-sm font-medium text-gray-700">Display Label</label>
                                <input 
                                    type="text" 
                                    id="field_label"
                                    wire:model="fieldLabel" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g., User Name">
                                @error('fieldLabel') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="field_type" class="block text-sm font-medium text-gray-700">Field Type</label>
                                <select 
                                    id="field_type"
                                    wire:model="fieldType" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @foreach($fieldTypes as $type)
                                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="source_table" class="block text-sm font-medium text-gray-700">Source Table</label>
                                <input 
                                    type="text" 
                                    id="source_table"
                                    wire:model="sourceTable" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g., users">
                                @error('sourceTable') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label for="source_column" class="block text-sm font-medium text-gray-700">Source Column</label>
                                <input 
                                    type="text" 
                                    id="source_column"
                                    wire:model="sourceColumn" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g., name">
                                @error('sourceColumn') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        
                        <div>
                            <label for="field_description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea 
                                id="field_description"
                                wire:model="fieldDescription" 
                                rows="2"
                                class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Optional field description"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="isSortable" id="is_sortable" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_sortable" class="ml-2 block text-sm text-gray-900">Sortable</label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="isSearchable" id="is_searchable" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_searchable" class="ml-2 block text-sm text-gray-900">Searchable</label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="isFilterable" id="is_filterable" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_filterable" class="ml-2 block text-sm text-gray-900">Filterable</label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="isVisible" id="is_visible" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_visible" class="ml-2 block text-sm text-gray-900">Visible</label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="isRequired" id="is_required" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_required" class="ml-2 block text-sm text-gray-900">Required</label>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button 
                                type="button" 
                                wire:click="hideFieldForm"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ $editingField ? 'Update Field' : 'Add Field' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>