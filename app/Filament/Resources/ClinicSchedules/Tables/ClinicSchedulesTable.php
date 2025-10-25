<?php

namespace App\Filament\Resources\ClinicSchedules\Tables;

use App\Enums\Shift;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ClinicSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'asc')
            ->columns([
                TextColumn::make('clinic_name')
                    ->label('Consultorio')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Médico')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('service.name')
                    ->label('Servicio')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('shift')
                    ->label('Turno')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        return ucfirst(is_string($state) ? $state : ($state?->value ?? ''));
                    })
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Activo')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('shift')
                    ->label('Turno')
                    ->options(Shift::getOptions()),
                TernaryFilter::make('is_active')
                    ->label('Activo'),
                SelectFilter::make('service_id')
                    ->label('Servicio')
                    ->relationship('service', 'name'),
                SelectFilter::make('user_id')
                    ->label('Médico')
                    ->relationship('user', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
