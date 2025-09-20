<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Schemas\Schema; // CORREGIDO: Usar Form en lugar de Schema
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Section;

// --- Importar todos los Enums ---
use App\Enums\PatientType;
use App\Enums\EmployeeStatus;
use App\Enums\Shift;
use App\Enums\VisitType;
use Carbon\Carbon;

class PatientForm
{
    // CORREGIDO: El método se llama "schema" y recibe un objeto Form
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Personal')
                    ->columns(2)
                    ->icon('heroicon-s-identification')
                    ->iconColor('icon')
                    
                    ->schema([
                        TextInput::make('medical_record_number')
                            ->label('Número de Expediente')
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Se generará automáticamente al guardar'),
                        TextInput::make('full_name')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(255),

                            //CURP
                            TextInput::make('curp')
                            ->label('CURP')
                            ->extraAttributes([
                                'x-mask' => 'AAAA999999HAA999A9', // <-- La directiva de AlpineJS
                                // 1. UX: Convierte a mayúsculas mientras el usuario escribe
                                '@input' => '$event.target.value = $event.target.value.toUpperCase()',
                            ])
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null)
                            ->unique(ignoreRecord: true)
                            ->regex('/^[A-Z]{1}[AEIOU]{1}[A-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[HM]{1}(AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS){1}[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}[0-9]{1}$/')
                            ->validationAttribute('CURP')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get,$set) {
                                $curp = $get('curp');
                                if (strlen($curp) === 18) {
                                    $dateStr = substr($curp, 4, 6);
                                    $year = substr($dateStr, 0, 2);
                                    $month = substr($dateStr, 2, 2);
                                    $day = substr($dateStr, 4, 2);
                                    $century = (intval($year) > (int)date('y')) ? '19' : '20';
                                    $fullYear = $century . $year;
                                    $dateOfBirth = "{$fullYear}-{$month}-{$day}";
                                    $set('date_of_birth', $dateOfBirth);
                                    $age = Carbon::parse($dateOfBirth)->age;
                                    $set('age_display', $age . ' años');
                                    $set('sex', (substr($curp, 10, 1) === 'H') ? 'Masculino' : 'Femenino');
                                }
                            }),
                        Select::make('sex')
                            ->label('Sexo')
                            ->options(['Masculino' => 'Masculino', 'Femenino' => 'Femenino'])
                            ->required(),
                            DatePicker::make('date_of_birth')
                            ->label('Fecha de Nacimiento')
                            ->required()
                            ->native(false)
                            ->maxDate(now())
                            ->minDate(now()->subYear(120))
                            ->displayFormat('d/F/Y')
                            ->locale('es')
                            ->prefixIcon('heroicon-s-calendar-date-range')
                            ->prefixIconColor('icon')
                            ->live()
                            ->afterStateUpdated(function ( $get,  $set) {
                                $dateOfBirth = $get('date_of_birth');
                                if ($dateOfBirth) {
                                    $age = Carbon::parse($dateOfBirth)->age;
                                    $set('age_display', $age . ' años');
                                } else {
                                    $set('age_display', null);
                                }
                            }),
                
                        TextInput::make('age_display')->label('Edad')->disabled()->placeholder('Se calculará automáticamente')->dehydrated(false),
                        TextInput::make('locality')->label('Localidad'),
                    ]),

                Section::make('Clasificación del Paciente')
                    ->columns(2)
                    ->schema([
                        Select::make('patient_type')
                            ->label('Tipo de Paciente')
                            ->options(PatientType::getOptions()) // CORREGIDO: Usando nuestro nuevo método
                            ->required()
                            ->live(),
                        Select::make('employee_status')
                            ->label('Estatus (si es empleado)')
                            ->options(EmployeeStatus::getOptions()) // CORREGIDO: Usando nuestro nuevo método
                            // CORREGIDO: La comparación debe ser con el objeto Enum
                            ->visible(fn ($get) => $get('patient_type') === PatientType::EMPLOYEE->value),
                    ]),

                Section::make('Tutor (para menores de edad)')
                    ->schema([
                        Toggle::make('is_pediatric')
                            ->label('¿Es paciente pediátrico?')
                            ->helperText('Activa esta opción si el paciente es menor de edad.')
                            ->live(),
                        Select::make('tutor_id')
                            ->label('Asignar Tutor')
                            ->relationship('tutor', 'full_name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('full_name')->label('Nombre Completo')->required(),
                                TextInput::make('relationship')->label('Parentesco')->required(),
                                TextInput::make('phone_number')->label('Teléfono'),
                                Textarea::make('address')->label('Dirección')->columnSpanFull(),
                            ])
                            ->visible(fn ($get) => $get('is_pediatric')),
                    ]),

                Section::make('Detalles Médicos y Administrativos') // AÑADIDO
                    ->columns(2)
                    ->schema([
                        Select::make('attending_doctor_id')
                            ->label('Médico que Atiende')
                            ->relationship('attendingDoctor', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('role', 'doctor'))
                            ->searchable()
                            ->preload(),
                        Select::make('shift')
                            ->label('Turno')
                            ->options(Shift::getOptions()), // CORREGIDO
                        Select::make('visit_type')
                            ->label('Tipo de Visita')
                            ->options(VisitType::getOptions()), // CORREGIDO
                        Toggle::make('has_disability')
                            ->label('¿Tiene alguna discapacidad?')
                            ->live(),
                        Textarea::make('disability_details')
                            ->label('Detalles de la Discapacidad')
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('has_disability')),
                    ]),

                ]);
    }


}
