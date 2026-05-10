<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LowStockWidget extends TableWidget
{
    protected static ?string $heading = 'Peringatan Stok Menipis';
    
    protected static ?int $sort = 2; // Position it below the stats

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => \App\Models\Product::query()->whereColumn('stock', '<=', 'reorder_point')->where('is_active', true))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Produk')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('reorder_point')
                    ->label('Batas Minimum'),
                \Filament\Tables\Columns\TextColumn::make('stock')
                    ->label('Stok Saat Ini')
                    ->weight('bold')
                    ->color('danger'),
            ])
            ->paginated(false);
    }
}
