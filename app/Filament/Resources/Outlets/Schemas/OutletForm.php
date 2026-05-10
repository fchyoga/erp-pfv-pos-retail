<?php

namespace App\Filament\Resources\Outlets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OutletForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('tax_percentage')
                    ->numeric()
                    ->default(11)
                    ->suffix('%')
                    ->label('Pajak (Tax)'),
                TextInput::make('discount_percentage')
                    ->numeric()
                    ->default(0)
                    ->suffix('%')
                    ->label('Diskon Otomatis (Global)'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
