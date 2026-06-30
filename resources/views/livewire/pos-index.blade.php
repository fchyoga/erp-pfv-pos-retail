<div x-data="posApp({{ $taxPercentage }}, {{ $discountPercentage }})" 
     id="pos-root-element"
     :class="{ 'is-printing-rekap': printingRekap }"
     data-products="{{ json_encode($products->keyBy('id')) }}"
     class="flex flex-col lg:flex-row h-full w-full relative overflow-hidden">
    
    <!-- Offline Indicator -->
    <div x-show="isOffline" style="display: none;" class="absolute top-0 left-0 right-0 bg-orange-500 text-white text-center py-1 text-sm font-bold z-50">
        ANDA SEDANG OFFLINE. Transaksi akan disimpan sementara di perangkat.
        <span x-show="syncQueue.length > 0" class="ml-2 bg-white text-orange-600 px-2 rounded-full text-xs" x-text="syncQueue.length + ' Menunggu Sync'"></span>
    </div>

    @if(session('error'))
    {{-- Flash error dari backend guard logout (misal kasir bypass lewat URL) --}}
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 5000)"
        x-transition.opacity.duration.300ms
        class="absolute top-4 left-1/2 -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg font-bold flex items-center gap-2 z-[60]">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.193 2.5 1.732 2.5z"></path></svg>
        {{ session('error') }}
    </div>
    @endif

    <!-- Left Panel: Products Grid -->
    <div :class="mobileTab === 'products' ? 'flex' : 'hidden lg:flex'" class="flex-1 flex-col bg-gray-50 h-full border-r border-gray-200 mt-0 lg:mt-6 left-panel-ui relative w-full lg:w-auto">
        
        <!-- Search & Category Filter (Livewire still handles search, but we just re-render grid) -->
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
                        <div @click="addToCart({{ $product->id }})" class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md cursor-pointer transition flex flex-col h-full overflow-hidden active:scale-95 group">
                            <div class="h-32 bg-gray-100 flex items-center justify-center relative">
                                @if($product->photo)
                                    <img src="{{ Storage::disk('public')->url($product->photo) }}" class="object-cover h-full w-full" alt="{{ $product->name }}">
                                @else
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                @endif
                                <div class="absolute inset-0 bg-primary-500 opacity-0 group-hover:opacity-10 transition"></div>
                            </div>
                            <div class="p-3 flex flex-col flex-1">
                                <h3 class="text-sm font-semibold text-gray-800 leading-tight mb-1 flex-1">{{ $product->name }}</h3>
                                <div class="flex items-end justify-between mt-2">
                                    <span class="text-primary-600 font-bold text-sm">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                                    <span class="text-xs {{ $product->stock <= $product->reorder_point ? 'text-red-500 font-bold' : 'text-gray-500' }}">Stok: {{ $product->stock }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <p class="text-lg font-medium">Belum ada produk.</p>
                </div>
            @endif
        </div>
        
        <!-- Mobile FAB to open cart -->
        <button @click="mobileTab = 'cart'" class="lg:hidden absolute bottom-6 left-1/2 -translate-x-1/2 bg-primary-600 text-white px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 transition hover:bg-primary-700 z-40 active:scale-95 print:hidden">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Keranjang <span x-show="cart.length > 0" class="bg-white text-primary-600 rounded-full px-2 py-0.5 text-xs ml-1" x-text="cart.length"></span>
        </button>
    </div>

    <!-- Right Panel: Cart -->
    <div :class="mobileTab === 'cart' ? 'flex' : 'hidden lg:flex'" class="w-full lg:w-96 bg-white flex-col h-full shadow-[-4px_0_15px_-3px_rgba(0,0,0,0.05)] z-30 lg:z-20 mt-0 lg:mt-6 right-panel-ui">
        
        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <div class="flex items-center gap-2">
                <button @click="mobileTab = 'products'" class="lg:hidden text-gray-500 hover:text-gray-800 p-1 bg-gray-200 rounded-full active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500 hidden lg:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Pesanan
                </h2>
            </div>
            <button @click="clearCart" class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 bg-red-50 hover:bg-red-100 rounded transition">
                Kosongkan
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-2" wire:ignore>
            <template x-if="cart.length > 0">
                <div class="space-y-2">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="bg-white border border-gray-100 p-3 rounded-lg shadow-sm flex flex-col gap-2 relative group">
                            
                            <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-gray-800 text-sm leading-tight cursor-pointer hover:text-primary-600" @click="openItemDiscountModal(index)" x-text="item.name"></h3>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <p class="text-xs text-gray-500">Rp <span x-text="formatCurrency(item.price)"></span></p>
                                    <template x-if="item.discountAmount > 0">
                                        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded cursor-pointer" @click="openItemDiscountModal(index)">
                                            Diskon: <span x-text="item.discountType === 'percentage' ? item.discountAmount + '%' : 'Rp ' + formatCurrency(item.discountAmount)"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600 transition p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                        <input type="text" x-model="item.note" placeholder="Catatan pesanan..." class="w-full text-xs border border-gray-200 rounded-md p-1.5 mb-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center bg-gray-100 rounded-lg p-0.5 border border-gray-200">
                                <button @click="updateQty(index, -1)" class="w-6 h-6 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path></svg>
                                </button>
                                <input type="number" x-model="item.qty" class="w-10 text-center text-sm font-semibold bg-transparent border-none focus:ring-0 p-0" min="1">
                                <button @click="updateQty(index, 1)" class="w-6 h-6 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                            <span class="font-bold text-primary-600 text-sm">Rp <span x-text="formatCurrency(item.price * item.qty)"></span></span>
                        </div>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="cart.length === 0">
                <div class="flex flex-col items-center justify-center h-full text-gray-300">
                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p class="text-sm">Keranjang masih kosong</p>
                </div>
            </template>
        </div>

        <!-- Checkout Summary -->
        <div class="bg-gray-50 border-t border-gray-200 p-4 pb-6">
            <div class="flex justify-between items-center mb-2 text-sm text-gray-600">
                <span>Subtotal</span>
                <span>Rp <span x-text="formatCurrency(subtotal)"></span></span>
            </div>
            
            <!-- Global Discount Section -->
            <div class="flex justify-between items-center mb-2 text-sm text-gray-600">
                <button @click="openTransactionDiscountModal" class="text-primary-600 hover:text-primary-700 font-medium underline decoration-dashed underline-offset-2">
                    <template x-if="discountAmount > 0">
                        <span>Diskon (<span x-text="transactionDiscountType === 'percentage' ? transactionDiscountInput + '%' : 'Rp ' + formatCurrency(transactionDiscountInput)"></span>)</span>
                    </template>
                    <template x-if="discountAmount <= 0">
                        <span>+ Tambah Diskon</span>
                    </template>
                </button>
                <template x-if="discountAmount > 0">
                    <span class="text-red-500 font-semibold">- Rp <span x-text="formatCurrency(discountAmount)"></span></span>
                </template>
            </div>
            <div class="flex justify-between items-center mb-4 text-sm text-gray-600">
                <span>Pajak (<span x-text="taxPercentage"></span>%)</span>
                <span>Rp <span x-text="formatCurrency(tax)"></span></span>
            </div>
            
            <div class="flex justify-between items-center mb-6">
                <span class="font-bold text-lg text-gray-800">Total</span>
                <span class="font-bold text-2xl text-primary-600">Rp <span x-text="formatCurrency(total)"></span></span>
            </div>

            <button @click="openCheckout" :disabled="cart.length === 0" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-primary-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2 text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                BAYAR SEKARANG
            </button>
            <div class="grid grid-cols-2 gap-2 mt-3">
                <button class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-2 px-4 rounded-lg transition text-sm disabled:opacity-50" :disabled="syncQueue.length === 0" @click="processSyncQueue">
                    Sync <span x-show="syncQueue.length > 0" x-text="'(' + syncQueue.length + ')'"></span>
                </button>
                <button onclick="window.print()" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-2 px-4 rounded-lg transition text-sm">
                    Print / Reprint
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-2 mt-2">
                <button class="bg-amber-50 border border-amber-200 hover:bg-amber-100 text-amber-700 font-semibold py-2 px-4 rounded-lg transition text-sm disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-1" :disabled="cart.length === 0" @click="showSuspendModal = true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Hold
                </button>
                <button class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-2 px-4 rounded-lg transition text-sm flex justify-center items-center gap-1" @click="showSuspendListModal = true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    List Hold <span x-show="suspendedCarts.length > 0" class="bg-amber-500 text-white rounded-full text-[10px] px-1.5 py-0.5 ml-1" x-text="suspendedCarts.length"></span>
                </button>
            </div>
            
            @if($activeShift)
            <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
                <div class="flex gap-2">
                    <button wire:click="initiateVoid" class="flex-1 bg-white border border-red-300 text-red-600 hover:bg-red-50 font-semibold py-2 px-2 rounded-lg transition text-xs flex justify-center items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Void Terakhir
                    </button>
                    <button wire:click="loadTransactionHistory" class="flex-1 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 px-2 rounded-lg transition text-xs flex justify-center items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        History Shift
                    </button>
                </div>
                <button wire:click="openCloseShiftModal" class="w-full bg-red-50 text-red-600 hover:bg-red-100 font-semibold py-2 px-4 rounded-lg transition text-sm">
                    Tutup Shift Kasir
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Modals code unchanged, appended below -->
    @if($showOpenShiftModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm print:hidden">
        <div class="bg-white rounded-2xl shadow-xl w-[95%] max-w-md overflow-hidden max-h-[90vh] flex flex-col">
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

                <div class="mt-4 border-t border-gray-100 pt-4 flex flex-col gap-2">
                    @if(!auth()->user()->hasRole('kasir'))
                    {{-- Hanya Admin yang bisa kembali ke dashboard tanpa buka shift --}}
                    <a href="/admin" class="w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 px-4 rounded-xl border border-gray-200 transition text-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Kembali ke Dashboard
                    </a>
                    @endif
                    {{-- Semua role bisa logout dari sini setelah shift ditutup --}}
                    <a href="{{ route('logout') }}" class="w-full text-center bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2.5 px-4 rounded-xl border border-red-200 transition text-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Keluar (Logout)
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif


    @if($showCloseShiftModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm print:hidden">
        <div class="bg-white rounded-2xl shadow-xl w-[95%] max-w-md overflow-hidden max-h-[90vh] flex flex-col">
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
                        <span>Total Penjualan Online:</span>
                        <span class="font-semibold text-green-600">+ Rp {{ number_format($expected - ($activeShift->starting_cash ?? 0), 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-gray-200 my-2"></div>
                    <div class="flex justify-between font-bold text-gray-800">
                        <span>Estimasi Saldo (belum termasuk transaksi offline):</span>
                        <span>Rp {{ number_format($expected, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Uang Fisik Aktual di Laci (Rp)</label>
                    <input type="number" wire:model="actualEndingCash" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-lg font-bold rounded-lg focus:ring-red-500 focus:border-red-500 block p-3 outline-none" placeholder="0">
                </div>
                <div class="flex gap-3">
                    <button wire:click="closeShift(false)" class="flex-1 bg-white border border-red-300 text-red-600 hover:bg-red-50 font-bold py-3 rounded-lg shadow-sm transition text-sm">
                        AKHIRI SAJA
                    </button>
                    <button @click="printingRekap = true; setTimeout(() => { window.print(); printingRekap = false; $wire.closeShift(true); }, 500)" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg shadow-md transition text-sm">
                        AKHIRI & CETAK REKAP
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Checkout Modal -->
    <div x-show="showCheckoutModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto print:hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div x-show="showCheckoutModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showCheckoutModal = false"></div>

            <div x-show="showCheckoutModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-[95%] sm:w-full">
                <div class="bg-white px-6 pt-6 pb-6">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-2xl leading-6 font-bold text-gray-900" id="modal-title">Pembayaran</h3>
                        <button @click="showCheckoutModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Total Amount -->
                    <div class="bg-primary-50 rounded-xl p-4 mb-6 border border-primary-100 text-center">
                        <p class="text-sm font-medium text-primary-600 mb-1">Total Tagihan</p>
                        <p class="text-4xl font-black text-primary-700">Rp <span x-text="formatCurrency(total)"></span></p>
                    </div>

                    <!-- Payment Method Tabs -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button @click="paymentMethod = 'cash'" :class="paymentMethod === 'cash' ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'" class="border rounded-lg py-2.5 font-semibold transition">
                                Tunai
                            </button>
                            <button @click="paymentMethod = 'qris'" :class="paymentMethod === 'qris' ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'" class="border rounded-lg py-2.5 font-semibold transition">
                                QRIS
                            </button>
                            <button @click="paymentMethod = 'transfer'" :class="paymentMethod === 'transfer' ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'" class="border rounded-lg py-2.5 font-semibold transition">
                                Transfer
                            </button>
                        </div>
                    </div>

                    <!-- Cash Input Area (Only visible if cash) -->
                    <div x-show="paymentMethod === 'cash'" x-collapse>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Uang Diterima (Rp)</label>
                        <input type="number" x-model="cashAmount" class="w-full bg-white border border-gray-300 text-gray-900 text-2xl font-bold rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-3 outline-none text-right mb-3" placeholder="0">
                        
                        <!-- Quick Cash Buttons -->
                        <div class="grid grid-cols-4 gap-2 mb-6">
                            <button @click="setExactAmount" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 rounded border border-gray-200 text-sm">Pas</button>
                            <button @click="quickAmount(10000)" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 rounded border border-gray-200 text-sm">+10k</button>
                            <button @click="quickAmount(50000)" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 rounded border border-gray-200 text-sm">+50k</button>
                            <button @click="quickAmount(100000)" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 rounded border border-gray-200 text-sm">+100k</button>
                        </div>

                        <!-- Change Amount -->
                        <div class="flex justify-between items-center py-3 border-t border-gray-200">
                            <span class="text-base font-semibold text-gray-600">Kembalian:</span>
                            <span :class="changeAmount < 0 ? 'text-red-500' : 'text-green-600'" class="text-2xl font-bold">
                                Rp <span x-text="formatCurrency(changeAmount)"></span>
                            </span>
                        </div>
                    </div>

                    <!-- QRIS Area -->
                    <div x-show="paymentMethod === 'qris'" x-collapse class="text-center py-4">
                        <div class="bg-gray-100 p-4 rounded-lg inline-block mb-3 border border-gray-200">
                            <!-- Placeholder QR Code -->
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=DUMMY_QRIS_CODE" alt="QRIS" class="w-32 h-32 mx-auto mix-blend-multiply">
                        </div>
                        <p class="text-sm text-gray-500">Minta pelanggan scan QRIS ini.</p>
                    </div>

                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse rounded-b-2xl">
                    <button @click="processCheckout" :disabled="paymentMethod === 'cash' && changeAmount < 0" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3.5 bg-primary-600 text-lg font-bold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Discount Modal -->
    <div x-show="showItemDiscountModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto print:hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div x-show="showItemDiscountModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showItemDiscountModal = false"></div>
            <div x-show="showItemDiscountModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-[95%] sm:w-full">
                <div class="bg-white px-6 pt-6 pb-6">
                    <h3 class="text-xl leading-6 font-bold text-gray-900 mb-4">Diskon per Item</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Diskon</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="tempItemDiscountType" value="fixed" class="text-primary-600 focus:ring-primary-500 w-4 h-4">
                                <span class="text-sm text-gray-700">Nominal (Rp)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="tempItemDiscountType" value="percentage" class="text-primary-600 focus:ring-primary-500 w-4 h-4">
                                <span class="text-sm text-gray-700">Persentase (%)</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Besaran Diskon</label>
                        <input type="number" x-model="tempItemDiscountAmount" class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-3 outline-none" placeholder="0">
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse rounded-b-2xl gap-2">
                    <button @click="applyItemDiscount" class="inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-bold text-white hover:bg-primary-700 focus:outline-none">
                        Terapkan
                    </button>
                    <button @click="showItemDiscountModal = false" class="inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Discount Modal -->
    <div x-show="showTransactionDiscountModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto print:hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div x-show="showTransactionDiscountModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showTransactionDiscountModal = false"></div>
            <div x-show="showTransactionDiscountModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-[95%] sm:w-full">
                <div class="bg-white px-6 pt-6 pb-6">
                    <h3 class="text-xl leading-6 font-bold text-gray-900 mb-4">Diskon Total (Keranjang)</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Diskon</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="tempTxDiscountType" value="fixed" class="text-primary-600 focus:ring-primary-500 w-4 h-4">
                                <span class="text-sm text-gray-700">Nominal (Rp)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="tempTxDiscountType" value="percentage" class="text-primary-600 focus:ring-primary-500 w-4 h-4">
                                <span class="text-sm text-gray-700">Persentase (%)</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Besaran Diskon</label>
                        <input type="number" x-model="tempTxDiscountAmount" class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-3 outline-none" placeholder="0">
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse rounded-b-2xl gap-2">
                    <button @click="applyTransactionDiscount" class="inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-bold text-white hover:bg-primary-700 focus:outline-none">
                        Terapkan
                    </button>
                    <button @click="showTransactionDiscountModal = false" class="inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspend Input Modal -->
    <div x-show="showSuspendModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto print:hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div x-show="showSuspendModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showSuspendModal = false"></div>
            <div x-show="showSuspendModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-[95%] sm:w-full">
                <div class="bg-white px-6 pt-6 pb-6">
                    <h3 class="text-xl leading-6 font-bold text-gray-900 mb-4">Tahan Transaksi (Hold)</h3>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pelanggan / Catatan</label>
                    <input type="text" x-model="suspendLabel" class="w-full bg-white border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-3 outline-none mb-2" placeholder="Misal: Meja 4 / Budi">
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse rounded-b-2xl gap-2">
                    <button @click="suspendCart" class="inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-amber-500 text-base font-bold text-white hover:bg-amber-600 focus:outline-none">
                        Simpan & Kosongkan
                    </button>
                    <button @click="showSuspendModal = false" class="inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspend List Modal -->
    <div x-show="showSuspendListModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto print:hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div x-show="showSuspendListModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showSuspendListModal = false"></div>
            <div x-show="showSuspendListModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-[95%] sm:w-full">
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-xl leading-6 font-bold text-gray-900">Daftar Transaksi Ditahan</h3>
                        <button @click="showSuspendListModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="max-h-96 overflow-y-auto pr-2">
                        <template x-if="suspendedCarts.length === 0">
                            <div class="text-center text-gray-500 py-8">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                Tidak ada transaksi yang ditahan.
                            </div>
                        </template>
                        <template x-for="(scart, index) in suspendedCarts" :key="index">
                            <div class="flex items-center justify-between border-b border-gray-200 py-3 last:border-0">
                                <div>
                                    <h4 class="font-bold text-gray-800" x-text="scart.label"></h4>
                                    <p class="text-xs text-gray-500">
                                        <span x-text="scart.timestamp"></span> • <span x-text="scart.cart.length"></span> item
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="resumeCart(index)" class="bg-primary-100 text-primary-700 hover:bg-primary-200 px-3 py-1.5 rounded text-sm font-semibold transition">
                                        Lanjutkan
                                    </button>
                                    <button @click="deleteSuspendedCart(index)" class="text-red-400 hover:text-red-600 p-1.5 rounded hover:bg-red-50 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Transaction History Modal -->
    @if($showHistoryModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/50 print:hidden">
        <div class="bg-white rounded-2xl shadow-xl w-[95%] max-w-2xl overflow-hidden flex flex-col max-h-[80vh]">
            <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">History Shift Aktif</h3>
                <button wire:click="$set('showHistoryModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-0 overflow-y-auto flex-1">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky top-0">
                        <tr>
                            <th scope="col" class="px-6 py-3">Invoice</th>
                            <th scope="col" class="px-6 py-3">Waktu</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Total</th>
                            <th scope="col" class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shiftTransactions as $tx)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $tx->invoice_number }}</td>
                                <td class="px-6 py-3">{{ $tx->created_at->format('H:i:s') }}</td>
                                <td class="px-6 py-3">
                                    @if($tx->status === 'completed')
                                        <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-green-400">Completed</span>
                                    @elseif($tx->status === 'void')
                                        <span class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded border border-red-400">Void</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">Rp {{ number_format($tx->total, 0, ',', '.') }}</td>
                                <td class="px-6 py-3">
                                    @if($tx->status === 'completed')
                                    <button wire:click="initiateRefund({{ $tx->id }})" class="text-red-600 hover:text-red-900 font-medium text-xs px-2 py-1 border border-red-600 rounded hover:bg-red-50 transition">
                                        Refund
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada transaksi di shift ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <p class="text-xs text-gray-500 text-center">* Hanya menampilkan transaksi yang terjadi pada shift saat ini.</p>
            </div>
        </div>
    </div>
    @endif
    
    <!-- PIN Verification Modal -->
    @if($showPinModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm print:hidden">
        <div class="bg-white rounded-2xl shadow-xl w-[95%] max-w-sm overflow-hidden flex flex-col max-h-[90vh]">
            <div class="bg-red-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Otorisasi Supervisor</h3>
                <button wire:click="$set('showPinModal', false)" class="text-white hover:text-red-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-gray-600 text-sm mb-4">Masukkan 6-digit PIN Supervisor/Admin untuk menyetujui transaksi pembatalan (Void/Refund).</p>
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">PIN Supervisor</label>
                    <input type="password" wire:model.live="supervisorPin" maxlength="6" class="w-full bg-gray-50 border border-gray-300 text-center text-gray-900 text-2xl tracking-widest font-bold rounded-lg focus:ring-red-500 focus:border-red-500 block p-3 outline-none" placeholder="******">
                </div>
                <div class="flex space-x-3">
                    <button wire:click="$set('showPinModal', false)" class="flex-1 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-3 rounded-lg shadow-sm transition">
                        Batal
                    </button>
                    <button wire:click="verifyPinAndExecute" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg shadow-md transition" {{ strlen($supervisorPin) < 4 ? 'disabled' : '' }}>
                        Setujui
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Print Receipt Template -->
    <div id="print-receipt">
        <div style="text-align: center; margin-bottom: 10px;">
            <img src="{{ asset('logo.png') }}" style="max-height: 50px; width: auto; margin: 0 auto 5px auto; display: block;" alt="Logo">
            <h2 style="margin: 0; font-size: 16px; font-weight: bold;">{{ $receiptHeader }}</h2>
            <p style="margin: 0; font-size: 11px;">Wisata Provit Farm Village</p>
        </div>
        
        <div style="border-bottom: 1px dashed #000; margin-bottom: 10px; padding-bottom: 5px;">
            <p style="margin: 0; font-size: 12px;">No: <span x-text="lastReceipt?.invoice"></span></p>
            <p style="margin: 0; font-size: 12px;">Tgl: <span x-text="lastReceipt?.date"></span></p>
        </div>

        <table style="width: 100%; font-size: 12px; margin-bottom: 10px;">
            <template x-for="(item, index) in lastReceipt?.cart" :key="index">
                <tr>
                    <td style="padding-bottom: 5px;">
                        <div x-text="item.name" style="font-weight: bold;"></div>
                        <template x-if="item.note">
                            <div style="font-size: 10px; font-style: italic; color: #555;">Catatan: <span x-text="item.note"></span></div>
                        </template>
                        <div><span x-text="item.qty"></span> x <span x-text="formatCurrency(item.price)"></span></div>
                    </td>
                    <td style="text-align: right; vertical-align: bottom; padding-bottom: 5px;" x-text="formatCurrency(item.qty * item.price)"></td>
                </tr>
            </template>
        </table>

        <div style="border-top: 1px dashed #000; padding-top: 5px; font-size: 12px;">
            <div style="display: flex; justify-content: space-between;">
                <span>Subtotal</span>
                <span x-text="formatCurrency(lastReceipt?.subtotal || 0)"></span>
            </div>
            <template x-if="lastReceipt?.discount > 0">
                <div style="display: flex; justify-content: space-between; color: #555;">
                    <span>Diskon</span>
                    <span x-text="'-' + formatCurrency(lastReceipt?.discount || 0)"></span>
                </div>
            </template>
            <div style="display: flex; justify-content: space-between;">
                <span>Pajak</span>
                <span x-text="formatCurrency(lastReceipt?.tax || 0)"></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 5px; font-size: 14px;">
                <span>TOTAL</span>
                <span x-text="formatCurrency(lastReceipt?.total || 0)"></span>
            </div>
        </div>

        <div style="border-top: 1px dashed #000; padding-top: 5px; font-size: 12px; margin-top: 5px;">
            <div style="display: flex; justify-content: space-between;">
                <span>Pembayaran (<span style="text-transform: uppercase;" x-text="lastReceipt?.paymentMethod"></span>)</span>
                <span x-text="formatCurrency(lastReceipt?.cashAmount || 0)"></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Kembalian</span>
                <span x-text="formatCurrency(lastReceipt?.changeAmount || 0)"></span>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 12px;">
            <p style="margin: 0;">{{ $receiptFooter }}</p>
            <p style="margin: 0;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
        </div>
    </div>

    <!-- Print Rekap Shift Template -->
    <div id="print-rekap">
        <div style="text-align: center; margin-bottom: 10px;">
            <h2 style="margin: 0; font-size: 18px; font-weight: bold;">{{ $receiptHeader }}</h2>
            <p style="margin: 0; font-size: 12px;">REKAPITULASI SHIFT</p>
        </div>
        
        <div style="border-bottom: 1px dashed #000; margin-bottom: 10px; padding-bottom: 5px; font-size: 12px;">
            <p style="margin: 0;">Kasir: {{ auth()->user()->name ?? 'Admin' }}</p>
            <p style="margin: 0;">Waktu Buka: {{ $activeShift ? $activeShift->created_at->format('d/m/Y H:i') : '' }}</p>
            <p style="margin: 0;">Waktu Tutup: <span x-text="new Date().toLocaleString('id-ID')"></span></p>
        </div>

        <div style="font-size: 12px; margin-bottom: 10px;">
            <div style="display: flex; justify-content: space-between;">
                <span>Modal Awal</span>
                <span>Rp {{ number_format($activeShift->starting_cash ?? 0, 0, ',', '.') }}</span>
            </div>
            <div style="border-bottom: 1px dashed #ccc; margin: 5px 0;"></div>
            <div style="display: flex; justify-content: space-between;">
                <span>Total Cash</span>
                <span>Rp {{ number_format($paymentSummary['cash'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Total QRIS</span>
                <span>Rp {{ number_format($paymentSummary['qris'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Total Transfer</span>
                <span>Rp {{ number_format($paymentSummary['transfer'] ?? 0, 0, ',', '.') }}</span>
            </div>
            <div style="border-bottom: 1px dashed #000; margin: 5px 0;"></div>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>Total Penjualan</span>
                <span>Rp {{ number_format(($paymentSummary['cash'] ?? 0) + ($paymentSummary['qris'] ?? 0) + ($paymentSummary['transfer'] ?? 0), 0, ',', '.') }}</span>
            </div>
            <div style="border-bottom: 1px dashed #000; margin: 5px 0;"></div>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>Estimasi Saldo Laci</span>
                <span>Rp {{ number_format(($activeShift->starting_cash ?? 0) + ($paymentSummary['cash'] ?? 0), 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 5px;">
                <span>Uang Aktual (Diinput)</span>
                <span>Rp <span x-text="formatCurrency($wire.actualEndingCash || 0)"></span></span>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 12px;">
            <p style="margin: 0;">-- End of Report --</p>
            <p style="margin: 0; margin-top: 5px;">{{ $receiptFooter }}</p>
        </div>
    </div>
</div>
