<div class="flex h-full w-full">
    
    <!-- Left Panel: Products Grid -->
    <div class="flex-1 flex flex-col bg-gray-50 h-full border-r border-gray-200">
        
        <!-- Search & Category Filter -->
        <div class="p-4 bg-white shadow-sm flex gap-3 z-10">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live="searchQuery" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2.5 outline-none" placeholder="Cari produk atau scan barcode...">
            </div>
            <select wire:model.live="selectedCategory" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5 outline-none cursor-pointer">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Products -->
        <div class="flex-1 overflow-y-auto p-4">
            @if(count($products) > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @foreach($products as $product)
                        <div wire:click="addToCart({{ $product->id }})" class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md cursor-pointer transition flex flex-col h-full overflow-hidden active:scale-95 group">
                            <div class="h-32 bg-gray-100 flex items-center justify-center relative">
                                @if($product->photo)
                                    <img src="{{ Storage::url($product->photo) }}" class="object-cover h-full w-full" alt="{{ $product->name }}">
                                @else
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                @endif
                                <div class="absolute inset-0 bg-primary-500 opacity-0 group-hover:opacity-10 transition"></div>
                            </div>
                            <div class="p-3 flex flex-col flex-1">
                                <h3 class="text-sm font-semibold text-gray-800 leading-tight mb-1 flex-1">{{ $product->name }}</h3>
                                <div class="flex items-end justify-between mt-2">
                                    <span class="text-primary-600 font-bold text-sm">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                                    <!-- Stock info could go here -->
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <p class="text-lg font-medium">Belum ada produk.</p>
                    <p class="text-sm mt-1">Silakan tambahkan di Backoffice.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Panel: Cart -->
    <div class="w-96 bg-white flex flex-col h-full shadow-[-4px_0_15px_-3px_rgba(0,0,0,0.05)] z-20">
        
        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Pesanan
            </h2>
            <button wire:click="clearCart" class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 bg-red-50 hover:bg-red-100 rounded transition">
                Kosongkan
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-2">
            @if(count($cart) > 0)
                <div class="space-y-2">
                    @foreach($cart as $index => $item)
                        <div class="bg-white border border-gray-100 p-3 rounded-lg shadow-sm flex flex-col gap-2 relative group">
                            
                            <!-- Delete button -->
                            <button wire:click="removeFromCart({{ $index }})" class="absolute top-2 right-2 text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 pr-5">{{ $item['name'] }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                            </div>
                            
                            <div class="flex items-center justify-between mt-1">
                                <div class="flex items-center gap-1 bg-gray-50 border border-gray-200 rounded p-0.5">
                                    <button wire:click="updateQty({{ $index }}, -1)" class="w-6 h-6 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path></svg>
                                    </button>
                                    <input type="number" wire:model.live="cart.{{ $index }}.qty" class="w-10 text-center text-sm font-semibold bg-transparent border-none focus:ring-0 p-0" min="1">
                                    <button wire:click="updateQty({{ $index }}, 1)" class="w-6 h-6 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                                <span class="font-bold text-primary-600 text-sm">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full text-gray-300">
                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p class="text-sm">Keranjang masih kosong</p>
                </div>
            @endif
        </div>

        <!-- Checkout Summary -->
        <div class="bg-gray-50 border-t border-gray-200 p-4 pb-6">
            <div class="flex justify-between items-center mb-2 text-sm text-gray-600">
                <span>Subtotal</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between items-center mb-4 text-sm text-gray-600">
                <span>Pajak (11%)</span>
                <span>Rp {{ number_format($tax, 0, ',', '.') }}</span>
            </div>
            
            <div class="flex justify-between items-center mb-6">
                <span class="font-bold text-lg text-gray-800">Total</span>
                <span class="font-bold text-2xl text-primary-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <button wire:click="checkout" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-primary-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2 text-lg disabled:opacity-50 disabled:cursor-not-allowed" @if(count($cart) === 0) disabled @endif>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                BAYAR SEKARANG
            </button>
            <div class="grid grid-cols-2 gap-2 mt-3">
                <button class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-2 px-4 rounded-lg transition text-sm">
                    Hold Bill
                </button>
                <button class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-2 px-4 rounded-lg transition text-sm">
                    Print Bill
                </button>
            </div>
            
            @if($activeShift)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <button wire:click="$set('showCloseShiftModal', true)" class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-semibold py-2 px-4 rounded-lg transition text-sm">
                    Tutup Shift Kasir
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Open Shift -->
    @if($showOpenShiftModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="bg-primary-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white">Buka Shift Kasir</h3>
                <p class="text-primary-100 text-sm mt-1">Masukkan modal awal (uang di laci) untuk memulai.</p>
            </div>
            <div class="p-6">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modal Awal (Rp)</label>
                    <input type="number" wire:model="startingCash" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-lg font-bold rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-3 outline-none" placeholder="0">
                </div>
                <button wire:click="openShift" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 rounded-lg shadow-md transition text-lg">
                    MULAI SHIFT
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Close Shift -->
    @if($showCloseShiftModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="bg-red-600 px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-white">Tutup Shift Kasir</h3>
                    <p class="text-red-100 text-sm mt-1">Rekap pendapatan sebelum menutup laci.</p>
                </div>
                <button wire:click="$set('showCloseShiftModal', false)" class="text-white hover:text-red-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <div class="flex justify-between text-sm mb-1 text-gray-600">
                        <span>Modal Awal:</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($activeShift->starting_cash ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @php
                        $expected = 0;
                        if($activeShift) {
                            $expected = $activeShift->starting_cash + \App\Models\Transaction::where('shift_id', $activeShift->id)->where('payment_status', 'paid')->sum('total');
                        }
                    @endphp
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Total Penjualan:</span>
                        <span class="font-semibold text-green-600">+ Rp {{ number_format($expected - ($activeShift->starting_cash ?? 0), 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-gray-200 my-2"></div>
                    <div class="flex justify-between font-bold text-gray-800">
                        <span>Estimasi Saldo Akhir:</span>
                        <span>Rp {{ number_format($expected, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Uang Fisik Aktual di Laci (Rp)</label>
                    <input type="number" wire:model="actualEndingCash" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-lg font-bold rounded-lg focus:ring-red-500 focus:border-red-500 block p-3 outline-none" placeholder="0">
                </div>
                <button wire:click="closeShift" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg shadow-md transition text-lg">
                    AKHIRI SHIFT
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
