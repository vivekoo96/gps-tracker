<!-- Add this to your live.blade.php or device detail page -->
<div class="device-controls mt-4">
    <h4 class="font-semibold mb-2">Remote Controls</h4>
    <div class="flex gap-2">
        <button 
            onclick="sendDeviceCommand({{ $device->id }}, 'cut-off')" 
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
            🔴 Cut Off Engine
        </button>
        <button 
            onclick="sendDeviceCommand({{ $device->id }}, 'restore')" 
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
            ✅ Restore Engine
        </button>
    </div>
    <div id="command-status-{{ $device->id }}" class="mt-2 text-sm"></div>
</div>

<script>
function sendDeviceCommand(deviceId, action) {
    const statusDiv = document.getElementById(`command-status-${deviceId}`);
    statusDiv.innerHTML = '<span class="text-blue-600">⏳ Sending command...</span>';
    
    fetch(`/admin/devices/${deviceId}/${action}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusDiv.innerHTML = `<span class="text-green-600">✅ ${data.message}</span>`;
        } else {
            statusDiv.innerHTML = `<span class="text-red-600">❌ ${data.message}</span>`;
        }
    })
    .catch(error => {
        statusDiv.innerHTML = '<span class="text-red-600">❌ Error sending command</span>';
        console.error('Error:', error);
    });
}
</script>
