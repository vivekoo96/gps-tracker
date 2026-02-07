@extends('layouts.admin')

@section('title', 'Vehicle Assignments')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Vehicle Assignments</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Assign vehicles to zones, wards, and transfer stations</p>
    </div>

    <!-- Vehicles Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vehicle No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vehicle Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Zone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ward</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Transfer Station</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($devices as $device)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $device->vehicle_no }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $device->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $device->zone->name ?? 'Not Assigned' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $device->ward->name ?? 'Not Assigned' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                            @if($device->transfer_station_id)
                                {{ DB::table('transfer_stations')->where('id', $device->transfer_station_id)->value('name') }}
                            @else
                                Not Assigned
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <button onclick="editAssignment({{ $device->id }})" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Edit</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div id="assignmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-11/12 max-w-md">
        <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit Vehicle Assignment</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="assignmentForm" class="p-6 space-y-4">
            <input type="hidden" id="vehicleId">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Zone</label>
                <select id="zoneSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100">
                    <option value="">Not Assigned</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ward</label>
                <select id="wardSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100">
                    <option value="">Not Assigned</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Transfer Station</label>
                <select id="transferStationSelect" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-gray-100">
                    <option value="">Not Assigned</option>
                    @foreach($transferStations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Save Assignment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editAssignment(vehicleId) {
    document.getElementById('vehicleId').value = vehicleId;
    
    // Get current assignments from table row
    const row = event.target.closest('tr');
    const cells = row.querySelectorAll('td');
    
    // Reset selects
    document.getElementById('zoneSelect').value = '';
    document.getElementById('wardSelect').value = '';
    document.getElementById('transferStationSelect').value = '';
    
    document.getElementById('assignmentModal').classList.remove('hidden');
    document.getElementById('assignmentModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('assignmentModal').classList.add('hidden');
    document.getElementById('assignmentModal').classList.remove('flex');
}

document.getElementById('assignmentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const vehicleId = document.getElementById('vehicleId').value;
    const zoneId = document.getElementById('zoneSelect').value || null;
    const wardId = document.getElementById('wardSelect').value || null;
    const transferStationId = document.getElementById('transferStationSelect').value || null;

    try {
        const response = await fetch(`{{ url('/admin/users/vehicle-assignment') }}/${vehicleId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                zone_id: zoneId,
                ward_id: wardId,
                transfer_station_id: transferStationId
            })
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update assignment'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update assignment');
    }
});
</script>
@endsection
