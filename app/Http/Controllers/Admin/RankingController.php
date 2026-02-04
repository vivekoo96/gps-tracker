<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ward;
use App\Models\Circle;
use App\Models\Zone;
use App\Models\Device;
use App\Models\Position;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RankingController extends Controller
{
    public function index()
    {
        $wardRanking = $this->calculateWardRanking();
        $circleRanking = $this->calculateCircleRanking();
        
        return view('admin.ranking.index', compact('wardRanking', 'circleRanking'));
    }

    private function calculateWardRanking()
    {
        // Simple ranking based on vehicle activity (position counts)
        return Ward::withCount(['devices', 'transferStations'])
            ->get()
            ->map(function ($ward) {
                $deviceIds = $ward->devices->pluck('id');
                
                // Get activity count for today
                $activityCount = Position::whereIn('device_id', $deviceIds)
                    ->whereDate('fix_time', Carbon::today())
                    ->count();

                // Score = Activity / Device Count (average activity per device)
                $score = $ward->devices_count > 0 ? round($activityCount / $ward->devices_count, 2) : 0;

                return [
                    'id' => $ward->id,
                    'name' => $ward->name,
                    'circle' => $ward->circle->name ?? 'N/A',
                    'vehicle_count' => $ward->devices_count,
                    'activity_score' => $score,
                    'rank_level' => $this->getRankLabel($score)
                ];
            })
            ->sortByDesc('activity_score')
            ->values();
    }

    private function calculateCircleRanking()
    {
        return Circle::withCount(['wards', 'devices'])
            ->get()
            ->map(function ($circle) {
                $deviceIds = $circle->devices->pluck('id');
                
                $activityCount = Position::whereIn('device_id', $deviceIds)
                    ->whereDate('fix_time', Carbon::today())
                    ->count();

                $score = $circle->devices_count > 0 ? round($activityCount / $circle->devices_count, 2) : 0;

                return [
                    'id' => $circle->id,
                    'name' => $circle->name,
                    'zone' => $circle->zone->name ?? 'N/A',
                    'ward_count' => $circle->wards_count,
                    'activity_score' => $score,
                    'rank_level' => $this->getRankLabel($score)
                ];
            })
            ->sortByDesc('activity_score')
            ->values();
    }

    private function getRankLabel($score)
    {
        if ($score > 100) return 'EXCELLENT';
        if ($score > 50) return 'GOOD';
        if ($score > 10) return 'AVERAGE';
        return 'POOR';
    }
}
