<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('log_name')
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('description')
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('subject_type')
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('causer.name')
                    ->label('Causer')
                    ->disabled(),
                \Filament\Forms\Components\KeyValue::make('properties.old')
                    ->label('Old Values')
                    ->disabled(),
                \Filament\Forms\Components\KeyValue::make('properties.attributes')
                    ->label('New Values')
                    ->disabled(),
            ]);
    }
}
