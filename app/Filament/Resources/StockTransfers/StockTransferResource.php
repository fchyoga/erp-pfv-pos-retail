<?php

namespace App\Filament\Resources\StockTransfers;

use App\Filament\Resources\StockTransfers\Pages\ManageStockTransfers;
use App\Models\StockTransfer;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockTransferResource extends Resource
{
    protected static ?string $model = StockTransfer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    public static function getNavigationGroup(): ?string
    {
        return 'Inventory';
    }

    public static function getNavigationLabel(): string
    {
        return 'Stock Transfers';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('from_outlet_id')
                    ->label('Outlet Asal')
                    ->relationship('fromOutlet', 'name')
                    ->required()
                    ->default(fn () => auth()->user()?->outlet_id),
                \Filament\Forms\Components\Select::make('to_outlet_id')
                    ->label('Outlet Tujuan')
                    ->relationship('toOutlet', 'name')
                    ->required(),
                \Filament\Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
                \Filament\Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable(),
                \Filament\Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                \Filament\Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('completed')
                    ->required(),
                \Filament\Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('fromOutlet.name')
                    ->label('Dari'),
                \Filament\Tables\Columns\TextColumn::make('toOutlet.name')
                    ->label('Ke'),
                \Filament\Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->weight('bold'),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStockTransfers::route('/'),
        ];
    }
}
