<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
//SOMATOMETRICO
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth; // <-- PASO 1: Importa el Facade
use Filament\Schemas\Components\Fieldset as ComponentsFieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('addSomatometricReading')
                ->label('Registrar Somatometría')
                ->icon('heroicon-o-heart')
                // 🔐 Restricción de acceso solo para enfermeros
                //->visible(fn () => Auth::user() && Auth::user()->role === 'enfermero')
                ->schema([
                    ComponentsFieldset::make('Presión Arterial')
                        ->schema([
                            TextInput::make('blood_pressure_systolic')->label('Sistólica')->numeric(),
                            TextInput::make('blood_pressure_diastolic')->label('Diastólica')->numeric(),
                        ])->columns(2),
                    ComponentsFieldset::make('Signos Vitales')
                        ->schema([
                            TextInput::make('heart_rate')->label('Frecuencia Cardíaca (ppm)')->numeric(),
                            TextInput::make('temperature')->label('Temperatura (°C)')->numeric(),
                        ])->columns(2),
                    ComponentsFieldset::make('Medidas Corporales')
                        ->schema([
                            TextInput::make('weight')->label('Peso (kg)')->numeric(),
                            TextInput::make('height')->label('Altura (m)')->numeric(),
                        ])->columns(2),
                    Textarea::make('observations')->label('Observaciones')->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    // El $this->getRecord() obtiene el paciente actual de la página
                    $patient = $this->getRecord();

                    // Asocia la lectura con el paciente y el usuario logueado
                    $patient->somatometricReadings()->create([
                        'user_id' => Auth::id(), // También es buena práctica usar Auth::id() aquí
                        ...$data, // El resto de los datos del formulario
                    ]);

                    Notification::make()
                        ->title('Lectura guardada correctamente')
                        ->success()
                        ->send();
                }),
        ];
    }
}
