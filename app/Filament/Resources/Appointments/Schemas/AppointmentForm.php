<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\Patients\Schemas\PatientForm;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                // Campo principal: Selección de Expediente Médico
                Select::make('medical_record_id')
                    ->label('Expediente Médico')
                    ->placeholder('Buscar por número de expediente...')
                    ->options(function () {
                        return MedicalRecord::with('patient')
                            ->get()
                            ->mapWithKeys(function ($record) {
                                return [
                                    $record->id => $record->record_number . ' - ' . $record->patient->full_name
                                ];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->columnSpanFull()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $medicalRecord = MedicalRecord::with('patient')->find($state);
                            if ($medicalRecord && $medicalRecord->patient) {
                                $patient = $medicalRecord->patient;
                                $set('patient_name', $patient->full_name);
                                $set('patient_age', $patient->age_display);
                                $set('patient_sex', $patient->sex === 'M' ? 'Masculino' : 'Femenino');
                            }
                        } else {
                            $set('patient_name', null);
                            $set('patient_age', null);
                            $set('patient_sex', null);
                        }
                    })
                    ->createOptionForm(
                        // Reutilizamos el esquema completo de paciente desde PatientForm
                        // El método getBasicPatientFormSchema() ahora maneja automáticamente el contexto
                        PatientForm::getBasicPatientFormSchema()
                    )
                    ->createOptionUsing(function (array $data) {
                        // Crear el paciente (esto automáticamente crea un MedicalRecord en el evento created)
                        $patient = Patient::create([
                            'full_name' => $data['full_name'],
                            'date_of_birth' => $data['date_of_birth'],
                            'sex' => $data['sex'],
                            'curp' => $data['curp'] ?? null,
                            'contact_phone' => $data['contact_phone'] ?? null,
                            'locality' => $data['locality'] ?? null,
                        ]);

                        // Actualizar el expediente médico que ya fue creado automáticamente
                        $medicalRecord = $patient->medicalRecord;
                        $medicalRecord->update([
                            'patient_type' => $data['patient_type'],
                        ]);

                        return $medicalRecord->id;
                    }),

                // Información del paciente (visible solo cuando se selecciona un expediente)
                Fieldset::make('Información del Paciente')
                    ->schema([
                        TextInput::make('patient_name')
                            ->label('Nombre del Paciente')
                            ->disabled()
                            ->dehydrated(false),
                        
                        TextInput::make('patient_age')
                            ->label('Edad')
                            ->disabled()
                            ->dehydrated(false),
                        
                        TextInput::make('patient_sex')
                            ->label('Sexo')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->visible(fn ( $get) => filled($get('medical_record_id'))),

                // Campos del formulario de cita
                TextInput::make('ticket_number')
                    ->label('Número de Ticket')
                    ->placeholder('Se generará automáticamente si se deja vacío')
                    ->helperText('Dejar vacío para generar ticket walk-in automáticamente'),

                Select::make('service_id')
                    ->label('Servicio')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('doctor_id')
                    ->label('Doctor')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->preload(),

                TextInput::make('clinic_room_number')
                    ->label('Número de Consultorio')
                    ->numeric(),

                DateTimePicker::make('appointment_time')
                    ->label('Fecha y Hora de la Cita')
                    ->default(now())
                    ->required(),

                Select::make('status')
                    ->label('Estado')
                    ->options(AppointmentStatus::class)
                    ->default(AppointmentStatus::PENDING)
                    ->required(),

                Textarea::make('reason_for_visit')
                    ->label('Motivo de la Visita')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Notas Adicionales')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
