<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS | Provit Farm Village</title>
    <link rel="manifest" href="/manifest.json">
    <!-- Use Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    }
                }
            }
        }
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('posApp', (productsData) => ({
                products: productsData,
                cart: [],
                syncQueue: [],
                subtotal: 0,
                tax: 0,
                total: 0,
                isOffline: !navigator.onLine,
                lastReceipt: null,
                
                async init() {
                    this.cart = await localforage.getItem('pos_cart') || [];
                    this.syncQueue = await localforage.getItem('pos_sync_queue') || [];
                    this.calculateTotals();
                    
                    this.$watch('cart', (val) => {
                        localforage.setItem('pos_cart', val);
                        this.calculateTotals();
                    }, { deep: true });
                    
                    window.addEventListener('online', () => {
                        this.isOffline = false;
                        this.processSyncQueue();
                    });
                    window.addEventListener('offline', () => {
                        this.isOffline = true;
                    });
                    
                    // Try syncing immediately on load if online
                    if (!this.isOffline) {
                        setTimeout(() => this.processSyncQueue(), 2000);
                    }
                },
                
                calculateTotals() {
                    this.subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                    this.tax = this.subtotal * 0.11;
                    this.total = this.subtotal + this.tax;
                },
                
                addToCart(productId) {
                    const product = this.products[productId];
                    if (!product) return;
                    
                    const existing = this.cart.find(item => item.id == productId);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cart.push({
                            id: product.id,
                            name: product.name,
                            price: product.selling_price,
                            qty: 1
                        });
                    }
                },
                
                updateQty(index, change) {
                    this.cart[index].qty += change;
                    if (this.cart[index].qty <= 0) {
                        this.removeFromCart(index);
                    }
                },
                
                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },
                
                clearCart() {
                    this.cart = [];
                },
                
                async checkout() {
                    if (this.cart.length === 0) return;
                    
                    let invoiceNumber = 'INV-' + new Date().toISOString().replace(/[-:T.]/g, '').substring(0, 14);

                    this.lastReceipt = {
                        invoice: invoiceNumber,
                        cart: JSON.parse(JSON.stringify(this.cart)),
                        subtotal: this.subtotal,
                        tax: this.tax,
                        total: this.total,
                        date: new Date().toLocaleString('id-ID')
                    };

                    if (this.isOffline) {
                        this.syncQueue.push({
                            id: Date.now(),
                            cart: JSON.parse(JSON.stringify(this.cart)),
                            subtotal: this.subtotal,
                            tax: this.tax,
                            total: this.total,
                            timestamp: new Date().toISOString()
                        });
                        await localforage.setItem('pos_sync_queue', this.syncQueue);
                        this.clearCart();
                        window.dispatchEvent(new CustomEvent('notify', { detail: ['Tersimpan Offline. Menunggu Sinkronisasi.'] }));
                        setTimeout(() => window.print(), 500); // Auto print
                    } else {
                        // Call livewire method syncTransaction manually
                        let livewireComponent = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                        let success = await livewireComponent.syncTransaction(this.cart, this.subtotal, this.tax, this.total);
                        if (success) {
                            this.clearCart();
                            setTimeout(() => window.print(), 500); // Auto print
                        }
                    }
                },
                
                async processSyncQueue() {
                    if (this.syncQueue.length === 0) return;
                    
                    let successfulIndexes = [];
                    let livewireComponent = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));

                    for (let i = 0; i < this.syncQueue.length; i++) {
                        let tx = this.syncQueue[i];
                        try {
                            let success = await livewireComponent.syncTransaction(tx.cart, tx.subtotal, tx.tax, tx.total);
                            if (success) {
                                successfulIndexes.push(i);
                            }
                        } catch (e) {
                            console.error('Sync failed for tx', i, e);
                        }
                    }
                    
                    this.syncQueue = this.syncQueue.filter((_, index) => !successfulIndexes.includes(index));
                    await localforage.setItem('pos_sync_queue', this.syncQueue);
                    
                    if (successfulIndexes.length > 0) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: [`${successfulIndexes.length} transaksi offline berhasil disinkronkan!`] }));
                    }
                },
                
                formatCurrency(num) {
                    return new Intl.NumberFormat('id-ID').format(num);
                }
            }));
        });
    </script>
    <!-- LocalForage for IndexedDB (Alpine is bundled with Livewire v3) -->
    <script src="https://cdn.jsdelivr.net/npm/localforage@1.10.0/dist/localforage.min.js"></script>
    <style>
        /* Custom scrollbar for better POS look */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af; 
        }
        /* Screen styles to hide print receipt */
        @media screen {
            #print-receipt {
                display: none !important;
            }
        }
        
        /* Print styles for thermal receipt */
        @media print {
            @page {
                margin: 0; /* Remove default browser margins */
            }
            body {
                background: white;
                color: black;
                -webkit-print-color-adjust: exact;
            }
            
            /* Hide all UI elements */
            header, 
            .left-panel-ui, 
            .right-panel-ui,
            .toast-notification {
                display: none !important;
            }
            
            /* Show only the receipt */
            #print-receipt {
                display: block !important;
                width: 58mm; /* Standard thermal receipt width */
                padding: 10px;
                font-family: monospace;
                font-size: 12px;
                margin: 0 auto;
            }
        }
    </style>
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800 overflow-hidden">
    <div id="app-wrapper" class="h-screen w-screen flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-primary-600 text-white shadow-md z-10 flex items-center justify-between px-6 py-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-primary-600 font-bold text-xl">
                    PFV
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight">Provit Farm Village</h1>
                    <p class="text-primary-100 text-xs">POS Kasir</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="font-semibold text-sm">Super Admin</p>
                    <p class="text-primary-100 text-xs">Outlet: Hasil Peternakan</p>
                </div>
                <a href="/admin" class="bg-primary-700 hover:bg-primary-800 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Dashboard Backoffice
                </a>
            </div>
        </header>

        <!-- Main Content (Livewire) -->
        <main class="flex-1 overflow-hidden relative">
            {{ $slot }}

            <!-- Toast Notification -->
            <div x-data="{ show: false, message: '' }" 
                 @notify.window="message = $event.detail[0]; show = true; setTimeout(() => show = false, 3000)"
                 x-show="show" 
                 x-transition.opacity.duration.300ms
                 class="absolute top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg font-bold flex items-center gap-2 z-50 toast-notification"
                 style="display: none;">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-text="message"></span>
            </div>
        </main>
    </div>

    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    console.log('Service Worker registered', reg);
                }).catch(err => {
                    console.log('Service Worker registration failed', err);
                });
            });
        }
    </script>
</body>
</html>
