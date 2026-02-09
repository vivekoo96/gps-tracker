<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProtocolLog extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\VendorScope);
        
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->vendor_id && !isset($model->vendor_id)) {
                $model->vendor_id = auth()->user()->vendor_id;
            }
        });
    }

    protected $fillable = [
        'device_id',
        'protocol_type',
        'raw_data',
        'parsed_data',
        'parse_success',
        'error_message',
    ];

    protected $casts = [
        'parsed_data' => 'array',
        'parse_success' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Scope for successful parses
     */
    public function scopeSuccessful($query)
    {
        return $query->where('parse_success', true);
    }

    /**
     * Scope for failed parses
     */
    public function scopeFailed($query)
    {
        return $query->where('parse_success', false);
    }

    /**
     * Scope by protocol type
     */
    public function scopeByProtocol($query, string $protocol)
    {
        return $query->where('protocol_type', $protocol);
    }
}
