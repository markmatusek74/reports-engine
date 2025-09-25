<div class="bg-white shadow-sm rounded-lg">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">{{ $report->name }}</h3>
                @if($report->description)
                    <p class="mt-1 text-sm text-gray-600">{{ $report->description }}</p>
                @endif
            </div>
            <div class="flex items-center space-x-3">
                <button 
                    wire:click="clearAllFilters" 
                    @if(empty($filters) && empty($search)) disabled @endif
                    class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    Clear Filters
                </button>
                <div class="relative">
                    <button class="px-3 py-1 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input 
                    type="text" 
                    id="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search reports..." 
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <!-- Dynamic Filters -->
            @foreach($filterableFields as $field)
                @if($field->type === 'boolean')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field->label }}</label>
                        <select 
                            wire:change="applyFilter('{{ $field->name }}', $event.target.value)"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">All</option>
                            <option value="1" @if(($filters[$field->name] ?? '') === '1') selected @endif>Yes</option>
                            <option value="0" @if(($filters[$field->name] ?? '') === '0') selected @endif>No</option>
                        </select>
                    </div>
                @elseif(in_array($field->type, ['string', 'text']))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field->label }}</label>
                        <input 
                            type="text"
                            wire:model.live.debounce.300ms="filters.{{ $field->name }}"
                            placeholder="Filter by {{ strtolower($field->label) }}..."
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($fields as $field)
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            @if($field->isSortable())
                                <button 
                                    wire:click="toggleSort('{{ $field->name }}')"
                                    class="flex items-center space-x-1 hover:text-gray-700">
                                    <span>{{ $field->label }}</span>
                                    @if(isset($sorting['field']) && $sorting['field'] === $field->name)
                                        @if($sorting['direction'] === 'asc')
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    @endif
                                </button>
                            @else
                                {{ $field->label }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($reportData['data'] as $row)
                    <tr class="hover:bg-gray-50">
                        @foreach($fields as $field)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($field->type === 'boolean')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($row[$field->name]) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                        {{ $row[$field->name] ? 'Yes' : 'No' }}
                                    </span>
                                @elseif($field->type === 'date')
                                    {{ $row[$field->name] ? \Carbon\Carbon::parse($row[$field->name])->format('M d, Y') : '-' }}
                                @elseif($field->type === 'datetime')
                                    {{ $row[$field->name] ? \Carbon\Carbon::parse($row[$field->name])->format('M d, Y H:i') : '-' }}
                                @else
                                    {{ $row[$field->name] ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $fields->count() }}" class="px-6 py-12 text-center text-sm text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-medium text-gray-900 mb-1">No data found</p>
                                <p class="text-gray-500">Try adjusting your search or filter criteria.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(isset($reportData['meta']) && $reportData['meta']['total'] > 0)
        <div class="px-6 py-4 border-t border-gray-200 bg-white">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ (($reportData['meta']['current_page'] - 1) * $reportData['meta']['per_page']) + 1 }} to 
                    {{ min($reportData['meta']['current_page'] * $reportData['meta']['per_page'], $reportData['meta']['total']) }} of 
                    {{ $reportData['meta']['total'] }} results
                </div>
                
                @if($reportData['meta']['last_page'] > 1)
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        @if($reportData['meta']['current_page'] > 1)
                            <button wire:click="previousPage" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif

                        @for($i = max(1, $reportData['meta']['current_page'] - 2); $i <= min($reportData['meta']['last_page'], $reportData['meta']['current_page'] + 2); $i++)
                            <button 
                                wire:click="gotoPage({{ $i }})"
                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium
                                    @if($i === $reportData['meta']['current_page'])
                                        border-blue-500 bg-blue-50 text-blue-600
                                    @else
                                        border-gray-300 bg-white text-gray-700 hover:bg-gray-50
                                    @endif">
                                {{ $i }}
                            </button>
                        @endfor

                        @if($reportData['meta']['current_page'] < $reportData['meta']['last_page'])
                            <button wire:click="nextPage" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    @endif
</div>