<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $guarded = [];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::created(function ($adjustment) {
            $product = $adjustment->product;
            if ($product) {
                // Update product stock directly to actual
                $product->update(['stock' => $adjustment->actual_stock]);

                // Create stock movement
                \App\Models\StockMovement::create([
                    'product_id' => $product->id,
                    'outlet_id' => $adjustment->outlet_id,
                    'user_id' => $adjustment->user_id,
                    'type' => 'adjustment',
                    'quantity' => $adjustment->difference,
                    'reference_type' => self::class,
                    'reference_id' => $adjustment->id,
                    'notes' => 'Stock adjustment: ' . $adjustment->reason,
                ]);
            }
        });
    }
}
