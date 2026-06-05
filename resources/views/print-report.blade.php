<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - PFV Retail</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background: white;
                color: black;
                font-size: 12px;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 10mm 15mm 10mm;
            }
        }
    </style>
</head>
<body class="bg-gray-100 p-8 font-sans antialiased text-gray-800">
    <!-- Action bar for screen view -->
    <div class="max-w-4xl mx-auto mb-6 p-4 bg-white rounded-lg shadow-sm flex justify-between items-center no-print border border-gray-200">
        <div>
            <h1 class="font-bold text-gray-700">Laporan Siap Dicetak</h1>
            <p class="text-xs text-gray-500">Gunakan dialog printer browser untuk menyimpan sebagai PDF atau cetak ke printer fisik.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.close()" class="px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 text-sm">Tutup</button>
            <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 text-sm shadow-md shadow-emerald-600/20">Cetak Laporan</button>
        </div>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-10 rounded-xl shadow-sm border border-gray-200 print:shadow-none print:border-none print:p-0">
        <!-- Logo and Brand -->
        <div class="flex justify-between items-center border-b-2 border-emerald-500 pb-6 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('logo.png') }}" class="h-14 w-auto object-contain" alt="Provit Farm Village Logo">
                <div>
                    <h1 class="text-3xl font-black text-emerald-600 tracking-tight">PFV Retail</h1>
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Wisata Provit Farm Village</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-gray-700">Laporan Keuangan</p>
                <p class="text-xs text-gray-500">Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- Report Details -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                @switch($type)
                    @case('best-seller')
                        Laporan Produk Terlaris
                        @break
                    @case('payment-summary')
                        Laporan Ringkasan Pembayaran
                        @break
                    @case('profit-margin')
                        Laporan Profit Margin Produk
                        @break
                    @case('slow-moving')
                        Laporan Produk Slow Moving
                        @break
                    @case('sales')
                        Laporan Penjualan & Transaksi
                        @break
                @endswitch
            </h2>
            <div class="grid grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded-lg border border-gray-150">
                <div>
                    <span class="text-gray-500">Periode:</span>
                    <span class="font-bold text-gray-700 ml-1">
                        @if($startDate || $endDate)
                            {{ $startDate ? date('d-m-Y', strtotime($startDate)) : 'Awal' }} s/d {{ $endDate ? date('d-m-Y', strtotime($endDate)) : 'Hari Ini' }}
                        @else
                            Semua Waktu
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-gray-500">Outlet:</span>
                    <span class="font-bold text-gray-700 ml-1">{{ $outlet ? $outlet->name : 'Semua Outlet' }}</span>
                </div>
            </div>
        </div>

        <!-- Table Data -->
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-xs font-bold border-b border-gray-200">
                    @switch($type)
                        @case('best-seller')
                            <th class="py-3.5 px-4 rounded-l-lg">Nama Produk</th>
                            <th class="py-3.5 px-4 text-center">Total Terjual (Qty)</th>
                            <th class="py-3.5 px-4 text-right rounded-r-lg">Total Pendapatan</th>
                            @break
                        @case('payment-summary')
                            <th class="py-3.5 px-4 rounded-l-lg">Metode Pembayaran</th>
                            <th class="py-3.5 px-4 text-center">Jumlah Transaksi</th>
                            <th class="py-3.5 px-4 text-right rounded-r-lg">Total Pendapatan</th>
                            @break
                        @case('profit-margin')
                            <th class="py-3.5 px-4 rounded-l-lg">Nama Produk</th>
                            <th class="py-3.5 px-4 text-center">Qty Terjual</th>
                            <th class="py-3.5 px-4 text-right">Pendapatan</th>
                            <th class="py-3.5 px-4 text-right">Total Modal</th>
                            <th class="py-3.5 px-4 text-right rounded-r-lg">Gross Profit</th>
                            @break
                        @case('slow-moving')
                            <th class="py-3.5 px-4 rounded-l-lg">Nama Produk</th>
                            <th class="py-3.5 px-4 text-center">Stok Tersedia</th>
                            <th class="py-3.5 px-4 text-center rounded-r-lg">Total Terjual</th>
                            @break
                        @case('sales')
                            <th class="py-3.5 px-4 rounded-l-lg">Invoice</th>
                            <th class="py-3.5 px-4">Tanggal</th>
                            <th class="py-3.5 px-4">Outlet</th>
                            <th class="py-3.5 px-4">Kasir</th>
                            <th class="py-3.5 px-4">Metode</th>
                            <th class="py-3.5 px-4 text-right">Subtotal</th>
                            <th class="py-3.5 px-4 text-right">Diskon</th>
                            <th class="py-3.5 px-4 text-right">Pajak</th>
                            <th class="py-3.5 px-4 text-right">Total</th>
                            <th class="py-3.5 px-4 text-center rounded-r-lg">Status</th>
                            @break
                    @endswitch
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $record)
                    <tr class="border-b border-gray-100 hover:bg-gray-50/50">
                        @switch($type)
                            @case('best-seller')
                                <td class="py-3 px-4 font-medium text-gray-900">{{ $record->product_name }}</td>
                                <td class="py-3 px-4 text-center text-gray-600">{{ $record->total_sold }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-900">Rp {{ number_format($record->total_revenue, 0, ',', '.') }}</td>
                                @break
                            @case('payment-summary')
                                <td class="py-3 px-4 font-bold text-emerald-700 uppercase">{{ $record->method }}</td>
                                <td class="py-3 px-4 text-center text-gray-600">{{ $record->total_transactions }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-900">Rp {{ number_format($record->total_amount, 0, ',', '.') }}</td>
                                @break
                            @case('profit-margin')
                                <td class="py-3 px-4 font-medium text-gray-900">{{ $record->product_name }}</td>
                                <td class="py-3 px-4 text-center text-gray-600">{{ $record->total_sold }}</td>
                                <td class="py-3 px-4 text-right text-gray-700">Rp {{ number_format($record->total_revenue, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-gray-500">Rp {{ number_format($record->total_cost, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold {{ $record->gross_profit < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    Rp {{ number_format($record->gross_profit, 0, ',', '.') }}
                                </td>
                                @break
                            @case('slow-moving')
                                <td class="py-3 px-4 font-medium text-gray-900">{{ $record->name }}</td>
                                <td class="py-3 px-4 text-center text-gray-600">{{ $record->stock }}</td>
                                <td class="py-3 px-4 text-center font-semibold {{ $record->total_sold == 0 ? 'text-red-500 bg-red-50' : 'text-amber-600 bg-amber-50' }} rounded-lg max-w-[80px] mx-auto py-1">{{ $record->total_sold }}</td>
                                @break
                            @case('sales')
                                <td class="py-3 px-4 font-bold text-gray-900 text-xs">{{ $record->invoice_number }}</td>
                                <td class="py-3 px-4 text-gray-600 text-xs">{{ $record->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 px-4 text-gray-600 text-xs">{{ $record->outlet?->name ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-600 text-xs">{{ $record->user?->name ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-600 text-xs font-semibold">{{ strtoupper($record->payment?->method ?? '-') }}</td>
                                <td class="py-3 px-4 text-right text-gray-700 text-xs">Rp {{ number_format($record->subtotal, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-gray-500 text-xs">Rp {{ number_format($record->discount, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right text-gray-500 text-xs">Rp {{ number_format($record->tax, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-right font-bold text-gray-900 text-xs">Rp {{ number_format($record->total, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-center text-xs">
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $record->status === 'completed' ? 'bg-green-100 text-green-800' : ($record->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                                @break
                        @endswitch
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-gray-400">Tidak ada data untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary section for revenue reports -->
        @if(count($records) > 0 && in_array($type, ['best-seller', 'payment-summary', 'profit-margin', 'sales']))
            <div class="mt-8 border-t-2 border-gray-100 pt-6">
                <div class="w-64 ml-auto text-sm space-y-2">
                    @if($type === 'sales')
                        <div class="flex justify-between text-gray-500 font-medium">
                            <span>Total Subtotal:</span>
                            <span class="font-bold text-gray-900">
                                Rp {{ number_format($records->sum('subtotal'), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-gray-500 font-medium">
                            <span>Total Diskon:</span>
                            <span class="font-bold text-gray-900">
                                Rp {{ number_format($records->sum('discount'), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-gray-500 font-medium">
                            <span>Total Pajak:</span>
                            <span class="font-bold text-gray-900">
                                Rp {{ number_format($records->sum('tax'), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between text-emerald-600 font-bold border-t border-gray-200 pt-2 text-base">
                            <span>Total Penjualan:</span>
                            <span>
                                Rp {{ number_format($records->sum('total'), 0, ',', '.') }}
                            </span>
                        </div>
                    @else
                        <div class="flex justify-between text-gray-500 font-medium">
                            <span>Total Pendapatan:</span>
                            <span class="font-bold text-gray-900">
                                Rp {{ number_format($records->sum(fn($r) => $type === 'payment-summary' ? $r->total_amount : $r->total_revenue), 0, ',', '.') }}
                            </span>
                        </div>
                        @if($type === 'profit-margin')
                            <div class="flex justify-between text-gray-500 font-medium">
                                <span>Total Modal:</span>
                                <span class="font-bold text-gray-900">
                                    Rp {{ number_format($records->sum('total_cost'), 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between text-emerald-600 font-bold border-t border-gray-200 pt-2 text-base">
                                <span>Total Profit:</span>
                                <span>
                                    Rp {{ number_format($records->sum('gross_profit'), 0, ',', '.') }}
                                </span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="mt-16 text-center text-xs text-gray-400 border-t border-gray-100 pt-6">
            <p>Laporan ini digenerate secara otomatis oleh sistem **PFV Retail**</p>
            <p class="mt-1">&copy; {{ date('Y') }} Provit Farm Village. Seluruh Hak Cipta Dilindungi.</p>
        </div>
    </div>

    <script>
        // Automatically trigger print dialog on page load
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 600);
        }
    </script>
</body>
</html>
