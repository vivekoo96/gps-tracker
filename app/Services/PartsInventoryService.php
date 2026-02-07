<?php

namespace App\Services;

use App\Models\MaintenancePart;
use App\Models\MaintenancePartUsage;

class PartsInventoryService
{
    /**
     * Add a new part to inventory
     */
    public function addPart(array $data)
    {
        return MaintenancePart::create($data);
    }

    /**
     * Update stock quantity
     */
    public function updateStock($partId, $quantity, $operation = 'add')
    {
        $part = MaintenancePart::findOrFail($partId);

        if ($operation === 'add') {
            $part->quantity_in_stock += $quantity;
        } elseif ($operation === 'subtract') {
            $part->quantity_in_stock -= $quantity;
        } else {
            $part->quantity_in_stock = $quantity;
        }

        $part->save();

        // Check if low stock
        if ($part->is_low_stock) {
            // TODO: Trigger low stock alert
        }

        return $part;
    }

    /**
     * Record part usage in maintenance
     */
    public function recordUsage($maintenanceRecordId, array $parts)
    {
        $totalPartsCost = 0;

        foreach ($parts as $partData) {
            $part = MaintenancePart::findOrFail($partData['part_id']);
            $quantity = $partData['quantity'];
            $unitPrice = $part->unit_price;
            $totalCost = $quantity * $unitPrice;

            // Create usage record
            MaintenancePartUsage::create([
                'maintenance_record_id' => $maintenanceRecordId,
                'part_id' => $part->id,
                'quantity_used' => $quantity,
                'unit_price' => $unitPrice,
                'total_cost' => $totalCost,
            ]);

            // Deduct from stock
            $this->updateStock($part->id, $quantity, 'subtract');

            $totalPartsCost += $totalCost;
        }

        return $totalPartsCost;
    }

    /**
     * Get parts with low stock
     */
    public function getLowStockParts($vendorId)
    {
        return MaintenancePart::where('vendor_id', $vendorId)
            ->lowStock()
            ->get();
    }

    /**
     * Get part usage history
     */
    public function getPartHistory($partId)
    {
        $part = MaintenancePart::with(['usage.maintenanceRecord'])->findOrFail($partId);

        $usage = $part->usage()->with('maintenanceRecord')->get();

        return [
            'part' => $part,
            'total_used' => $usage->sum('quantity_used'),
            'total_cost' => $usage->sum('total_cost'),
            'usage_records' => $usage,
        ];
    }

    /**
     * Get inventory value
     */
    public function getInventoryValue($vendorId)
    {
        return MaintenancePart::where('vendor_id', $vendorId)
            ->selectRaw('SUM(quantity_in_stock * unit_price) as total_value')
            ->value('total_value') ?? 0;
    }

    /**
     * Get parts by category
     */
    public function getPartsByCategory($vendorId)
    {
        return MaintenancePart::where('vendor_id', $vendorId)
            ->selectRaw('category, COUNT(*) as part_count, SUM(quantity_in_stock) as total_stock')
            ->groupBy('category')
            ->get();
    }
}
