<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    protected $ticketService;
    
    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }
    
    /**
     * Display ticket list
     */
    public function index(Request $request)
    {
        $query = DB::table('tickets')
            ->leftJoin('devices', 'tickets.device_id', '=', 'devices.id')
            ->leftJoin('users', 'tickets.assigned_to', '=', 'users.id')
            ->select(
                'tickets.*',
                'devices.vehicle_no',
                'devices.name as device_name',
                'users.name as assigned_to_name'
            );
        
        // Apply filters
        if ($request->status) {
            $query->where('tickets.status', $request->status);
        }
        
        if ($request->severity) {
            $query->where('tickets.severity', $request->severity);
        }
        
        if ($request->alert_type) {
            $query->where('tickets.alert_type', $request->alert_type);
        }
        
        if ($request->assigned_to) {
            $query->where('tickets.assigned_to', $request->assigned_to);
        }
        
        $tickets = $query->orderBy('tickets.created_at', 'desc')->paginate(50);
        
        // Calculate stats for dashboard
        $stats = [
            'open' => DB::table('tickets')->where('status', 'OPEN')->count(),
            'in_progress' => DB::table('tickets')->where('status', 'IN_PROGRESS')->count(),
            'closed' => DB::table('tickets')->where('status', 'CLOSED')->count(),
        ];
        
        // Get filter options
        $users = DB::table('users')->whereIn('role', ['officer', 'executive'])->get();
        $alertTypes = DB::table('tickets')->select('alert_type')->distinct()->get();
        
        return view('admin.tickets.index', compact('tickets', 'users', 'alertTypes', 'stats'));
    }
    
    /**
     * Show ticket details
     */
    public function show($id)
    {
        $ticket = DB::table('tickets')
            ->leftJoin('devices', 'tickets.device_id', '=', 'devices.id')
            ->leftJoin('users as assigned_user', 'tickets.assigned_to', '=', 'assigned_user.id')
            ->leftJoin('device_alerts', 'tickets.alert_id', '=', 'device_alerts.id')
            ->select(
                'tickets.*',
                'devices.vehicle_no',
                'devices.name as device_name',
                'assigned_user.name as assigned_to_name',
                'assigned_user.email as assigned_to_email',
                'device_alerts.alert_time'
            )
            ->where('tickets.id', $id)
            ->first();
        
        if (!$ticket) {
            abort(404);
        }
        
        $actions = DB::table('ticket_actions')
            ->leftJoin('users', 'ticket_actions.user_id', '=', 'users.id')
            ->select('ticket_actions.*', 'users.name as user_name')
            ->where('ticket_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.tickets.show', compact('ticket', 'actions'));
    }
    
    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,assigned,in_progress,resolved,closed',
            'note' => 'nullable|string'
        ]);
        
        $success = $this->ticketService->updateStatus(
            $id,
            $request->status,
            auth()->id(),
            $request->note
        );
        
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket status updated successfully'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update ticket status'
        ], 400);
    }
    
    /**
     * Add comment to ticket
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string'
        ]);
        
        $this->ticketService->addComment($id, auth()->id(), $request->comment);
        
        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully'
        ]);
    }
    
    /**
     * Close ticket
     */
    public function close(Request $request, $id)
    {
        $request->validate([
            'resolution_note' => 'required|string'
        ]);
        
        $this->ticketService->closeTicket($id, auth()->id(), $request->resolution_note);
        
        return response()->json([
            'success' => true,
            'message' => 'Ticket closed successfully'
        ]);
    }
    
    /**
     * Analytics dashboard
     */
    public function analytics()
    {
        $stats = [
            'total' => DB::table('tickets')->count(),
            'open' => DB::table('tickets')->where('status', 'open')->count(),
            'in_progress' => DB::table('tickets')->where('status', 'in_progress')->count(),
            'resolved' => DB::table('tickets')->where('status', 'resolved')->count(),
            'closed' => DB::table('tickets')->where('status', 'closed')->count(),
            
            'by_severity' => DB::table('tickets')
                ->select('severity', DB::raw('count(*) as count'))
                ->groupBy('severity')
                ->get(),
            
            'by_type' => DB::table('tickets')
                ->select('alert_type', DB::raw('count(*) as count'))
                ->groupBy('alert_type')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            
            'recent_tickets' => DB::table('tickets')
                ->leftJoin('devices', 'tickets.device_id', '=', 'devices.id')
                ->select('tickets.*', 'devices.vehicle_no')
                ->orderBy('tickets.created_at', 'desc')
                ->limit(10)
                ->get()
        ];
        
        return view('admin.tickets.analytics', compact('stats'));
    }
}
