<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'created_by',
        'name',
        'type',
        'description',
        'columns',
        'filters',
        'grouping',
        'sorting',
        'schedule',
        'recipients',
        'is_active',
    ];

    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
        'grouping' => 'array',
        'sorting' => 'array',
        'recipients' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generatedReports()
    {
        return $this->hasMany(GeneratedReport::class, 'template_id');
    }

    public function schedule()
    {
        return $this->hasOne(ReportSchedule::class, 'template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
