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
                                
                                // Calcular la edad a partir de date_of_birth
                                if ($patient->date_of_birth) {
                                    $age = Carbon::parse($patient->date_of_birth)->age;
                                    $set('patient_age', $age . ' años');
                                } else {
                                    $set('patient_age', 'No disponible');
                                }
                                
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
                    ->createOptionUsing(function (array $data,  $get) {
                        // Crear el paciente (esto automáticamente crea un MedicalRecord en el evento created)
                        $patientData = [
                            'full_name' => $data['full_name'],
                            'date_of_birth' => $data['date_of_birth'],
                            'sex' => $data['sex'],
                            'curp' => $data['curp'] ?? null,
                            'contact_phone' => $data['contact_phone'] ?? null,
                            'locality' => $data['locality'] ?? null,
                        ];
                        
                        // Añadir tutor_id si está presente en los datos
                        if (isset($data['tutor_id'])) {
                            $patientData['tutor_id'] = $data['tutor_id'];
                        }
                        
                        $patient = Patient::create($patientData);

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
                    ->visible(fn ($get, $livewire) => filled($get('medical_record_id')))
                    // Cargar datos del paciente cuando se está editando un registro existente
                    ->afterStateHydrated(function ($component, $state, $livewire) {
                        // Solo ejecutar si estamos en modo edición y hay un medical_record_id
                        if (isset($livewire->record) && $livewire->record->medical_record_id) {
                            $medicalRecord = MedicalRecord::with('patient')->find($livewire->record->medical_record_id);
                            if ($medicalRecord && $medicalRecord->patient) {
                                $patient = $medicalRecord->patient;
                                $livewire->data['patient_name'] = $patient->full_name;
                                
                                // Calcular la edad a partir de date_of_birth
                                if ($patient->date_of_birth) {
                                    $age = Carbon::parse($patient->date_of_birth)->age;
                                    $livewire->data['patient_age'] = $age . ' años';
                                } else {
                                    $livewire->data['patient_age'] = 'No disponible';
                                }
                                
                                $livewire->data['patient_sex'] = $patient->sex === 'M' ? 'Masculino' : 'Femenino';
                            }
                        }
                    }),

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

                Select::make('status')
                    ->label('Estado de la visita')
                    ->options(AppointmentStatus::class)
                    ->default(AppointmentStatus::PENDING)
                    ->required(),
                DateTimePicker::make('created_at')
                    ->label('Fecha de Creación')
                    ->displayFormat('d/m/Y H:i:s')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\ViewRecord || $livewire instanceof \Filament\Resources\Pages\EditRecord),
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
