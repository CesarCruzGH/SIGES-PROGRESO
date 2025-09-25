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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\Auth; // <-- PASO 1: Importa el Facade


class SomatometricReadingsRelationManager extends RelationManager
{
    protected static string $relationship = 'somatometricReadings';
    protected static ?string $title = 'Historial de Somatometría';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('blood_pressure_systolic')->label('Presión Sistólica (mmHg)')->numeric(),
                TextInput::make('blood_pressure_diastolic')->label('Presión Diastólica (mmHg)')->numeric(),
                TextInput::make('heart_rate')->label('Frecuencia Cardíaca (lpm)')->numeric(),
                TextInput::make('temperature')->label('Temperatura (°C)')->numeric(),
                TextInput::make('weight')->label('Peso (kg)')->numeric(),
                TextInput::make('height')->label('Talla (m)')->numeric(),
                Textarea::make('observations')->label('Observaciones')->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ticket_number')
            ->columns([
                TextColumn::make('created_at')
                ->label('Fecha de Toma')
                ->dateTime('d/m/Y H:i a')
                ->sortable(),
                TextColumn::make('blood_pressure')
                    ->label('Presión Arterial')
                    ->getStateUsing(fn ($record) => $record->blood_pressure_systolic . '/' . $record->blood_pressure_diastolic . ' mmHg'),
                TextColumn::make('weight')->label('Peso')->suffix(' kg'),
                TextColumn::make('height')->label('Talla')->suffix(' m'),
                TextColumn::make('user.name')->label('Registrado por'), // Asumiendo que user_id apunta a quien lo registró
            ])
            ->headerActions([
                CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id(); // Asigna al usuario actual
                    return $data;
                }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}