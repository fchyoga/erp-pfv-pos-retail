<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $guarded = [];

    public function fromOutlet()
    {
        return $this->belongsTo(Outlet::class, 'from_outlet_id');
    }

    public function toOutlet()
    {
        return $this->belongsTo(Outlet::class, 'to_outlet_id');
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
        static::created(function ($transfer) {
            if ($transfer->status === 'completed') {
                self::processTransfer($transfer);
            }
        });

        static::updated(function ($transfer) {
            // If it was just updated to completed
            if ($transfer->isDirty('status') && $transfer->status === 'completed') {
                self::processTransfer($transfer);
            }
        });
    }

    protected static function processTransfer($transfer)
    {
        $product = $transfer->product;
        if ($product) {
            // In a real multi-outlet scenario where each outlet has its own stock table, 
            // we would deduct from source outlet stock and add to target outlet stock.
            // But since stock is currently global per product in 'products' table, 
            // the global stock count doesn't change, only its location.
            // We just record the movement out and in.

            // Movement OUT
            \App\Models\StockMovement::create([
                'product_id' => $product->id,
                'outlet_id' => $transfer->from_outlet_id,
                'user_id' => $transfer->user_id,
                'type' => 'transfer_out',
                'quantity' => -abs($transfer->quantity),
                'reference_type' => self::class,
                'reference_id' => $transfer->id,
                'notes' => 'Transfer to ' . $transfer->toOutlet->name,
            ]);

            // Movement IN
            \App\Models\StockMovement::create([
                'product_id' => $product->id,
                'outlet_id' => $transfer->to_outlet_id,
                'user_id' => $transfer->user_id,
                'type' => 'transfer_in',
                'quantity' => abs($transfer->quantity),
                'reference_type' => self::class,
                'reference_id' => $transfer->id,
                'notes' => 'Transfer from ' . $transfer->fromOutlet->name,
            ]);
        }
    }
}
