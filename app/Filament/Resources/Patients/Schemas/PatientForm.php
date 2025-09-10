<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Schemas\Components\Fieldset as ComponentsFieldset;
use Illuminate\Support\Carbon;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use League\CommonMark\Extension\Table\TableSection;
use Filament\Forms\Components\Toggle;
use Filament\Actions\Action;
class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información personal')
                    ->description('Detalles básicos del paciente')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([  
                        Section::make('Medico asignado')
                        ->schema([
                            TextInput::make('assigned_doctor_name')
                                            ->label('Médico Asignado')
                                            ->placeholder('Haga clic en el botón para seleccionar...')
                                            ->readOnly() // El usuario no puede escribir aquí.
                                            ->columnSpanFull()
                                            // 2. El botón que abre el modal.
                                            ->suffixAction(
                                                Action::make('selectDoctor')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->label('Elegir')
                                                    ->modalHeading('Selecciona un Médico')
                                                    ->modalSubmitActionLabel('Confirmar Selección')
                                                    ->modalCancelActionLabel('Cancelar')
                                                    
                                                    // 3. El contenido del modal se define aquí.
                                                    ->form([
                                                        Select::make('doctor_id')
                                                            ->label('Médicos Disponibles')
                                                            ->searchable() // ¡Permite buscar en la lista!
                                                            ->required()
                                                            // 4. Tu lista estática de opciones.
                                                            ->options([
                                                                'dr_garcia' => 'Dr. Alejandro García (Cardiólogo)',
                                                                'dra_lopez' => 'Dra. Isabel López (Pediatra)',
                                                                'dr_sanchez' => 'Dr. Carlos Sánchez (Neurólogo)',
                                                                'dra_martinez' => 'Dra. Laura Martínez (Dermatóloga)',
                                                            ])
                                                            ->helperText('Puedes escribir para filtrar la lista.'),
                                                    ])
                                                    
                                                    // 5. La acción que se ejecuta al confirmar.
                                                    ->action(function (array $data, $set) {
                                                        // $data contiene los valores del formulario del modal
                                                        // Ejemplo: ['doctor_id' => 'dra_lopez']

                                                        $doctores = [
                                                            'dr_garcia' => 'Dr. Alejandro García (Cardiólogo)',
                                                            'dra_lopez' => 'Dra. Isabel López (Pediatra)',
                                                            'dr_sanchez' => 'Dr. Carlos Sánchez (Neurólogo)',
                                                            'dra_martinez' => 'Dra. Laura Martínez (Dermatóloga)',
                                                        ];
                                                        
                                                        $nombreSeleccionado = $doctores[$data['doctor_id']];

                                                        // Usamos $set para actualizar el campo de texto principal.
                                                        $set('assigned_doctor_name', $nombreSeleccionado);
                                                    })
                                            ),
                                        ]),
                        Toggle::make('has_insurance')
                            ->label('¿Tiene seguro médico?')
                            ->live() // Esencial para que el formulario reaccione a sus cambios.
                            ->default(false),
                        TextInput::make('insurance_provider')
                            ->label('Aseguradora')
                            ->hidden(fn ($get): bool =>!$get('has_insurance')) // Oculto si 'has_insurance' es falso.
                            ->required(fn ($get): bool => $get('has_insurance')), // Requerido si 'has_insurance' es verdadero.

                        TextInput::make('policy_number')
                            ->label('Número de Póliza')
                            ->hidden(fn ($get): bool =>!$get('has_insurance'))
                            ->required(fn ($get): bool => $get('has_insurance')),     

                        TextInput::make('full_name')
                            ->label('Nombre Completo')
                            ->required()
                            ->minLength(5)
                            ->validationMessages([
                                'required' => 'El nombre completo del paciente es obligatorio.',
                                'minLength' => 'El nombre completo debe tener al menos 5 caracteres.',
                            ]),

                        DatePicker::make('date_of_birth')
                            ->label('Fecha de Nacimiento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $set, ?string $state) {
                                $set('age', $state ? Carbon::parse($state)->age : null);
                            }),

                        TextInput::make('age')
                            ->label('Edad')
                            ->numeric()
                            ->readOnly()
                            ->placeholder('Se calculará automáticamente'),

                        Radio::make('gender')
                            ->label('Género')
                            ->options([
                                'Masculino' => 'Masculino',
                                'Femenino' => 'Femenino',
                                'Otro' => 'Otro',
                            ])
                            ->required()
                            ->inline(),
                        
                    ]),
                Tabs::make('Información Personal')                  
                    ->columnSpanFull()
                    ->persistTabInQueryString()                    
                    ->tabs([
                        Tabs\Tab::make('Detalles básicos del paciente')
                            ->icon('heroicon-s-user-plus')
                            ->schema([
                                TextInput::make('full_name')
                            ->label('Nombre Completo')
                            ->required()
                            ->minLength(5)
                            ->validationMessages([
                                'required' => 'El nombre completo del paciente es obligatorio.',
                                'minLength' => 'El nombre completo debe tener al menos 5 caracteres.',
                            ]),

                        DatePicker::make('date_of_birth')
                            ->label('Fecha de Nacimiento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $set, ?string $state) {
                                $set('age', $state ? Carbon::parse($state)->age : null);
                            }),

                        TextInput::make('age')
                            ->label('Edad')
                            ->numeric()
                            ->readOnly()
                            ->placeholder('Se calculará automáticamente'),

                        Radio::make('gender')
                            ->label('Género')
                            ->options([
                                'Masculino' => 'Masculino',
                                'Femenino' => 'Femenino',
                                'Otro' => 'Otro',
                            ])
                            ->required()
                            ->inline(),
                            ]),
                        Tabs\Tab::make('Seguro y Facturación')
                            ->schema([
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->required()
                            ->validationMessages([
                                'required' => 'El número de teléfono es obligatorio.',
                                'tel' => 'Por favor, introduce un número de teléfono válido.',
                            ]),

                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->validationMessages([
                                'required' => 'El correo electrónico es obligatorio.',
                                'email' => 'Por favor, introduce una dirección de correo electrónico válida.',
                            ]),

                        TextInput::make('address')
                            ->label('Dirección')
                            ->required()
                            ->minLength(10)
                            ->validationMessages([
                                'required' => 'La dirección es obligatoria.',
                                'minLength' => 'La dirección debe tener al menos 10 caracteres.',
                            ]),
                    ]),
                ]),
            ]);
    }
}