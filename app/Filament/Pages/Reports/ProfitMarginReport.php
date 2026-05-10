<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;

use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Filament\Support\Icons\Heroicon;

class ProfitMarginReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Profit Margin';

    protected string $view = 'filament.pages.reports.profit-margin-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransactionItem::query()
                    ->fromSub(
                        TransactionItem::query()
                            ->select(
                                'product_id', 
                                'product_name', 
                                DB::raw('MAX(id) as id'),
                                DB::raw('SUM(quantity) as total_sold'),
                                DB::raw('SUM(subtotal) as total_revenue'),
                                DB::raw('SUM(cost_price * quantity) as total_cost'),
                                DB::raw('SUM(subtotal) - SUM(cost_price * quantity) as gross_profit')
                            )
                            ->groupBy('product_id', 'product_name'),
                        'transaction_items'
                    )
            )
            ->defaultSort('gross_profit', 'desc')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('total_sold')
                    ->label('Qty Terjual')
                    ->sortable(),
                TextColumn::make('total_revenue')
                    ->label('Pendapatan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Total Modal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('gross_profit')
                    ->label('Gross Profit')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success'),
            ]);
    }
}
