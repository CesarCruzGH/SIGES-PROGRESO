<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Fieldset as ComponentsFieldset;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ComponentsFieldset::make('Información personal')
                ->schema([
                    TextInput::make('name')->label('Nombre')->required(),
                    TextInput::make('email')->label('Email')->email()->required(),
                    // DateTimePicker::make('email_verified_at'),
                    TextInput::make('password')->label('Contraseña')->password()->required(),
                ])
                ->columns(1),
            ComponentsFieldset::make('Funciones')
                ->schema([
                    Select::make('role')
                        ->label('Función')
                        ->options([
                            'admin' => 'Administrador',
                            'doctor' => 'Doctor',
                            'enfermero' => 'Enfermero',
                            'recepcionista' => 'Recepcionista',
                            // agregar más roles aquí
            ])
            ->placeholder('Seleccione una función')
            ->required(),
                ])
                ->columns(1),
            ]);
    }
}
