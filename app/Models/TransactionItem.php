<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'discount_type' => 'string',
            'subtotal' => 'decimal:2',
        ];
    }
}
