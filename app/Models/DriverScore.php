<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverScore extends Model
{
    protected $fillable = [
        'vendor_id',
        'driver_id',
        'period',
        'period_start',
        'period_end',
        'score',
        'safety_score',
        'efficiency_score',
        'compliance_score',
        'total_trips',
        'total_distance',
        'total_violations',
        'harsh_braking_count',
        'harsh_acceleration_count',
        'harsh_cornering_count',
        'speeding_count',
        'idling_count',
        'rank',
        'performance_level',
        'calculated_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'score' => 'decimal:2',
        'safety_score' => 'decimal:2',
        'efficiency_score' => 'decimal:2',
        'compliance_score' => 'decimal:2',
        'total_distance' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    // Scopes
    public function scopeForPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeTopDrivers($query, $limit = 10)
    {
        return $query->orderBy('score', 'desc')->limit($limit);
    }

    public function scopeBottomDrivers($query, $limit = 10)
    {
        return $query->orderBy('score', 'asc')->limit($limit);
    }

    // Accessors
    public function getScoreGradeAttribute()
    {
        return $this->getGrade();
    }

    public function getTrendAttribute()
    {
        // Compare with previous period
        $previousScore = static::where('driver_id', $this->driver_id)
            ->where('period', $this->period)
            ->where('period_start', '<', $this->period_start)
            ->orderBy('period_start', 'desc')
            ->first();

        if (!$previousScore) {
            return 'stable';
        }

        $diff = $this->score - $previousScore->score;
        
        if ($diff > 5) return 'improving';
        if ($diff < -5) return 'declining';
        return 'stable';
    }

    public function getPerformanceLevelAttribute()
    {
        if ($this->score >= 90) return 'excellent';
        if ($this->score >= 75) return 'good';
        if ($this->score >= 60) return 'fair';
        return 'poor';
    }

    // Methods
    public function getScoreColor()
    {
        return match($this->performance_level) {
            'excellent' => 'green',
            'good' => 'blue',
            'fair' => 'yellow',
            'poor' => 'red',
            default => 'gray',
        };
    }

    public function getGrade()
    {
        if ($this->score >= 95) return 'A+';
        if ($this->score >= 90) return 'A';
        if ($this->score >= 85) return 'A-';
        if ($this->score >= 80) return 'B+';
        if ($this->score >= 75) return 'B';
        if ($this->score >= 70) return 'B-';
        if ($this->score >= 65) return 'C+';
        if ($this->score >= 60) return 'C';
        if ($this->score >= 55) return 'C-';
        if ($this->score >= 50) return 'D';
        return 'F';
    }

    public function getTrend()
    {
        return $this->trend;
    }
}
