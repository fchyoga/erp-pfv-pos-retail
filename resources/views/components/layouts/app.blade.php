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
            Alpine.data('posApp', (taxPct = 11, discountPct = 0) => ({
                mobileTab: 'products',
                get products() { 
                    const el = document.getElementById('pos-root-element');
                    return el ? JSON.parse(el.dataset.products) : {}; 
                },
                taxPercentage: taxPct,
                discountPercentage: discountPct,
                cart: [],
                syncQueue: [],
                subtotal: 0,
                tax: 0,
                discountAmount: 0,
                transactionDiscountInput: 0,
                transactionDiscountType: 'fixed',
                total: 0,
                isOffline: !navigator.onLine,
                lastReceipt: null,
                printingRekap: false,
                
                // Payment State
                showCheckoutModal: false,
                paymentMethod: 'cash',
                cashAmount: '',
                changeAmount: 0,
                
                // Suspend State
                suspendedCarts: [],
                showSuspendModal: false,
                suspendLabel: '',
                showSuspendListModal: false,
                
                // Discount Modals State
                showItemDiscountModal: false,
                selectedCartIndex: null,
                tempItemDiscountType: 'fixed',
                tempItemDiscountAmount: 0,
                showTransactionDiscountModal: false,
                tempTxDiscountType: 'fixed',
                tempTxDiscountAmount: 0,
                
                async init() {
                    this.cart = await localforage.getItem('pos_cart') || [];
                    this.syncQueue = await localforage.getItem('pos_sync_queue') || [];
                    this.suspendedCarts = await localforage.getItem('pos_suspended_carts') || [];
                    this.calculateTotals();
                    
                    this.$watch('cart', (val) => {
                        localforage.setItem('pos_cart', val);
                        this.calculateTotals();
                    }, { deep: true });
                    
                    this.$watch('suspendedCarts', (val) => {
                        localforage.setItem('pos_suspended_carts', val);
                    }, { deep: true });
                    
                    this.$watch('cashAmount', (val) => {
                        this.calculateChange();
                    });
                    
                    this.$watch('paymentMethod', (val) => {
                        this.calculateChange();
                    });
                    
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
                    let sub = 0;
                    this.cart.forEach(item => {
                        let itemTotal = item.price * item.qty;
                        let itemDiscount = 0;
                        if (item.discountType === 'percentage') {
                            itemDiscount = itemTotal * (item.discountAmount / 100);
                        } else {
                            itemDiscount = parseFloat(item.discountAmount) || 0;
                        }
                        // item discount shouldn't exceed item total
                        itemDiscount = Math.min(itemDiscount, itemTotal);
                        sub += (itemTotal - itemDiscount);
                    });
                    
                    this.subtotal = sub;
                    
                    let txDiscount = 0;
                    if (this.transactionDiscountType === 'percentage') {
                        txDiscount = this.subtotal * (this.transactionDiscountInput / 100);
                    } else {
                        txDiscount = parseFloat(this.transactionDiscountInput) || 0;
                    }
                    txDiscount = Math.min(txDiscount, this.subtotal);
                    
                    this.discountAmount = txDiscount;
                    
                    let afterDiscount = this.subtotal - this.discountAmount;
                    this.tax = afterDiscount * (this.taxPercentage / 100);
                    this.total = afterDiscount + this.tax;
                    this.calculateChange();
                },
                
                calculateChange() {
                    if (this.paymentMethod === 'cash') {
                        let amount = parseFloat(this.cashAmount) || 0;
                        this.changeAmount = amount - this.total;
                    } else {
                        this.changeAmount = 0;
                    }
                },
                
                setExactAmount() {
                    this.cashAmount = this.total;
                },
                
                quickAmount(add) {
                    let current = parseFloat(this.cashAmount) || 0;
                    this.cashAmount = current + add;
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
                            qty: 1,
                            note: '',
                            discountType: 'fixed',
                            discountAmount: 0
                        });
                    }
                },
                
                openItemDiscountModal(index) {
                    this.selectedCartIndex = index;
                    this.tempItemDiscountType = this.cart[index].discountType || 'fixed';
                    this.tempItemDiscountAmount = this.cart[index].discountAmount || 0;
                    this.showItemDiscountModal = true;
                },
                
                applyItemDiscount() {
                    if (this.selectedCartIndex !== null) {
                        this.cart[this.selectedCartIndex].discountType = this.tempItemDiscountType;
                        this.cart[this.selectedCartIndex].discountAmount = parseFloat(this.tempItemDiscountAmount) || 0;
                        this.calculateTotals();
                    }
                    this.showItemDiscountModal = false;
                },
                
                openTransactionDiscountModal() {
                    this.tempTxDiscountType = this.transactionDiscountType;
                    this.tempTxDiscountAmount = this.transactionDiscountInput;
                    this.showTransactionDiscountModal = true;
                },
                
                applyTransactionDiscount() {
                    this.transactionDiscountType = this.tempTxDiscountType;
                    this.transactionDiscountInput = parseFloat(this.tempTxDiscountAmount) || 0;
                    this.calculateTotals();
                    this.showTransactionDiscountModal = false;
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
                
                suspendCart() {
                    if (this.cart.length === 0) return;
                    if (!this.suspendLabel) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: ['Masukkan nama/label pelanggan!'] }));
                        return;
                    }
                    
                    this.suspendedCarts.push({
                        label: this.suspendLabel,
                        cart: JSON.parse(JSON.stringify(this.cart)),
                        timestamp: new Date().toLocaleString('id-ID')
                    });
                    
                    this.clearCart();
                    this.showSuspendModal = false;
                    this.suspendLabel = '';
                    window.dispatchEvent(new CustomEvent('notify', { detail: ['Transaksi berhasil ditahan (Suspend).'] }));
                },
                
                resumeCart(index) {
                    if (this.cart.length > 0) {
                        if (!confirm('Keranjang saat ini tidak kosong. Ganti dengan transaksi ini?')) {
                            return;
                        }
                    }
                    this.cart = JSON.parse(JSON.stringify(this.suspendedCarts[index].cart));
                    this.suspendedCarts.splice(index, 1);
                    this.showSuspendListModal = false;
                },
                
                deleteSuspendedCart(index) {
                    if (confirm('Hapus transaksi tertunda ini?')) {
                        this.suspendedCarts.splice(index, 1);
                    }
                },
                
                openCheckout() {
                    if (this.cart.length === 0) return;
                    this.paymentMethod = 'cash';
                    this.cashAmount = '';
                    this.changeAmount = -this.total;
                    this.showCheckoutModal = true;
                },
                
                async processCheckout() {
                    if (this.paymentMethod === 'cash' && this.changeAmount < 0) {
                        window.dispatchEvent(new CustomEvent('notify', { detail: ['Uang yang dibayarkan kurang!'] }));
                        return;
                    }
                    
                    this.showCheckoutModal = false;
                    
                    let invoiceNumber = 'INV-' + new Date().toISOString().replace(/[-:T.]/g, '').substring(0, 14);

                    this.lastReceipt = {
                        invoice: invoiceNumber,
                        cart: JSON.parse(JSON.stringify(this.cart)),
                        subtotal: this.subtotal,
                        discount: this.discountAmount,
                        discountType: this.transactionDiscountType,
                        tax: this.tax,
                        total: this.total,
                        paymentMethod: this.paymentMethod,
                        cashAmount: this.paymentMethod === 'cash' ? (parseFloat(this.cashAmount) || 0) : this.total,
                        changeAmount: this.changeAmount,
                        date: new Date().toLocaleString('id-ID')
                    };

                    if (this.isOffline) {
                        this.syncQueue.push({
                            id: Date.now(),
                            cart: JSON.parse(JSON.stringify(this.cart)),
                            subtotal: this.subtotal,
                            discount: this.discountAmount,
                            discountType: this.transactionDiscountType,
                            tax: this.tax,
                            total: this.total,
                            paymentMethod: this.paymentMethod,
                            changeAmount: this.changeAmount,
                            timestamp: new Date().toISOString()
                        });
                        await localforage.setItem('pos_sync_queue', this.syncQueue);
                        this.clearCart();
                        window.dispatchEvent(new CustomEvent('notify', { detail: ['Tersimpan Offline. Menunggu Sinkronisasi.'] }));
                        setTimeout(() => window.print(), 500); // Auto print
                    } else {
                        // Call livewire method syncTransaction manually
                        let livewireComponent = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                        let success = await livewireComponent.syncTransaction(this.cart, this.subtotal, this.discountAmount, this.transactionDiscountType, this.tax, this.total, this.paymentMethod, this.changeAmount);
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
                            let success = await livewireComponent.syncTransaction(tx.cart, tx.subtotal, tx.discount || 0, tx.discountType || 'fixed', tx.tax, tx.total, tx.paymentMethod || 'cash', tx.changeAmount || 0);
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
            .toast-notification,
            .fixed.inset-0, /* Livewire & Alpine Modals */
            [role="dialog"] {
                display: none !important;
            }
            
            /* Remove overflow restrictions for printing */
            #app-wrapper, body, main, #pos-root-element {
                height: auto !important;
                overflow: visible !important;
                display: block !important;
            }
            
            /* Print elements */
            #print-receipt, #print-rekap {
                width: 58mm; /* Standard thermal receipt width */
                padding: 0;
                margin: 0;
                font-family: monospace;
                font-size: 12px;
                position: absolute;
                top: 0;
                left: 0;
            }
            
            .is-printing-rekap #print-receipt {
                display: none !important;
            }
            
            #pos-root-element:not(.is-printing-rekap) #print-rekap {
                display: none !important;
            }
        }
        
        @media screen {
            #print-receipt, #print-rekap {
                display: none !important;
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
