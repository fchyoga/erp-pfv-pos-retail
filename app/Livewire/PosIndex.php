<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;

class PosIndex extends Component
{
    public $searchQuery = '';
    public $selectedCategory = '';
    public $activeShift;
    public $showOpenShiftModal = false;
    public $showCloseShiftModal = false;
    public $startingCash = 0;
    public $actualEndingCash = 0;
    
    public $showHistoryModal = false;
    public $shiftTransactions = [];
    public $paymentSummary = ['cash' => 0, 'qris' => 0, 'transfer' => 0];

    public function mount()
    {
        $userId = auth()->id() ?? 1;
        $outletId = auth()->user()?->outlet_id ?? \App\Models\Outlet::first()?->id ?? 1;

        
        $this->activeShift = \App\Models\Shift::where('user_id', $userId)
            ->where('outlet_id', $outletId)
            ->where('status', 'open')
            ->first();

        if (!$this->activeShift) {
            $this->showOpenShiftModal = true;
        }
    }

    public function render()
    {
        $categories = Category::all();
        
        $productsQuery = Product::where('is_active', true);
        
        if ($this->searchQuery) {
            $productsQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('barcode', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('sku', 'like', '%' . $this->searchQuery . '%');
            });
        }
        
        if ($this->selectedCategory) {
            $productsQuery->where('category_id', $this->selectedCategory);
        }
        
        $products = $productsQuery->get();

        $outlet = auth()->user()?->outlet;
        $taxPercentage = $outlet?->tax_percentage ?? 11;
        $discountPercentage = $outlet?->discount_percentage ?? 0;

