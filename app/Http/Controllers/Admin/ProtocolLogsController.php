<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProtocolLog;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProtocolLogsController extends Controller
{
    public function index(Request $request)
    {
        $query = ProtocolLog::with('device')->latest();

        // Filter by device
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        // Filter by protocol
        if ($request->filled('protocol')) {
            $query->where('protocol_type', $request->protocol);
        }

        // Filter by success/failure
        if ($request->filled('status')) {
            $query->where('parse_success', $request->status === 'success');
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(50);
        $devices = Device::orderBy('name')->get();

        return view('admin.protocol-logs.index', compact('logs', 'devices'));
    }

    public function show($id)
    {
        $log = ProtocolLog::with('device')->findOrFail($id);
        return view('admin.protocol-logs.show', compact('log'));
    }

    public function destroy($id)
    {
        ProtocolLog::findOrFail($id)->delete();
        return redirect()->route('admin.protocol-logs.index')
            ->with('success', 'Protocol log deleted successfully');
    }

    public function clear(Request $request)
    {
        $days = $request->input('days', 7);
        
        ProtocolLog::where('created_at', '<', now()->subDays($days))->delete();
        
        return redirect()->route('admin.protocol-logs.index')
            ->with('success', "Logs older than {$days} days deleted successfully");
    }
}
