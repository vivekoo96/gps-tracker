<?php

namespace App\Services;

use App\Models\User;
use App\Models\DriverScore;
use App\Models\DriverViolation;
use App\Models\GpsData;
use Carbon\Carbon;

class DriverScoringService
{
    /**
     * Calculate driver score for a period
     */
    public function calculateScore(User $driver, $period = 'daily', $date = null)
    {
        $date = $date ?? now();
        [$periodStart, $periodEnd] = $this->getPeriodDates($period, $date);

        // Get violations for period
        $violations = DriverViolation::where('driver_id', $driver->id)
            ->whereBetween('occurred_at', [$periodStart, $periodEnd])
            ->get();

        // Get trip statistics
        $trips = GpsData::where('device_id', function($query) use ($driver) {
                $query->select('id')->from('devices')->where('user_id', $driver->id);
            })
            ->whereBetween('timestamp', [$periodStart, $periodEnd])
            ->get();

        // Calculate component scores
        $safetyScore = $this->calculateSafetyScore($violations);
        $efficiencyScore = $this->calculateEfficiencyScore($trips, $violations);
        $complianceScore = $this->calculateComplianceScore($violations);

        // Calculate overall score (weighted average)
        $overallScore = ($safetyScore * 0.4) + ($efficiencyScore * 0.3) + ($complianceScore * 0.3);

        // Count violations by type
        $violationCounts = $this->getViolationCounts($violations);

        // Create or update score record
        $score = DriverScore::updateOrCreate(
            [
                'vendor_id' => $driver->vendor_id,
                'driver_id' => $driver->id,
                'period' => $period,
                'period_start' => $periodStart,
            ],
            [
                'period_end' => $periodEnd,
                'score' => round($overallScore, 2),
                'safety_score' => round($safetyScore, 2),
                'efficiency_score' => round($efficiencyScore, 2),
                'compliance_score' => round($complianceScore, 2),
                'total_trips' => $this->countTrips($trips),
                'total_distance' => $this->calculateDistance($trips),
                'total_violations' => $violations->count(),
                'harsh_braking_count' => $violationCounts['harsh_braking'],
                'harsh_acceleration_count' => $violationCounts['harsh_acceleration'],
                'harsh_cornering_count' => $violationCounts['harsh_cornering'],
                'speeding_count' => $violationCounts['speeding'],
                'idling_count' => $violationCounts['excessive_idling'],
                'performance_level' => $this->getPerformanceLevel($overallScore),
                'calculated_at' => now(),
            ]
        );

        // Update rankings
        $this->updateRankings($driver->vendor_id, $period);

        return $score;
    }

    /**
     * Calculate safety score (40% of total)
     */
    private function calculateSafetyScore($violations)
    {
        $score = 100;

        foreach ($violations as $violation) {
            $penalty = $this->getViolationPenalty($violation->violation_type, $violation->severity);
            $score -= $penalty;
        }

        return max(0, $score);
    }

    /**
     * Calculate efficiency score (30% of total)
     */
    private function calculateEfficiencyScore($trips, $violations)
    {
        $score = 100;

        // Penalize for excessive idling
        $idlingViolations = $violations->where('violation_type', 'excessive_idling');
        $score -= $idlingViolations->count() * 5;

        // TODO: Add fuel efficiency rating when fuel data is available
        // TODO: Add route optimization adherence

        return max(0, $score);
    }

    /**
     * Calculate compliance score (30% of total)
     */
    private function calculateComplianceScore($violations)
    {
        $score = 100;

        // Penalize for compliance violations
        $seatbeltViolations = $violations->where('violation_type', 'seatbelt')->count();
        $phoneViolations = $violations->where('violation_type', 'phone_usage')->count();

        $score -= $seatbeltViolations * 10;
        $score -= $phoneViolations * 15;

        return max(0, $score);
    }

