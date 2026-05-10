<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;

use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Filament\Support\Icons\Heroicon;

class PaymentSummaryReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Ringkasan Pembayaran';

    protected string $view = 'filament.pages.reports.payment-summary-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Payment::query()
                    ->fromSub(
                        \App\Models\Payment::query()
                            ->select('method', DB::raw('MAX(id) as id'), DB::raw('COUNT(id) as total_transactions'), DB::raw('SUM(amount) as total_amount'))
                            ->groupBy('method'),
                        'payments'
                    )
            )
            ->columns([
                TextColumn::make('method')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                TextColumn::make('total_transactions')
                    ->label('Jumlah Transaksi')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total Pendapatan')
                    ->money('IDR')
                    ->sortable(),
            ]);
    }
}
