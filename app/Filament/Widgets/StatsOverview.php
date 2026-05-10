<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Transaction;
use App\Models\Shift;
use Carbon\Carbon;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        
        $transactionQuery = Transaction::whereDate('created_at', Carbon::today())->where('payment_status', 'paid');
        $shiftQuery = Shift::whereNull('closed_at');

        // Apply multi-tenancy
        if (! $user->hasRole('super_admin')) {
            $transactionQuery->where('outlet_id', $user->outlet_id);
            $shiftQuery->where('outlet_id', $user->outlet_id);
        }

        $revenue = $transactionQuery->sum('total');
        $transactionCount = $transactionQuery->count();
        $activeShifts = $shiftQuery->count();

        return [
            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format($revenue, 0, ',', '.'))
                ->description('Total penjualan lunas hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Transaksi Hari Ini', $transactionCount)
                ->description('Jumlah struk dicetak')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
                
            Stat::make('Shift Aktif', $activeShifts)
                ->description('Kasir yang sedang beroperasi')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
}
