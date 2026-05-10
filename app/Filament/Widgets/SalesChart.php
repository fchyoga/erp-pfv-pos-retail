<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Carbon\Carbon;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Penjualan (7 Hari Terakhir)';

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        $user = auth()->user();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('d M');
            
            $query = Transaction::whereDate('created_at', $date)->where('payment_status', 'paid');
            if (! $user->hasRole('super_admin')) {
                $query->where('outlet_id', $user->outlet_id);
            }
            $data[] = $query->sum('total');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan (Rp)',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
