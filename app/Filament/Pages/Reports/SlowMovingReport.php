<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;

use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Filament\Support\Icons\Heroicon;

class SlowMovingReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $title = 'Produk Slow Moving';

    protected string $view = 'filament.pages.reports.slow-moving-report';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
                    ->select('products.id', 'products.name', 'products.stock', DB::raw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold'))
                    ->groupBy('products.id', 'products.name', 'products.stock')
            )
            ->defaultSort('total_sold', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('stock')
                    ->label('Stok Tersedia')
                    ->sortable(),
                TextColumn::make('total_sold')
                    ->label('Total Terjual')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'danger' : 'warning'),
            ]);
    }
}
