<x-filament-panels::page>
    @include('filament.pages.reports.report-styles')
    @php
        $stats = $this->getReportStats();
    @endphp

    <div class="report-stats-grid cols-4">
        <!-- Card 1 -->
        <div class="report-stat-card">
            <div class="report-stat-icon-wrapper bg-emerald">
                <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="report-stat-label">Total Penjualan</p>
                <p class="report-stat-value">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="report-stat-card">
            <div class="report-stat-icon-wrapper bg-blue">
                <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <div>
                <p class="report-stat-label">Total Transaksi</p>
                <p class="report-stat-value">{{ number_format($stats['total_transactions'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="report-stat-card">
            <div class="report-stat-icon-wrapper bg-orange">
                <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 002.828 0L21 9"></path></svg>
            </div>
            <div>
                <p class="report-stat-label">Total Diskon</p>
                <p class="report-stat-value">Rp {{ number_format($stats['total_discount'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="report-stat-card">
            <div class="report-stat-icon-wrapper bg-gray">
                <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2-2 4 4m0-7v3h-3M3 17V7a2 2 0 012-2h6l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
            </div>
            <div>
                <p class="report-stat-label">Total Pajak</p>
                <p class="report-stat-value">Rp {{ number_format($stats['total_tax'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
