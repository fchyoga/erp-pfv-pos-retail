<?php

namespace App\Filament\Resources\Shifts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShiftsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('outlet.name')
                    ->label('Outlet')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('starting_cash')
                    ->label('Modal Awal')
                    ->numeric()
                    ->sortable()
                    ->money('IDR', locale: 'id'),
                TextColumn::make('expected_ending_cash')
                    ->label('Ekspektasi Saldo')
                    ->numeric()
                    ->sortable()
                    ->money('IDR', locale: 'id'),
                TextColumn::make('actual_ending_cash')
                    ->label('Saldo Aktual')
                    ->numeric()
                    ->sortable()
                    ->money('IDR', locale: 'id'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'success',
                        'closed' => 'secondary',
                        default => 'warning',
                    })
                    ->searchable(),
                TextColumn::make('opened_at')
                    ->label('Waktu Buka')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Waktu Tutup')
                    ->dateTime()
                    ->sortable(),
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
