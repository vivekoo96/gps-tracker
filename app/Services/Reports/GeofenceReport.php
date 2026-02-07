<?php

namespace App\Services\Reports;

use App\Models\GeofenceEvent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeofenceReport extends BaseReport
{
    public function getType(): string
    {
        return 'geofence';
    }

    public function getColumns(): array
    {
        return [
            'geofence_name' => 'Geofence',
            'device_name' => 'Device/Vehicle',
            'entry_time' => 'Entry Time',
            'exit_time' => 'Exit Time',
            'dwell_duration' => 'Dwell Duration',
        ];
    }

    public function generate(): Collection
    {
        $query = DB::table('geofence_events')
            ->join('geofences', 'geofence_events.geofence_id', '=', 'geofences.id')
            ->join('devices', 'geofence_events.device_id', '=', 'devices.id')
            ->select(
                'geofences.name as geofence_name',
                'devices.name as device_name',
                'devices.vehicle_no',
                'geofence_events.entry_time',
                'geofence_events.exit_time',
                DB::raw('TIMESTAMPDIFF(SECOND, entry_time, exit_time) as dwell_seconds')
            )
            ->whereNotNull('geofence_events.exit_time')
            ->orderBy('geofence_events.entry_time', 'desc');

        if (!empty($this->devices)) {
            $query->whereIn('geofence_events.device_id', $this->devices);
        }

        if (isset($this->filters['geofences']) && !empty($this->filters['geofences'])) {
            $query->whereIn('geofence_events.geofence_id', $this->filters['geofences']);
        }

        $query->whereBetween('geofence_events.entry_time', [$this->dateFrom, $this->dateTo]);

        return collect($query->get())->map(function ($record) {
            return [
                'geofence_name' => $record->geofence_name,
                'device_name' => $record->device_name,
                'vehicle_no' => $record->vehicle_no ?? 'N/A',
                'entry_time' => $record->entry_time,
                'exit_time' => $record->exit_time,
                'dwell_duration' => $this->formatDuration($record->dwell_seconds ?? 0),
            ];
        });
    }
}
