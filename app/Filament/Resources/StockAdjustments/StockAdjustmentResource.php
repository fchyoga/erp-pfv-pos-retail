<?php

namespace App\Filament\Resources\StockAdjustments;

use App\Filament\Resources\StockAdjustments\Pages\ManageStockAdjustments;
use App\Models\StockAdjustment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationGroup(): ?string
    {
        return 'Inventory';
    }

    public static function getNavigationLabel(): string
    {
        return 'Stock Adjustments';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->required()
                    ->default(fn () => auth()->user()?->outlet_id),
                \Filament\Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
                \Filament\Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $product = \App\Models\Product::find($state);
                        if ($product) {
                            $set('expected_stock', $product->stock);
                            $set('actual_stock', $product->stock);
                        }
                    }),
                \Filament\Forms\Components\TextInput::make('expected_stock')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
                \Filament\Forms\Components\TextInput::make('actual_stock')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $expected = $get('expected_stock') ?? 0;
                        $set('difference', $state - $expected);
                    }),
                \Filament\Forms\Components\TextInput::make('difference')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),
                \Filament\Forms\Components\Select::make('reason')
                    ->options([
                        'hilang' => 'Hilang',
                        'rusak' => 'Rusak',
                        'kadaluarsa' => 'Kadaluarsa',
                        'salah_input' => 'Salah Input',
                        'lainnya' => 'Lainnya',
                    ])
                    ->required(),
                \Filament\Forms\Components\Textarea::make('notes')
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
                \Filament\Tables\Columns\TextColumn::make('product.name')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('outlet.name')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('expected_stock')
                    ->label('Sistem'),
                \Filament\Tables\Columns\TextColumn::make('actual_stock')
                    ->label('Aktual')
                    ->weight('bold'),
                \Filament\Tables\Columns\TextColumn::make('difference')
                    ->label('Selisih')
                    ->color(fn ($state) => $state < 0 ? 'danger' : ($state > 0 ? 'success' : 'gray')),
                \Filament\Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan'),
            ])
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
            'index' => ManageStockAdjustments::route('/'),
        ];
    }
}
