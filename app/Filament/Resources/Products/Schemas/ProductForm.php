<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('barcode'),
                TextInput::make('cost_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('Rp'),
                TextInput::make('selling_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('Rp'),
                FileUpload::make('photo')
                    ->disk('public')
                    ->image()
                    ->directory('products'),
                TextInput::make('stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->default(0)
                    ->required(),
                TextInput::make('reorder_point')
                    ->label('Batas Minimum Stok')
                    ->numeric()
                    ->default(5)
                    ->required(),
                DatePicker::make('expired_date')
                    ->label('Tanggal Kadaluarsa')
                    ->nullable(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
