<x-filament-panels::page>
    @include('filament.pages.reports.report-styles')
    @php
        $stats = $this->getReportStats();
    @endphp

    <div class="report-stats-grid cols-3">
        <!-- Card 1 -->
        <div class="report-stat-card">
            <div class="report-stat-icon-wrapper bg-red">
                <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <p class="report-stat-label">Produk Slow Moving</p>
                <p class="report-stat-value">{{ number_format($stats['total_products'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="report-stat-card">
            <div class="report-stat-icon-wrapper bg-orange">
                <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div>
                <p class="report-stat-label">Stok Mengendap</p>
                <p class="report-stat-value">{{ number_format($stats['total_stock'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="report-stat-card">
            <div class="report-stat-icon-wrapper bg-blue">
                <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <div>
                <p class="report-stat-label">Total Terjual (Qty)</p>
                <p class="report-stat-value">{{ number_format($stats['total_sold'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
