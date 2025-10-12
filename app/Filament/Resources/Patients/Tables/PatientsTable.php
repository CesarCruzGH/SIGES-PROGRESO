<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Enums\Locality;
use App\Filament\Resources\Patients\PatientResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\CreateAction;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Pacientes')
            ->description('Lista de pacientes registrados en el sistema')
            ->poll('10s')
            ->columns([
                TextColumn::make('medicalRecord.record_number')
                    ->label('Exp.')
                    ->sortable()
                    ->searchable()
                    ->tooltip('Número de expediente')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('full_name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn ($record) => $record->tutor?->full_name ? 'Tutor: ' . $record->tutor->full_name : null)
                    ->tooltip(fn ($record) => $record->address),

                TextColumn::make('age')
                    ->label('Edad')
                    ->sortable()
                    ->alignCenter()
                    ->suffix(' años')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('sex')
                    ->label('Sexo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match (strtolower($state)) {
                        'f' => 'Femenino',
                        'm' => 'Masculino',
                        // Si ya viene en texto, lo normalizamos:
                        'femenino' => 'Femenino',
                        'masculino' => 'Masculino',
                        default => ucfirst($state),
                    })
                    ->color(fn ($state): string => match (strtolower(trim((string) $state))) {
                        // Usa tokens de color soportados por Filament
                        'f', 'femenino' => 'pink',
                        'm', 'masculino' => 'indigo',
                        default => 'gray',
                    })
                    ->searchable(
                        query: fn ($query, string $search) => $query->where(function ($q) use ($search) {
                            $search = strtolower($search);
                            if (str_contains($search, 'fem')) {
                                $q->where('sex', 'F')->orWhere('sex', 'femenino');
                            } elseif (str_contains($search, 'mas')) {
                                $q->where('sex', 'M')->orWhere('sex', 'masculino');
                            } else {
                                $q->where('sex', 'like', "%{$search}%");
                            }
                        })
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('curp')
                    ->label('CURP')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('CURP copiada')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('locality')
                    ->label('Localidad')
                    ->badge()
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('contact_phone')
                    ->label('Teléfono')
                    ->formatStateUsing(fn ($state) => $state ? '+52 ' . $state : '—')
                    ->copyable()
                    ->copyMessage('Teléfono copiado')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('has_disability')
                    ->label('Discap.')
                    ->boolean()
                    ->tooltip(fn ($record) => $record->has_disability
                        ? ($record->disability_details ?: 'Con discapacidad registrada')
                        : 'Sin discapacidad')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Estatus')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'pending_review' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('full_name')
            ->recordUrl(fn ($record) => PatientResource::getUrl('view', ['record' => $record]))
            ->filters([
                SelectFilter::make('locality')
                    ->label('Localidad')
                    ->options(Locality::getOptions()),

                TernaryFilter::make('has_disability')
                    ->label('Discapacidad')
                    ->boolean(),

                SelectFilter::make('status')
                    ->label('Estatus')
                    ->options([
                        'active' => 'Activo',
                        'pending_review' => 'Pendiente de revisión',
                        'inactive' => 'Inactivo',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-s-eye')
                    ->tooltip('Ver ficha del paciente'),
                EditAction::make()
                    ->label('Editar')
                    ->tooltip('Editar datos del paciente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Crear Nuevo Paciente')
                    ->icon('heroicon-s-plus')
                    ->tooltip('Crear una nueva ficha de paciente'),
            ])
            ;
    }
}
