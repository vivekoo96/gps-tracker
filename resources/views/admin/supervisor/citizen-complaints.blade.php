@extends('layouts.admin')

@section('title', 'Citizen Complaint - Collection Status')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Citizen Complaint - Collection Status</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Check garbage collection status for any location or society</p>
    </div>

    <!-- Search Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Search Location</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Society Name / Location / Address</label>
                <input type="text" id="searchInput" placeholder="Enter society name, location, or address..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
            </div>
            <div class="flex items-end">
                <button id="searchBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    🔍 Search
                </button>
            </div>
        </div>

        <!-- Search Results -->
        <div id="searchResults" class="hidden">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Results:</h3>
            <div id="resultsList" class="space-y-2 max-h-60 overflow-y-auto"></div>
        </div>

        <!-- OR Separator -->
        <div class="flex items-center my-6">
            <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
            <span class="px-4 text-gray-500 dark:text-gray-400">OR</span>
            <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
        </div>

        <!-- Filter by Zone/Ward -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Zone</label>
                <select id="zoneFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100">
                    <option value="">Select Zone</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ward</label>
                <select id="wardFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100">
                    <option value="">Select Ward</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button id="filterBtn" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    Filter Locations
                </button>
            </div>
        </div>
    </div>

    <!-- Collection Details Section -->
    <div id="detailsSection" class="hidden">
        <!-- Location Info Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Location Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Location Name</p>
                    <p id="locationName" class="text-lg font-semibold text-gray-900 dark:text-gray-100">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Society Name</p>
                    <p id="societyName" class="text-lg font-semibold text-gray-900 dark:text-gray-100">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Address</p>
                    <p id="locationAddress" class="text-gray-900 dark:text-gray-100">-</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Zone / Ward</p>
                    <p id="locationZoneWard" class="text-gray-900 dark:text-gray-100">-</p>
                </div>
            </div>
        </div>

        <!-- Today's Collection Status -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Today's Collection Status</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Scheduled Time</p>
                    <p id="scheduledTime" class="text-2xl font-bold text-blue-600 dark:text-blue-300">-</p>
                </div>
                <div class="text-center p-4 bg-green-50 dark:bg-green-900 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Actual Collection Time</p>
                    <p id="actualTime" class="text-2xl font-bold text-green-600 dark:text-green-300">-</p>
                </div>
                <div class="text-center p-4 bg-orange-50 dark:bg-orange-900 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Delay / Early</p>
                    <p id="delayTime" class="text-2xl font-bold text-orange-600 dark:text-orange-300">-</p>
                </div>
            </div>
            <div id="collectionStatus" class="mt-4 p-4 rounded-lg text-center"></div>
        </div>

        <!-- Last Collection Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Last Collection</h2>
            <div id="lastCollectionInfo" class="space-y-2"></div>
        </div>

        <!-- Collection History -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Collection History (Last 30 Days)</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vehicle No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vehicle Name</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No history available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchBtn').addEventListener('click', async function() {
    const searchTerm = document.getElementById('searchInput').value.trim();
    
    if (searchTerm.length < 3) {
        alert('Please enter at least 3 characters to search');
        return;
    }
    
    try {
        const response = await fetch('{{ route("admin.supervisor.search-location") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ search: searchTerm })
        });
        
        const data = await response.json();
        
        if (data.success && data.locations.length > 0) {
            displaySearchResults(data.locations);
        } else {
            alert('No locations found matching your search');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to search locations');
    }
});

