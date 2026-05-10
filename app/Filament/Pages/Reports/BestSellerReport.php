<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;

use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;

class BestSellerReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Produk Terlaris';

    protected string $view = 'filament.pages.reports.best-seller-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransactionItem::query()
                    ->fromSub(
                        TransactionItem::query()
                            ->select('product_id', 'product_name', DB::raw('MAX(id) as id'), DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
                            ->groupBy('product_id', 'product_name'),
                        'transaction_items'
                    )
            )
            ->defaultSort('total_sold', 'desc')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('total_sold')
                    ->label('Total Terjual (Qty)')
                    ->sortable(),
                TextColumn::make('total_revenue')
                    ->label('Total Pendapatan')
                    ->money('IDR')
                    ->sortable(),
            ]);
    }
}
