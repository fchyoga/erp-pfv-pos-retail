# POS Retail PFV - Task Tracking

## Phase 1: Foundation & Authentication
- [x] Inisialisasi project Laravel.
- [x] Konfigurasi database (`db_pfv_posretail` di MySQL MAMP).
- [x] Instalasi dan setup FilamentPHP (Admin Panel).
- [x] Setup Role & Permission (Spatie).
- [x] Buat Model, Migration, dan Filament Resource untuk `Outlet`.
- [x] Sesuaikan Model `User` (tambah relasi ke `Outlet`).
- [x] Buat Filament Resource untuk `User`.

## Phase 2: Product & Inventory Management (Backoffice)
- [x] Buat Model & Resource `Category`.
- [x] Buat Model & Resource `Product`.
- [x] Buat Model & Resource `Inventory` & `InventoryMovement`.

## Phase 3: POS Interface (Kasir)
- [x] Setup Livewire/Alpine untuk halaman kasir.
- [x] Desain antarmuka POS.
- [x] Fitur Keranjang Belanja (Cart).
- [x] Integrasi Scanner Barcode.

## Phase 4: Payment & Shift Management
- [x] Buat Model `Shift`.
- [x] Buat Model `Transaction`, `TransactionItem`, dan `Payment`.
- [x] Modul Buka/Tutup Shift di antarmuka Kasir.
- [x] Proses Checkout & Pembayaran.

## Phase 5: Hardware Integration & Offline Mode (PWA)
- [x] Buat manifest.json dan daftarkan di layout.
- [x] Buat file Service Worker (sw.js) untuk caching aset statis.
- [x] Integrasikan IndexedDB (menggunakan localForage atau custom JS) untuk master data produk.
- [x] Integrasikan logika antrean (Sync Queue) untuk transaksi offline.
- [x] Buat UI/Logika Sinkronisasi otomatis saat online.
- [x] Buat template cetak struk thermal (CSS @media print) dan trigger auto-print (Browser Print).

## Phase 6: Reporting & Security
- [x] Pembuatan Dashboard / Laporan.
- [x] Logika filter transaksi per kasir & outlet.
- [x] Keamanan & Enkripsi data lokal (menggunakan standar bawaan browser IndexedDB dan role-based access).

## Phase 7: Advanced POS & Payment System
- [x] Implementasi Hitung Kembalian Otomatis (Change Calculation) di UI POS.
- [x] Fitur Opsi Pembayaran (Cash, QRIS, Transfer).
- [x] Fitur Suspend/Hold dan Resume Transaksi Keranjang.
- [x] Kolom Catatan Item (Item Notes) di keranjang.
- [x] Pengaturan Pajak Dinamis & Persentase Diskon dari Panel Admin.

## Phase 8: Advanced Inventory Management
- [x] Pengurangan stok real-time (Sync Deduct).
- [x] Modul Koreksi Stok Manual (Stock Adjustment).
- [x] Modul Mutasi Stok Antar Outlet.
- [x] Peringatan Stok Menipis (Reorder Point & Low Stock Alert).
- [x] Manajemen Supplier & Tanggal Kadaluarsa (Batch Tracking).

## Phase 9: Comprehensive Reporting & Analytics
- [ ] Laporan Produk Terlaris (Best Seller).
- [ ] Laporan Slow Moving.
- [ ] Laporan Laba Bersih (Profit Margin).
- [ ] Laporan Ringkasan Metode Pembayaran.

## Phase 10: Security, Audit & Hardware Completeness
- [ ] Activity Log / Audit Trail.
- [ ] Persetujuan Void/Refund Transaksi.
- [ ] Multiple Print (Cashier Receipt, Barcode Label, Reprint).
- [ ] QR Scanner Support & Konfigurasi Laci/Display (opsional bila terintegrasi).
