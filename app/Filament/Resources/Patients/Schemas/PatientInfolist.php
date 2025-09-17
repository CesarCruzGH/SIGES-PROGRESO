<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

use Illuminate\Support\Facades\Auth; // <-- PASO 1: Importa el Facade
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;

//SOMATOMETRICO
use Filament\Actions\Action;
use Filament\Schemas\Components\Fieldset as ComponentsFieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\Patient; // <-- Importante: Añade el modelo


class PatientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                Section::make('Información Personal')
                ->columns()
                ->schema([
                    TextEntry::make('full_name')->label('Nombre Completo'),
                    TextEntry::make('medical_record_number')->label('N° Expediente'),
                    TextEntry::make('age')->label('Edad'),
                    TextEntry::make('date_of_birth')->label('Fecha de Nacimiento')->date(),
                    TextEntry::make('curp')->label('CURP'),
                    TextEntry::make('sex')->label('Sexo'),
                    TextEntry::make('locality')->label('Localidad'),
                ]),

                Section::make('Clasificación y Detalles')
                ->columns()
                ->schema([
                    TextEntry::make('patient_type')->label('Tipo de Paciente')->badge(),
                    TextEntry::make('employee_status')->label('Estatus Empleado')->badge(),
                    TextEntry::make('attendingDoctor.name')->label('Médico que Atiende'),
                    TextEntry::make('visit_type')->label('Tipo de Visita'),
                    IconEntry::make('has_disability')->label('Tiene Discapacidad')->boolean(),
                    TextEntry::make('disability_details')->label('Detalles de Discapacidad'),
                ]),

                Section::make('Información del Tutor')
                ->visible(fn ($record) => $record->tutor()->exists())
                ->columns()
                ->schema([
                    TextEntry::make('tutor.full_name')->label('Nombre del Tutor'),
                    TextEntry::make('tutor.relationship')->label('Parentesco'),
                    TextEntry::make('tutor.phone_number')->label('Teléfono del Tutor'),
                    TextEntry::make('tutor.address')->label('Dirección del Tutor'),
                ]),
//ACTION MAKE CREAR SOMATOMETRICO
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
                ->action(function (array $data, Patient $record) {
                    // Asocia la lectura con el paciente y el usuario logueado
                    $record->somatometricReadings()->create([
                        'user_id' => Auth::id(), // También es buena práctica usar Auth::id() aquí
                        ...$data, // El resto de los datos del formulario
                    ]);

                    Notification::make()
                        ->title('Lectura guardada correctamente')
                        ->success()
                        ->send();
                }),
                Section::make('Historial Somatométrico')
                    ->icon('heroicon-s-clipboard-document-list')
                    ->collapsible()
                    ->columnSpanFull()
                    
                    // Muestra esta sección solo si hay lecturas registradas
                    ->visible(fn ($record) => $record->somatometricReadings()->exists())
                    ->schema([
                        RepeatableEntry::make('somatometricReadings')
                            // Ordena las lecturas de la más reciente a la más antigua
                            ->label('Historial Somatométrico')
                            ->columns(4)
                            ->schema([
                                        TextEntry::make('created_at')
                                            ->label('Fecha de Registro')
                                            ->dateTime('d/m/Y H:i A') // Formato claro de fecha y hora
                                            ->icon('heroicon-s-calendar'),

                                            TextEntry::make('user.name')
                                                ->label('Registrado por')
                                                ->icon('heroicon-s-user'),
                                                 
                                        TextEntry::make('blood_pressure')
                                            ->label('Presión Arterial')
                                            ->icon('heroicon-s-heart')
                                            ->getStateUsing(fn ($record) => $record->blood_pressure_systolic . '/' . $record->blood_pressure_diastolic),

                                        TextEntry::make('heart_rate')
                                            ->label('Frec. Cardíaca')
                                            ->getStateUsing(fn ($record) => $record->heart_rate . ' ppm'),

                                        // Puedes seguir añadiendo el resto de campos aquí
                                        TextEntry::make('temperature')->label('Temperatura')->getStateUsing(fn ($record) => $record->temperature . ' °C'),
                                        TextEntry::make('weight')->label('Peso')->getStateUsing(fn ($record) => $record->weight . ' kg'),
                                        TextEntry::make('height')->label('Altura')->getStateUsing(fn ($record) => $record->height . ' m'),
                                    
                                        // Una línea horizontal para separar claramente cada entrada
                                        TextEntry::make('observations')
                                            ->label('Observaciones')
                                            ->columnSpanFull()
                                            ->visible(fn ($state) => !empty($state)),
                            ])
                            // Añade una línea divisoria entre cada registro para mayor claridad
                            ->contained(true),
                    ])

            ]);
    }
}
