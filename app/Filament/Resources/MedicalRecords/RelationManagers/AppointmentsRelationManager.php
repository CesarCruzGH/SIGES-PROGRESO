<?php

namespace App\Filament\Resources\MedicalRecords\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;


class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';
    protected static ?string $title = 'Historial de Visitas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Aquí configuraremos el formulario para crear/editar una visita
                TextInput::make('ticket_number')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ticket_number')
            ->columns([
                // Aquí configuraremos las columnas de la tabla de visitas
                TextColumn::make('ticket_number'),
                TextColumn::make('reason_for_visit'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}