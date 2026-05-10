<div x-data="posApp(@js($products->keyBy('id')))" class="flex h-full w-full relative">
    
    <!-- Offline Indicator -->
    <div x-show="isOffline" style="display: none;" class="absolute top-0 left-0 right-0 bg-orange-500 text-white text-center py-1 text-sm font-bold z-50">
        ANDA SEDANG OFFLINE. Transaksi akan disimpan sementara di perangkat.
        <span x-show="syncQueue.length > 0" class="ml-2 bg-white text-orange-600 px-2 rounded-full text-xs" x-text="syncQueue.length + ' Menunggu Sync'"></span>
    </div>

    <!-- Left Panel: Products Grid -->
    <div class="flex-1 flex flex-col bg-gray-50 h-full border-r border-gray-200 mt-6 left-panel-ui">
        
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
    </div>

    <!-- Right Panel: Cart -->
    <div class="w-96 bg-white flex flex-col h-full shadow-[-4px_0_15px_-3px_rgba(0,0,0,0.05)] z-20 mt-6 right-panel-ui">
        
        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Pesanan
            </h2>
            <button @click="clearCart" class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 bg-red-50 hover:bg-red-100 rounded transition">
                Kosongkan
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-2">
            <template x-if="cart.length > 0">
                <div class="space-y-2">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="bg-white border border-gray-100 p-3 rounded-lg shadow-sm flex flex-col gap-2 relative group">
                            
                            <button @click="removeFromCart(index)" class="absolute top-2 right-2 text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 pr-5" x-text="item.name"></h4>
                                <p class="text-xs text-gray-500 mt-0.5">Rp <span x-text="formatCurrency(item.price)"></span></p>
                            </div>
                            
                            <div class="flex items-center justify-between mt-1">
                                <div class="flex items-center gap-1 bg-gray-50 border border-gray-200 rounded p-0.5">
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
            <div class="flex justify-between items-center mb-4 text-sm text-gray-600">
                <span>Pajak (11%)</span>
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

    <!-- Modals code unchanged, appended below -->
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
                <button wire:click="closeShift" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg shadow-md transition text-lg">
                    AKHIRI SHIFT
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Checkout Modal -->
    <div x-show="showCheckoutModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div x-show="showCheckoutModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showCheckoutModal = false"></div>

            <div x-show="showCheckoutModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
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

    <!-- Print Receipt Template -->
    <div id="print-receipt" x-show="lastReceipt">
        <div style="text-align: center; margin-bottom: 10px;">
            <h2 style="margin: 0; font-size: 18px; font-weight: bold;">Provit Farm Village</h2>
            <p style="margin: 0; font-size: 12px;">Desa Wisata PFV</p>
        </div>
        
        <div style="border-bottom: 1px dashed #000; margin-bottom: 10px; padding-bottom: 5px;">
            <p style="margin: 0; font-size: 12px;">No: <span x-text="lastReceipt?.invoice"></span></p>
            <p style="margin: 0; font-size: 12px;">Tgl: <span x-text="lastReceipt?.date"></span></p>
        </div>

        <table style="width: 100%; font-size: 12px; margin-bottom: 10px;">
            <template x-for="(item, index) in lastReceipt?.cart" :key="index">
                <tr>
                    <td style="padding-bottom: 5px;">
                        <div x-text="item.name"></div>
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
            <div style="display: flex; justify-content: space-between;">
                <span>Pajak (11%)</span>
                <span x-text="formatCurrency(lastReceipt?.tax || 0)"></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 5px; font-size: 14px;">
                <span>TOTAL</span>
                <span x-text="formatCurrency(lastReceipt?.total || 0)"></span>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 12px;">
            <p style="margin: 0;">Terima Kasih atas kunjungan Anda!</p>
            <p style="margin: 0;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
        </div>
    </div>
</div>
