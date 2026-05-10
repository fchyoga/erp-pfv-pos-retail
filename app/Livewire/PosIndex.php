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

        return view('livewire.pos-index', [
            'products' => $products,
            'categories' => $categories,
        ])->layout('components.layouts.app');
    }

    public function syncTransaction($cartData, $subtotal, $tax, $total, $paymentMethod = 'cash', $changeAmount = 0)
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
            'tax' => $tax,
            'total' => $total,
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        foreach ($cartData as $item) {
            \App\Models\TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'quantity' => $item['qty'],
                'unit_price' => $item['price'],
                'subtotal' => $item['price'] * $item['qty'],
                'note' => $item['note'] ?? null,
            ]);
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

    public function closeShift()
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
}
