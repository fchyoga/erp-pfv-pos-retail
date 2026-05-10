<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl & Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('log_name')
                    ->label('Log Name')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subjek')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('causer.name')
                    ->label('Dilakukan Oleh')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->toolbarActions([
                // Empty toolbar for audit logs
            ]);
    }
}
