<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCommand extends Model
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
        'command_type',
        'command_hex',
        'status',
        'sent_at',
        'acknowledged_at',
        'created_by',
        'response'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate GT06 cut-off command hex
     */
    public static function generateCutOffCommand(int $serial = 1): string
    {
        // GT06 Protocol: 78 78 [Len] 80 [String Command] [Serial] [CRC] 0D 0A
        $command = "RELAY,1#";
        $commandHex = bin2hex($command);
        $serialHex = str_pad(dechex($serial), 4, '0', STR_PAD_LEFT);
        
        // Simplified packet (actual CRC calculation would be more complex)
        return "787815" . "80" . $commandHex . $serialHex . "00000D0A";
    }

    /**
     * Generate GT06 restore command hex
     */
    public static function generateRestoreCommand(int $serial = 1): string
    {
        $command = "RELAY,0#";
        $commandHex = bin2hex($command);
        $serialHex = str_pad(dechex($serial), 4, '0', STR_PAD_LEFT);
        
        return "787815" . "80" . $commandHex . $serialHex . "00000D0A";
    }
}
