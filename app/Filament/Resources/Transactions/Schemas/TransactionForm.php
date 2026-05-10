<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('outlet_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('shift_id')
                    ->numeric(),
                TextInput::make('invoice_number')
                    ->required(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('payment_status')
                    ->required()
                    ->default('unpaid'),
                TextInput::make('status')
                    ->required()
                    ->default('completed'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
