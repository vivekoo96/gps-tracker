<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['device', 'closedBy']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('alert_type') && $request->alert_type != '') {
            $query->where('alert_type', $request->alert_type);
        }

        $tickets = $query->latest('raised_at')->paginate(15);
        
        $stats = [
            'open' => Ticket::where('status', 'OPEN')->count(),
            'in_progress' => Ticket::where('status', 'IN_PROGRESS')->count(),
            'closed' => Ticket::where('status', 'CLOSED')->count(),
        ];

        return view('admin.tickets.index', compact('tickets', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Tickets are mainly raised by system or supervisors, but we can allow manual creation
        return view('admin.tickets.create', [
            'devices' => \App\Models\Device::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'alert_type' => 'required|string',
            'description' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'device_id' => $validated['device_id'],
            'alert_type' => $validated['alert_type'],
            'description' => $validated['description'],
            'status' => 'OPEN',
            'raised_at' => now(),
        ]);

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'CREATED',
            'notes' => 'Ticket raised manually by admin.'
        ]);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket raised successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['device', 'logs.user', 'closedBy']);
        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:OPEN,IN_PROGRESS,CLOSED',
            'notes' => 'required|string|min:5'
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];
        
        $ticket->status = $newStatus;
        
        if ($newStatus === 'CLOSED' && $oldStatus !== 'CLOSED') {
            $ticket->closed_at = now();
            $ticket->closed_by = Auth::id();
        } elseif ($newStatus !== 'CLOSED') {
            $ticket->closed_at = null;
            $ticket->closed_by = null;
        }

        $ticket->save();

        TicketLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => $oldStatus === $newStatus ? 'COMMENT' : 'STATUS_CHANGE',
            'notes' => $validated['notes'] . ($oldStatus !== $newStatus ? " (Status changed from $oldStatus to $newStatus)" : "")
        ]);

        return back()->with('success', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        // Usually tickets are kept for audit, but if deletion is allowed:
        $ticket->delete();
        return redirect()->route('admin.tickets.index')->with('success', 'Ticket deleted.');
    }
}
