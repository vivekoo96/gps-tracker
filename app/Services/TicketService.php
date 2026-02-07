<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketService
{
    /**
     * Generate ticket from alert
     */
    public function generateTicket($alert, $priority = null)
    {
        try {
            // Determine priority based on severity
            if (!$priority) {
                $priority = match($alert->severity) {
                    'critical' => 1,
                    'high' => 2,
                    'medium' => 3,
                    'low' => 4,
                    default => 3
                };
            }
            
            // Determine assigned official
            $assignedTo = $this->determineAssignedOfficial($alert);
            
            // Create ticket
            $ticketId = DB::table('tickets')->insertGetId([
                'ticket_number' => $this->generateTicketNumber(),
                'alert_id' => $alert->id,
                'device_id' => $alert->device_id,
                'alert_type' => $alert->alert_type,
                'severity' => $alert->severity,
                'status' => 'open',
                'priority' => $priority,
                'assigned_to' => $assignedTo,
                'assigned_by' => 1, // System
                'assigned_at' => now(),
                'description' => $alert->description,
                'location_lat' => $alert->latitude ?? null,
                'location_lng' => $alert->longitude ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Log ticket creation
            DB::table('ticket_actions')->insert([
                'ticket_id' => $ticketId,
                'user_id' => 1, // System
                'action_type' => 'created',
                'action_description' => 'Ticket auto-generated from alert',
                'created_at' => now()
            ]);
            
            // Log assignment
            if ($assignedTo) {
                DB::table('ticket_actions')->insert([
                    'ticket_id' => $ticketId,
                    'user_id' => 1, // System
                    'action_type' => 'assigned',
                    'action_description' => "Ticket assigned to user ID: {$assignedTo}",
                    'new_value' => (string)$assignedTo,
                    'created_at' => now()
                ]);
            }
            
            Log::info("Ticket generated", ['ticket_id' => $ticketId, 'alert_id' => $alert->id]);
            
            return $ticketId;
            
        } catch (\Exception $e) {
            Log::error("Failed to generate ticket", ['error' => $e->getMessage(), 'alert_id' => $alert->id ?? null]);
            return null;
        }
    }
    
    /**
     * Generate unique ticket number
     */
    protected function generateTicketNumber()
    {
        $date = now()->format('Ymd');
        $count = DB::table('tickets')->whereDate('created_at', today())->count() + 1;
        return "TKT-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Determine which official should be assigned the ticket
     */
    protected function determineAssignedOfficial($alert)
    {
        // For device-related alerts, assign to zone officer
        if ($alert->device_id) {
            $device = DB::table('devices')->find($alert->device_id);
            
            if ($device && $device->zone_id) {
                // Find officer assigned to this zone
                $officer = DB::table('users')
                    ->join('user_zone_assignments', 'users.id', '=', 'user_zone_assignments.user_id')
                    ->where('user_zone_assignments.zone_id', $device->zone_id)
                    ->where('users.role', 'officer')
                    ->first();
                
                if ($officer) {
                    return $officer->id;
                }
            }
        }
        
        // For critical alerts, assign to executive
        if ($alert->severity === 'critical') {
            $executive = DB::table('users')->where('role', 'executive')->first();
            if ($executive) {
                return $executive->id;
            }
        }
        
        // Default: assign to first available officer
        $defaultOfficer = DB::table('users')->where('role', 'officer')->first();
        return $defaultOfficer->id ?? null;
    }
    
    /**
     * Update ticket status
     */
    public function updateStatus($ticketId, $newStatus, $userId, $note = null)
    {
        $ticket = DB::table('tickets')->find($ticketId);
        
        if (!$ticket) {
            return false;
        }
        
        $oldStatus = $ticket->status;
        
        DB::table('tickets')->where('id', $ticketId)->update([
            'status' => $newStatus,
            'updated_at' => now()
        ]);
        
        // Log status change
        DB::table('ticket_actions')->insert([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'action_type' => 'status_change',
            'action_description' => $note ?? "Status changed from {$oldStatus} to {$newStatus}",
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'created_at' => now()
        ]);
        
        return true;
    }
    
    /**
     * Close ticket
     */
    public function closeTicket($ticketId, $userId, $resolutionNote)
    {
        DB::table('tickets')->where('id', $ticketId)->update([
            'status' => 'closed',
            'closed_at' => now(),
            'updated_at' => now()
        ]);
        
        DB::table('ticket_actions')->insert([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'action_type' => 'closed',
            'action_description' => $resolutionNote,
            'created_at' => now()
        ]);
        
        return true;
    }
    
    /**
     * Add comment to ticket
     */
    public function addComment($ticketId, $userId, $comment)
    {
        DB::table('ticket_actions')->insert([
            'ticket_id' => $ticketId,
            'user_id' => $userId,
            'action_type' => 'comment',
            'action_description' => $comment,
            'created_at' => now()
        ]);
        
        return true;
    }
}