        return view('livewire.pos-index', [
            'products' => $products,
            'categories' => $categories,
            'taxPercentage' => $taxPercentage,
            'discountPercentage' => $discountPercentage,
        ])->layout('components.layouts.app');
    }

    public function syncTransaction($cartData, $subtotal, $discount, $discountType, $tax, $total, $paymentMethod = 'cash', $changeAmount = 0)
    {
        // Ensure active shift exists
        if (!$this->activeShift) {
            $this->dispatch('notify', 'Harap buka shift terlebih dahulu!');
            return false;
        }

        // Generate Invoice Number
        $invoice = 'INV-' . date('YmdHis') . '-' . rand(1000, 9999);

        $outletId = auth()->user()?->outlet_id ?? \App\Models\Outlet::first()?->id ?? 1;
        
        $transaction = \App\Models\Transaction::create([
            'outlet_id' => $outletId,
            'user_id' => auth()->id() ?? 1,
            'shift_id' => $this->activeShift->id,
            'invoice_number' => $invoice,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_type' => $discountType,
            'tax' => $tax,
            'total' => $total,
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        foreach ($cartData as $item) {
            $product = \App\Models\Product::find($item['id']);
            $costPrice = $product ? $product->cost_price : 0;

            $transactionItem = \App\Models\TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'quantity' => $item['qty'],
                'unit_price' => $item['price'],
                'cost_price' => $costPrice,
                'discount_amount' => $item['discountAmount'] ?? 0,
                'discount_type' => $item['discountType'] ?? 'fixed',
                'subtotal' => $item['price'] * $item['qty'],
                'note' => $item['note'] ?? null,
            ]);

            // Deduct stock
            if ($product) {
                $product->decrement('stock', $item['qty']);

                // Record Stock Movement
                \App\Models\StockMovement::create([
                    'product_id' => $product->id,
                    'outlet_id' => $outletId,
                    'user_id' => auth()->id() ?? 1,
                    'type' => 'sale',
                    'quantity' => -abs($item['qty']), // negative for sale
                    'reference_type' => \App\Models\Transaction::class,
                    'reference_id' => $transaction->id,
                    'notes' => 'Sale from POS',
                ]);
            }
        }

        \App\Models\Payment::create([
            'transaction_id' => $transaction->id,
            'method' => $paymentMethod,
            'amount' => $total,
            'change' => $changeAmount,
        ]);

        $this->dispatch('notify', 'Transaksi Berhasil Disinkronkan!');
        return true;
    }

    public function openShift()
    {
        $userId = auth()->id() ?? 1;
        $outletId = auth()->user()?->outlet_id ?? \App\Models\Outlet::first()?->id ?? 1;


        $this->activeShift = \App\Models\Shift::create([
            'user_id' => $userId,
            'outlet_id' => $outletId,
            'starting_cash' => $this->startingCash,
            'expected_ending_cash' => $this->startingCash,
            'status' => 'open',
        ]);

        $this->showOpenShiftModal = false;
        $this->dispatch('notify', 'Shift Berhasil Dibuka!');
    }

    public function calculatePaymentSummary()
    {
        if (!$this->activeShift) return;
        
        $this->paymentSummary = [
            'cash' => \App\Models\Payment::whereHas('transaction', function($q) {
                $q->where('shift_id', $this->activeShift->id)->where('payment_status', 'paid');
            })->where('method', 'cash')->sum('amount'),
            'qris' => \App\Models\Payment::whereHas('transaction', function($q) {
                $q->where('shift_id', $this->activeShift->id)->where('payment_status', 'paid');
            })->where('method', 'qris')->sum('amount'),
            'transfer' => \App\Models\Payment::whereHas('transaction', function($q) {
                $q->where('shift_id', $this->activeShift->id)->where('payment_status', 'paid');
            })->where('method', 'transfer')->sum('amount'),
        ];
    }

    public function openCloseShiftModal()
    {
        $this->calculatePaymentSummary();
        $this->showCloseShiftModal = true;
    }

    public function closeShift($printed = false)
    {
        if (!$this->activeShift) return;

        // Calculate expected ending cash
        $transactions = \App\Models\Transaction::where('shift_id', $this->activeShift->id)
            ->where('payment_status', 'paid')
            ->get();
        
        $totalSales = $transactions->sum('total');
        $expected = $this->activeShift->starting_cash + $totalSales;

        $this->activeShift->update([
            'expected_ending_cash' => $expected,
            'actual_ending_cash' => $this->actualEndingCash,
            'closed_at' => now(),
            'status' => 'closed',
        ]);

        $this->activeShift = null;
        $this->showCloseShiftModal = false;
        $this->showOpenShiftModal = true;
        $this->startingCash = 0;
        $this->actualEndingCash = 0;
        
        $this->dispatch('notify', 'Shift Berhasil Ditutup!');
    }
    
    public function loadTransactionHistory()
    {
        if (!$this->activeShift) {
            $this->dispatch('notify', 'Harap buka shift terlebih dahulu!');
            return;
        }

        $this->shiftTransactions = \App\Models\Transaction::where('shift_id', $this->activeShift->id)
            ->where('user_id', auth()->id() ?? 1)
            ->orderBy('id', 'desc')
            ->get();
            
        $this->showHistoryModal = true;
    }
    
    public function refundTransaction($transactionId)
    {
        if (!$this->activeShift) return;

        $transaction = \App\Models\Transaction::where('id', $transactionId)
            ->where('shift_id', $this->activeShift->id)
            ->where('status', 'completed')
            ->first();

        if (!$transaction) {
            $this->dispatch('notify', 'Transaksi tidak dapat di-refund!');
            return;
        }

        // Void the transaction
        $transaction->update(['status' => 'void', 'payment_status' => 'void']);

        // Return stock and reverse stock movement
        foreach ($transaction->items as $item) {
            $product = \App\Models\Product::find($item->product_id);
            if ($product) {
                $product->increment('stock', $item->quantity);
                
                \App\Models\StockMovement::create([
                    'product_id' => $product->id,
                    'outlet_id' => $transaction->outlet_id,
                    'user_id' => auth()->id() ?? 1,
                    'type' => 'adjustment',
                    'quantity' => abs($item->quantity), // positive to return stock
                    'reference_type' => \App\Models\Transaction::class,
                    'reference_id' => $transaction->id,
                    'notes' => 'Refund Transaction: ' . $transaction->invoice_number,
                ]);
            }
        }

        // Remove payment
        if ($transaction->payment) {
            $transaction->payment->delete();
        }

        $this->loadTransactionHistory();
        $this->dispatch('notify', 'Transaksi ' . $transaction->invoice_number . ' Berhasil Di-Refund!');
    }

    public function voidLastTransaction()
    {
        if (!$this->activeShift) return;

        $lastTransaction = \App\Models\Transaction::where('shift_id', $this->activeShift->id)
            ->where('user_id', auth()->id() ?? 1)
            ->where('status', 'completed')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastTransaction) {
            $this->dispatch('notify', 'Tidak ada transaksi untuk dibatalkan!');
            return;
        }

        // Void the transaction
        $lastTransaction->update(['status' => 'void', 'payment_status' => 'void']);

        // Return stock and reverse stock movement
        foreach ($lastTransaction->items as $item) {
            $product = \App\Models\Product::find($item->product_id);
            if ($product) {
                $product->increment('stock', $item->quantity);
                
                \App\Models\StockMovement::create([
                    'product_id' => $product->id,
                    'outlet_id' => $lastTransaction->outlet_id,
                    'user_id' => auth()->id() ?? 1,
                    'type' => 'adjustment',
                    'quantity' => abs($item->quantity), // positive to return stock
                    'reference_type' => \App\Models\Transaction::class,
                    'reference_id' => $lastTransaction->id,
                    'notes' => 'Void Transaction: ' . $lastTransaction->invoice_number,
                ]);
            }
        }

        // Remove payment
        if ($lastTransaction->payment) {
            $lastTransaction->payment->delete();
        }

        $this->dispatch('notify', 'Transaksi ' . $lastTransaction->invoice_number . ' Berhasil Di-Void!');
    }
}