// Filter by zone/ward
document.getElementById('filterBtn').addEventListener('click', async function() {
    const zoneId = document.getElementById('zoneFilter').value;
    const wardId = document.getElementById('wardFilter').value;
    
    if (!zoneId && !wardId) {
        alert('Please select at least one filter (Zone or Ward)');
        return;
    }
    
    try {
        const response = await fetch('{{ route("admin.supervisor.locations-by-area") }}?' + new URLSearchParams({
            zone_id: zoneId,
            ward_id: wardId
        }));
        
        const data = await response.json();
        
        if (data.success && data.locations.length > 0) {
            displaySearchResults(data.locations);
        } else {
            alert('No locations found for selected filters');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load locations');
    }
});

function displaySearchResults(locations) {
    const resultsList = document.getElementById('resultsList');
    resultsList.innerHTML = locations.map(loc => `
        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer" onclick="loadLocationDetails(${loc.id})">
            <p class="font-semibold text-gray-900 dark:text-gray-100">${loc.name}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">${loc.address || 'N/A'}</p>
            <p class="text-xs text-gray-500 dark:text-gray-500">${loc.zone_name || 'N/A'} / ${loc.ward_name || 'N/A'}</p>
        </div>
    `).join('');
    
    document.getElementById('searchResults').classList.remove('hidden');
}

async function loadLocationDetails(locationId) {
    try {
        const response = await fetch(`{{ url('/admin/supervisor/collection-details') }}/${locationId}`);
        const data = await response.json();
        
        if (data.success) {
            displayLocationDetails(data);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load location details');
    }
}

function displayLocationDetails(data) {
    // Location info
    document.getElementById('locationName').textContent = data.location.name;
    document.getElementById('societyName').textContent = data.location.society_name;
    document.getElementById('locationAddress').textContent = data.location.address;
    document.getElementById('locationZoneWard').textContent = `${data.location.zone} / ${data.location.ward}`;
    
    // Today's status
    document.getElementById('scheduledTime').textContent = data.today_status.scheduled_time || data.scheduled_time;
    document.getElementById('actualTime').textContent = data.today_status.actual_time ? new Date(data.today_status.actual_time).toLocaleTimeString() : 'Not Collected';
    
    const delay = data.today_status.delay_minutes;
    if (delay !== null) {
        const delayText = delay > 0 ? `+${delay} min (Late)` : delay < 0 ? `${Math.abs(delay)} min (Early)` : 'On Time';
        document.getElementById('delayTime').textContent = delayText;
    } else {
        document.getElementById('delayTime').textContent = '-';
    }
    
    // Collection status message
    const statusDiv = document.getElementById('collectionStatus');
    if (data.today_status.collected) {
        statusDiv.className = 'mt-4 p-4 rounded-lg text-center bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200';
        statusDiv.innerHTML = '✅ Garbage has been collected today';
    } else if (data.today_status.scheduled) {
        statusDiv.className = 'mt-4 p-4 rounded-lg text-center bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200';
        statusDiv.innerHTML = '⏳ Scheduled for today but not yet collected';
    } else {
        statusDiv.className = 'mt-4 p-4 rounded-lg text-center bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
        statusDiv.innerHTML = '❌ Not scheduled for today';
    }
    
    // Last collection
    const lastCollectionDiv = document.getElementById('lastCollectionInfo');
    if (data.last_collected) {
        lastCollectionDiv.innerHTML = `
            <p><strong>Date & Time:</strong> ${new Date(data.last_collected.date_time).toLocaleString()}</p>
            <p><strong>Vehicle:</strong> ${data.last_collected.vehicle_no} (${data.last_collected.vehicle_name})</p>
            <p><strong>Time Since:</strong> ${data.last_collected.hours_ago} hours ago</p>
        `;
    } else {
        lastCollectionDiv.innerHTML = '<p class="text-gray-500">No collection record found</p>';
    }
    
    // Collection history
    const historyBody = document.getElementById('historyTableBody');
    if (data.collection_history.length > 0) {
        historyBody.innerHTML = data.collection_history.map(record => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">${record.date}</td>
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">${record.time}</td>
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">${record.vehicle_no}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${record.vehicle_name}</td>
            </tr>
        `).join('');
    } else {
        historyBody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No history available</td></tr>';
    }
    
    // Show details section
    document.getElementById('detailsSection').classList.remove('hidden');
    
    // Scroll to details
    document.getElementById('detailsSection').scrollIntoView({ behavior: 'smooth' });
}

// Allow Enter key to search
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('searchBtn').click();
    }
});
</script>
@endsection
