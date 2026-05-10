<?php

namespace App\Filament\Resources\Shifts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('outlet_id')
                    ->required()
                    ->numeric(),
                TextInput::make('starting_cash')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('expected_ending_cash')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('actual_ending_cash')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('open'),
                DateTimePicker::make('opened_at')
                    ->required(),
                DateTimePicker::make('closed_at'),
            ]);
    }
}