    /**
     * Get penalty points for violation
     */
    public function getViolationPenalty($violationType, $severity)
    {
        $basePenalties = [
            'harsh_braking' => ['low' => 2, 'medium' => 5, 'high' => 10, 'critical' => 15],
            'harsh_acceleration' => ['low' => 2, 'medium' => 5, 'high' => 10, 'critical' => 15],
            'harsh_cornering' => ['low' => 2, 'medium' => 5, 'high' => 10, 'critical' => 15],
            'speeding' => ['low' => 3, 'medium' => 7, 'high' => 15, 'critical' => 25],
            'excessive_idling' => ['low' => 3, 'medium' => 5, 'high' => 8, 'critical' => 10],
            'seatbelt' => ['low' => 10, 'medium' => 10, 'high' => 10, 'critical' => 10],
            'phone_usage' => ['low' => 15, 'medium' => 15, 'high' => 15, 'critical' => 15],
        ];

        return $basePenalties[$violationType][$severity] ?? 5;
    }

    /**
     * Get violation counts by type
     */
    private function getViolationCounts($violations)
    {
        return [
            'harsh_braking' => $violations->where('violation_type', 'harsh_braking')->count(),
            'harsh_acceleration' => $violations->where('violation_type', 'harsh_acceleration')->count(),
            'harsh_cornering' => $violations->where('violation_type', 'harsh_cornering')->count(),
            'speeding' => $violations->where('violation_type', 'speeding')->count(),
            'excessive_idling' => $violations->where('violation_type', 'excessive_idling')->count(),
        ];
    }

    /**
     * Get performance level based on score
     */
    private function getPerformanceLevel($score)
    {
        if ($score >= 90) return 'excellent';
        if ($score >= 75) return 'good';
        if ($score >= 60) return 'fair';
        return 'poor';
    }

    /**
     * Update driver rankings for vendor
     */
    public function updateRankings($vendorId, $period)
    {
        $scores = DriverScore::where('vendor_id', $vendorId)
            ->where('period', $period)
            ->orderBy('score', 'desc')
            ->get();

        $rank = 1;
        foreach ($scores as $score) {
            $score->update(['rank' => $rank++]);
        }
    }

    /**
     * Get period date range
     */
    private function getPeriodDates($period, $date)
    {
        return match($period) {
            'daily' => [
                Carbon::parse($date)->startOfDay(),
                Carbon::parse($date)->endOfDay(),
            ],
            'weekly' => [
                Carbon::parse($date)->startOfWeek(),
                Carbon::parse($date)->endOfWeek(),
            ],
            'monthly' => [
                Carbon::parse($date)->startOfMonth(),
                Carbon::parse($date)->endOfMonth(),
            ],
            'all_time' => [
                Carbon::parse('2020-01-01'),
                now(),
            ],
            default => [
                Carbon::parse($date)->startOfDay(),
                Carbon::parse($date)->endOfDay(),
            ],
        };
    }

    /**
     * Count trips from GPS data
     */
    private function countTrips($trips)
    {
        // Count ignition on events as trip starts
        return $trips->where('ignition', true)->count();
    }

    /**
     * Calculate total distance from GPS data
     */
    private function calculateDistance($trips)
    {
        // Sum odometer changes or calculate from coordinates
        return $trips->sum('odometer') ?? 0;
    }

    /**
     * Calculate trend for driver
     */
    public function calculateTrend(User $driver, $period = 'daily')
    {
        $current = DriverScore::where('driver_id', $driver->id)
            ->where('period', $period)
            ->latest('period_start')
            ->first();

        $previous = DriverScore::where('driver_id', $driver->id)
            ->where('period', $period)
            ->where('period_start', '<', $current->period_start ?? now())
            ->latest('period_start')
            ->first();

        if (!$current || !$previous) {
            return 'stable';
        }

        $diff = $current->score - $previous->score;
        
        if ($diff > 5) return 'improving';
        if ($diff < -5) return 'declining';
        return 'stable';
    }
}
