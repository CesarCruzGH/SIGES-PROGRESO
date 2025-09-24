<?php

namespace App\Filament\Resources\MedicalRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MedicalRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('record_number')
                    ->label('N° de Expediente')
                    ->searchable(),
                // Mostramos el nombre del paciente a través de la relación
                TextColumn::make('patient.full_name')
                    ->label('Paciente')
                    ->searchable(),
                TextColumn::make('patient_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y')
                    ->sortable(),
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
