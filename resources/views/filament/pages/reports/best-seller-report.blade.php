<x-filament-panels::page>
    @include('filament.pages.reports.report-styles')
    @php
        $stats = $this->getReportStats();
    @endphp

    <div class="report-stats-grid cols-3">
        <!-- Card 1 -->
        <div class="report-stat-card">
            <div class="report-stat-icon-wrapper bg-emerald">
                <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <div>
                <p class="report-stat-label">Total Terjual (Qty)</p>
                <p class="report-stat-value">{{ number_format($stats['total_sold'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="report-stat-card">
            <div class="report-stat-icon-wrapper bg-blue">
                <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="report-stat-label">Total Pendapatan</p>
                <p class="report-stat-value">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="report-stat-card">
            <div class="report-stat-icon-wrapper bg-violet">
                <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <div>
                <p class="report-stat-label">Jenis Produk Terlaris</p>
                <p class="report-stat-value">{{ number_format($stats['product_count'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
