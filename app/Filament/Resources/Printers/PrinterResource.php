<?php

namespace App\Filament\Resources\Printers;

use App\Filament\Resources\Printers\Pages\ManagePrinters;
use App\Models\Printer;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrinterResource extends Resource
{
    protected static ?string $model = Printer::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('connection_type')
                    ->options(['browser' => 'Browser', 'network' => 'Network', 'bluetooth' => 'Bluetooth'])
                    ->default('browser')
                    ->required(),
                TextInput::make('ip_address'),
                TextInput::make('mac_address'),
                TextInput::make('receipt_header'),
                TextInput::make('receipt_footer'),
                Toggle::make('is_default')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('outlet.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('connection_type')
                    ->badge(),
                TextColumn::make('ip_address')
                    ->searchable(),
                TextColumn::make('mac_address')
                    ->searchable(),
                TextColumn::make('receipt_header')
                    ->searchable(),
                TextColumn::make('receipt_footer')
                    ->searchable(),
                IconColumn::make('is_default')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ManagePrinters::route('/'),
        ];
    }
}
