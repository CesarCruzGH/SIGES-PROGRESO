<?php

namespace App\Filament\Resources\Patients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medical_record_number')
                    ->label('N° Expediente')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Nombre Completo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('age')
                    ->label('Edad'),
                TextColumn::make('patient_type')
                    ->label('Tipo Paciente')
                    ->badge(), // El badge le da un estilo visual
                TextColumn::make('tutor.full_name') // <-- Usando la relación
                    ->label('Tutor Asignado')
                    ->searchable(),
                TextColumn::make('attendingDoctor.name') // <-- Usando la relación
                    ->label('Médico')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
