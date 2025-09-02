<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                
                TextInput::make('Nombre')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                #DateTimePicker::make('email_verified_at'),
                TextInput::make('Contraseña')
                    ->password()
                    ->required(),
            ]);
    }
}
