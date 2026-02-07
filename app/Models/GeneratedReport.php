<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'generated_by',
        'file_path',
        'format',
        'parameters',
        'record_count',
        'generated_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'generated_at' => 'datetime',
    ];

    // Relationships
    public function template()
    {
        return $this->belongsTo(ReportTemplate::class, 'template_id');
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFileSizeAttribute()
    {
        $path = storage_path('app/public/' . $this->file_path);
        return file_exists($path) ? filesize($path) : 0;
    }
}
