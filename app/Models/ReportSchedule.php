<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReportSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'frequency',
        'time',
        'days_of_week',
        'day_of_month',
        'last_run_at',
        'next_run_at',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function template()
    {
        return $this->belongsTo(ReportTemplate::class, 'template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', true)
                    ->where('next_run_at', '<=', now());
    }

    // Methods
    public function calculateNextRun()
    {
        $now = Carbon::now();
        $time = Carbon::parse($this->time);

        switch ($this->frequency) {
            case 'daily':
                $next = $now->copy()->setTime($time->hour, $time->minute);
                if ($next->isPast()) {
                    $next->addDay();
                }
                break;

            case 'weekly':
                $next = $now->copy()->setTime($time->hour, $time->minute);
                $daysOfWeek = $this->days_of_week ?? [1, 2, 3, 4, 5]; // Default to weekdays
                
                // Find next occurrence
                for ($i = 0; $i < 7; $i++) {
                    if (in_array($next->dayOfWeek, $daysOfWeek) && $next->isFuture()) {
                        break;
                    }
                    $next->addDay();
                }
                break;

            case 'monthly':
                $dayOfMonth = $this->day_of_month ?? 1;
                $next = $now->copy()->day($dayOfMonth)->setTime($time->hour, $time->minute);
                if ($next->isPast()) {
                    $next->addMonth();
                }
                break;

            default:
                return null;
        }

        $this->next_run_at = $next;
        $this->save();

        return $next;
    }
}
