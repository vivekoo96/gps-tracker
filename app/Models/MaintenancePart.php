<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenancePart extends Model
{
    protected $fillable = [
        'vendor_id', 'part_number', 'part_name', 'category', 'manufacturer',
        'unit_price', 'quantity_in_stock', 'minimum_stock_level',
        'location', 'supplier', 'supplier_contact', 'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity_in_stock <= minimum_stock_level');
    }

    public function getIsLowStockAttribute()
    {
        return $this->quantity_in_stock <= $this->minimum_stock_level;
    }
}
