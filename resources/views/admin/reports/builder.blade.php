<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Report Builder') }}
        </h2>
    </x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Report Builder</h1>
            <a href="{{ route('admin.reports.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                ← Back to Reports
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
            <div class="p-6">
                <form id="reportForm">
                    @csrf
                    
                    <!-- Step 1: Report Type -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">1. Select Report Type</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($reportTypes as $key => $label)
                            <label class="report-type-option relative flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer transition {{ $selectedType == $key ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-blue-300' }}" data-type="{{ $key }}">
                                <input type="radio" name="type" value="{{ $key }}" class="sr-only report-type-radio" {{ $selectedType == $key ? 'checked' : '' }}>
                                <svg class="w-8 h-8 mb-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 text-center">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Step 2: Report Details -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">2. Report Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Report Name</label>
                                <input type="text" name="name" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g., Weekly Fleet Summary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description (Optional)</label>
                                <input type="text" name="description" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Brief description">
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Filters -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">3. Configure Filters</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                                <input type="date" name="date_from" required value="{{ now()->subDays(7)->format('Y-m-d') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                                <input type="date" name="date_to" required value="{{ now()->format('Y-m-d') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Devices (Optional - Leave empty for all)</label>
                                <select name="devices[]" multiple class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500" size="5">
                                    @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} - {{ $device->vehicle_no }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hold Ctrl/Cmd to select multiple devices</p>
                            </div>
                        </div>

                        <!-- Additional Filters (Type-specific) -->
                        <div id="additionalFilters" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Speed Violation Filters -->
                            <div class="speed-filter hidden">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Speed Limit (km/h)</label>
                                <input type="number" name="filters[speed_limit]" value="80" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Idle Time Filters -->
                            <div class="idle-filter hidden">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minimum Idle Duration (minutes)</label>
                                <input type="number" name="filters[min_idle_minutes]" value="5" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <!-- Fuel Filters -->
                            <div class="fuel-filter hidden">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fuel Price (per liter)</label>
                                <input type="number" step="0.01" name="filters[fuel_price]" value="1.50" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Actions -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex space-x-4">
                            <button type="button" onclick="generateReport()" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Preview Report
                            </button>
                            <button type="button" onclick="saveTemplate()" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                Save as Template
                            </button>
                        </div>
                        <div class="flex space-x-2">
                            <button type="button" onclick="exportReport('csv')" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">Export CSV</button>
                            <button type="button" onclick="exportReport('excel')" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">Export Excel</button>
                            <button type="button" onclick="exportReport('pdf')" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">Export PDF</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Preview -->
        <div id="reportPreview" class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Report Preview</h2>
                <span id="recordCount" class="text-sm text-gray-500 dark:text-gray-400"></span>
            </div>
            <div class="p-6">
                <div id="reportTable" class="overflow-x-auto"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateReportType(type) {
    // Update visual selection
    document.querySelectorAll('.report-type-option').forEach(el => {
        const radioInput = el.querySelector('.report-type-radio');
        if (radioInput && radioInput.value === type) {
            el.classList.remove('border-gray-300', 'dark:border-gray-600');
            el.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
            radioInput.checked = true;
        } else {
            el.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
            el.classList.add('border-gray-300', 'dark:border-gray-600');
            if (radioInput) radioInput.checked = false;
        }
    });
    
    // Hide all type-specific filters
    document.querySelectorAll('#additionalFilters > div').forEach(el => el.classList.add('hidden'));
    
    // Show relevant filters
    if (type === 'speed_violation') {
        document.querySelector('.speed-filter').classList.remove('hidden');
    } else if (type === 'idle_time') {
        document.querySelector('.idle-filter').classList.remove('hidden');
    } else if (type === 'fuel_consumption') {
        document.querySelector('.fuel-filter').classList.remove('hidden');
    }
}

function generateReport() {
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    
    // Show loading
    document.getElementById('reportPreview').classList.remove('hidden');
    document.getElementById('reportTable').innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="mt-2 text-gray-600">Generating report...</p></div>';
    
    fetch('{{ route("admin.reports.generate") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayReport(data);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating the report');
    });
}

function displayReport(data) {
    document.getElementById('recordCount').textContent = `${data.record_count} records found`;
    
    if (data.record_count === 0) {
        document.getElementById('reportTable').innerHTML = '<p class="text-center text-gray-500 py-8">No data found for the selected criteria</p>';
        return;
    }
    
    // Build table
    let html = '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-700"><tr>';
    
    // Headers
    Object.values(data.columns).forEach(column => {
        html += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">${column}</th>`;
    });
    html += '</tr></thead><tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">';
    
    // Data rows
    data.data.forEach(row => {
        html += '<tr>';
        Object.keys(data.columns).forEach(key => {
            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${row[key] || '-'}</td>`;
        });
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    document.getElementById('reportTable').innerHTML = html;
}

function saveTemplate() {
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    
    // Convert to regular form submission
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = '{{ route("admin.reports.templates.store") }}';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    tempForm.appendChild(csrfInput);
    
    // Add form data
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        tempForm.appendChild(input);
    }
    
    document.body.appendChild(tempForm);
    tempForm.submit();
}

let currentReportData = null;
let currentTemplate = null;

function exportReport(format) {
    if (!currentReportData) {
        alert('Please generate a report first before exporting');
        return;
    }
    
    // Create a temporary template object
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    
    const template = {
        id: 0,
        name: formData.get('name') || 'Untitled Report',
        type: formData.get('type'),
        filters: {}
    };
    
    // Show loading
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Exporting...';
    
    // Create export request
    const exportData = new FormData();
    exportData.append('_token', '{{ csrf_token() }}');
    exportData.append('format', format);
    exportData.append('template_name', template.name);
    exportData.append('template_type', template.type);
    exportData.append('report_data', JSON.stringify(currentReportData));
    
    // Copy form data
    for (let [key, value] of formData.entries()) {
        exportData.append(key, value);
    }
    
    fetch('{{ route("admin.reports.generate") }}', {
        method: 'POST',
        body: exportData
    })
    .then(response => response.blob())
    .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${template.name.replace(/\s+/g, '_')}_${new Date().toISOString().slice(0,10)}.${format}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        button.disabled = false;
        button.textContent = originalText;
    })
    .catch(error => {
        console.error('Export error:', error);
        alert('Export failed. Please try again.');
        button.disabled = false;
        button.textContent = originalText;
    });
}

// Update displayReport to store data
function displayReportOriginal(data) {
    currentReportData = data;
    document.getElementById('recordCount').textContent = `${data.record_count} records found`;
    
    if (data.record_count === 0) {
        document.getElementById('reportTable').innerHTML = '<p class="text-center text-gray-500 py-8">No data found for the selected criteria</p>';
        return;
    }
    
    // Build table
    let html = '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-700"><tr>';
    
    // Headers
    Object.values(data.columns).forEach(column => {
        html += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">${column}</th>`;
    });
    html += '</tr></thead><tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">';
    
    // Data rows
    data.data.forEach(row => {
        html += '<tr>';
        Object.keys(data.columns).forEach(key => {
            html += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${row[key] || '-'}</td>`;
        });
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    document.getElementById('reportTable').innerHTML = html;
}


// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set up click handlers for report type selection
    document.querySelectorAll('.report-type-option').forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('.report-type-radio');
            if (radio) {
                updateReportType(radio.value);
            }
        });
    });
    
    // Initialize with selected type
    updateReportType('{{ $selectedType }}');
});
</script>
@endpush
</x-app-layout>
