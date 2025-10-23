<?php

namespace App\Filament\Resources\MedicalRecords\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\Auth;
use App\Models\NursingAssessment;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;

class SomatometricReadingsRelationManager extends RelationManager
{
    protected static string $relationship = 'somatometricReadings';
    protected static ?string $title = 'Historial de Somatometría';
    
    // Variable para almacenar la valoración inicial
    protected ?NursingAssessment $nursingAssessment = null;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->afterMount(function (): void {
            // Cargar la valoración inicial de enfermería solo si hay un registro propietario
            if ($this->getOwnerRecord()) {
                $this->nursingAssessment = NursingAssessment::where('medical_record_id', $this->getOwnerRecord()->id)->first();
            }
        });
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('blood_pressure_systolic')->label('Presión Sistólica (mmHg)')->numeric(),
            TextInput::make('blood_pressure_diastolic')->label('Presión Diastólica (mmHg)')->numeric(),
            TextInput::make('heart_rate')->label('Frecuencia Cardíaca (lpm)')->numeric(),
            TextInput::make('temperature')->label('Temperatura (°C)')->numeric(),
            TextInput::make('weight')->label('Peso (kg)')->numeric(),
            TextInput::make('height')->label('Talla (m)')->numeric(),
            Textarea::make('observations')->label('Observaciones')->columnSpanFull(),
        ];
    }

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
            ->modifyQueryUsing(function (Builder $query) {
                return $query;
            })
            ->columns([
                IconColumn::make('is_initial_assessment')
                    ->label('Tipo')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-clipboard')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->getStateUsing(function ($record) {
                        return false; // Registros normales
                    })
                    ->tooltip(fn ($state) => $state ? 'Valoración Inicial' : 'Registro Diario'),
                TextColumn::make('created_at')
                    ->label('Fecha de Toma')
                    ->dateTime('d/m/Y H:i a')
                    ->sortable(),
                TextColumn::make('blood_pressure')
                    ->label('Presión Arterial')
                    ->getStateUsing(fn ($record) => $record->blood_pressure_systolic . '/' . $record->blood_pressure_diastolic . ' mmHg'),
                TextColumn::make('weight')
                    ->label('Peso')
                    ->suffix(' kg'),
                TextColumn::make('height')
                    ->label('Talla')
                    ->suffix(' m'),
                TextColumn::make('user.name')->label('Registrado por'),
            ])
            ->headerActions([
                CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id(); // Asigna al usuario actual
                    return $data;
                }),
                // Acción para ver la valoración inicial
                ViewAction::make('viewInitialAssessment')
                    ->label('Ver Valoración Inicial')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn () => $this->nursingAssessment !== null)
                    ->modalHeading('Valoración Inicial de Enfermería')
                    ->modalDescription('Datos registrados en la primera consulta del paciente')
                    ->modalIcon('heroicon-o-star')
                    ->schema(function () {
                        $nursingAssessment = $this->nursingAssessment;
                        
                        return [
                            Section::make('Datos Somatométricos Iniciales')
                                ->description('Valores registrados en la primera consulta')
                                ->icon('heroicon-o-clipboard-document-list')
                                ->schema([
                                    Forms\Components\TextInput::make('blood_pressure_systolic')
                                        ->label('Presión Sistólica (mmHg)')
                                        ->default($nursingAssessment->blood_pressure_systolic ?? 'No registrado')
                                        ->disabled(),
                                    Forms\Components\TextInput::make('blood_pressure_diastolic')
                                        ->label('Presión Diastólica (mmHg)')
                                        ->default($nursingAssessment->blood_pressure_diastolic ?? 'No registrado')
                                        ->disabled(),
                                    Forms\Components\TextInput::make('heart_rate')
                                        ->label('Frecuencia Cardíaca (lpm)')
                                        ->default($nursingAssessment->heart_rate ?? 'No registrado')
                                        ->disabled(),
                                    Forms\Components\TextInput::make('temperature')
                                        ->label('Temperatura (°C)')
                                        ->default($nursingAssessment->temperature ?? 'No registrado')
                                        ->disabled(),
                                    Forms\Components\TextInput::make('weight')
                                        ->label('Peso (kg)')
                                        ->default($nursingAssessment->weight ?? 'No registrado')
                                        ->disabled(),
                                    Forms\Components\TextInput::make('height')
                                        ->label('Talla (m)')
                                        ->default($nursingAssessment->height ?? 'No registrado')
                                        ->disabled(),
                                ]),
                            Section::make('Información Clínica')
                                ->description('Antecedentes y alergias')
                                ->icon('heroicon-o-clipboard-document-check')
                                ->schema([
                                    Forms\Components\Textarea::make('allergies')
                                        ->label('Alergias')
                                        ->default($nursingAssessment->allergies ?? 'No registrado')
                                        ->disabled(),
                                    Forms\Components\Textarea::make('personal_pathological_history')
                                        ->label('Antecedentes Patológicos Personales')
                                        ->default($nursingAssessment->personal_pathological_history ?? 'No registrado')
                                        ->disabled(),
                                ]),
                            Section::make('Información de Registro')
                                ->schema([
                                    Forms\Components\TextInput::make('created_at')
                                        ->label('Fecha de Registro')
                                        ->default($nursingAssessment ? $nursingAssessment->created_at->format('d/m/Y H:i a') : 'No registrado')
                                        ->disabled(),
                                    Forms\Components\TextInput::make('user')
                                        ->label('Registrado por')
                                        ->default($nursingAssessment && $nursingAssessment->user ? $nursingAssessment->user->name : 'No registrado')
                                        ->disabled(),
                                ]),
                        ];
                    })
                    ->action(function () {
                        // Solo cerrar el modal
                    }),
            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}