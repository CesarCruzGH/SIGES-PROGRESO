<?php

namespace App\Filament\Resources\Appointments\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfoSection;
use App\Models\SomatometricReading;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Section;
class NursingEvolutionsRelationManager extends RelationManager
{
    protected static string $relationship = 'nursingEvolutions';
    protected static ?string $title = 'Evolución de Enfermería';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Textarea::make('problem')->label('P'),
                Forms\Components\Textarea::make('subjective')->label('S'),
                Forms\Components\Textarea::make('objective')->label('O'),
                Forms\Components\Textarea::make('analysis')->label('A'),
                Forms\Components\Textarea::make('plan')->label('P'),
                Section::make('Signos vitales')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('blood_pressure_systolic')->label('PA sistólica')->numeric(),
                        Forms\Components\TextInput::make('blood_pressure_diastolic')->label('PA diastólica')->numeric(),
                        Forms\Components\TextInput::make('heart_rate')->label('Pulso (bpm)')->numeric(),
                        Forms\Components\TextInput::make('respiratory_rate')->label('FR (resp/min)')->numeric(),
                        Forms\Components\TextInput::make('temperature')->label('Temp (°C)')->numeric(),
                        Forms\Components\TextInput::make('weight')->label('Peso (kg)')->numeric(),
                        Forms\Components\TextInput::make('height_cm')->label('Talla (cm)')->numeric()->minValue(50)->maxValue(250)->step('1'),
                        Forms\Components\TextInput::make('blood_glucose')->label('Glucosa')->numeric(),
                        Forms\Components\TextInput::make('oxygen_saturation')->label('SpO2 (%)')->numeric(),
                        Forms\Components\Textarea::make('observations')->label('Observaciones')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Enfermero'),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('signos')
                    ->label('Signos')
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $sr = $record->somatometricReading;
                        if (! $sr) return '';
                        $parts = [];
                        if ($sr->blood_pressure_systolic && $sr->blood_pressure_diastolic) $parts[] = "PA {$sr->blood_pressure_systolic}/{$sr->blood_pressure_diastolic}";
                        if ($sr->heart_rate) $parts[] = "FC {$sr->heart_rate}";
                        if ($sr->respiratory_rate) $parts[] = "FR {$sr->respiratory_rate}";
                        if ($sr->temperature) $parts[] = "Temp {$sr->temperature}°C";
                        if ($sr->weight) $parts[] = "Peso {$sr->weight}kg";
                        if ($sr->height) $parts[] = "Talla ".(int) round($sr->height * 100)."cm";
                        if ($sr->blood_glucose) $parts[] = "Gluc {$sr->blood_glucose}";
                        if ($sr->oxygen_saturation) $parts[] = "SpO2 {$sr->oxygen_saturation}%";
                        return implode(' • ', $parts);
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Crear Evolución de Enfermería')
                    ->modalHeading('Registrar Evolución de Enfermería')
                    ->modalSubmitActionLabel('Guardar')
                    ->modalCancelActionLabel('Cancelar')
                    ->mutateFormDataUsing(function (array $data): array {
                        $owner = $this->getOwnerRecord(); // Appointment
                        $hasVitals = collect([
                            'blood_pressure_systolic','blood_pressure_diastolic','heart_rate','respiratory_rate',
                            'temperature','weight','height','blood_glucose','oxygen_saturation','observations',
                        ])->some(fn ($k) => isset($data[$k]) && $data[$k] !== null && $data[$k] !== '');

                        if ($hasVitals) {
                            $reading = SomatometricReading::create([
                                'medical_record_id' => $owner->medical_record_id,
                                'appointment_id' => $owner->id,
                                'user_id' => Auth::id(),
                                'blood_pressure_systolic' => $data['blood_pressure_systolic'] ?? null,
                                'blood_pressure_diastolic' => $data['blood_pressure_diastolic'] ?? null,
                                'heart_rate' => $data['heart_rate'] ?? null,
                                'respiratory_rate' => $data['respiratory_rate'] ?? null,
                                'temperature' => $data['temperature'] ?? null,
                                'weight' => $data['weight'] ?? null,
                            'height' => isset($data['height_cm']) ? ($data['height_cm'] / 100) : null,
                                'blood_glucose' => $data['blood_glucose'] ?? null,
                                'oxygen_saturation' => $data['oxygen_saturation'] ?? null,
                                'observations' => $data['observations'] ?? null,
                            ]);
                            $data['somatometric_reading_id'] = $reading->id;
                        }

                        // asegurar vínculo al expediente de la visita
                        $data['medical_record_id'] = $owner->medical_record_id;
                        // asegurar el usuario registrante
                        $data['user_id'] = Auth::id();

                        // limpiar campos de signos del payload de evolución
                        foreach (['blood_pressure_systolic','blood_pressure_diastolic','heart_rate','respiratory_rate','temperature','weight','height_cm','blood_glucose','oxygen_saturation','observations'] as $k) {
                            unset($data[$k]);
                        }
                        return $data;
                    }),
            ])
            ->actions([
                ViewAction::make()->modalHeading('Evolución de Enfermería')
                    ->infolist(function (Schema $schema) {
                        return $schema->schema([
                            Section::make('Evolución')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('problem')->label('P')->columnSpanFull(),
                                    TextEntry::make('subjective')->label('S')->columnSpanFull(),
                                    TextEntry::make('objective')->label('O')->columnSpanFull(),
                                    TextEntry::make('analysis')->label('A')->columnSpanFull(),
                                    TextEntry::make('plan')->label('P')->columnSpanFull(),
                                ]),
                            Section::make('Signos vitales')
                                ->visible(fn ($record) => filled($record->somatometricReading))
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('somatometricReading.blood_pressure_systolic')->label('PA sistólica'),
                                    TextEntry::make('somatometricReading.blood_pressure_diastolic')->label('PA diastólica'),
                                    TextEntry::make('somatometricReading.heart_rate')->label('FC'),
                                    TextEntry::make('somatometricReading.respiratory_rate')->label('FR'),
                                    TextEntry::make('somatometricReading.temperature')->label('Temp (°C)'),
                                    TextEntry::make('somatometricReading.weight')->label('Peso (kg)'),
                                    TextEntry::make('somatometricReading.height')->label('Talla (m)'),
                                    TextEntry::make('somatometricReading.blood_glucose')->label('Glucosa'),
                                    TextEntry::make('somatometricReading.oxygen_saturation')->label('SpO2 (%)'),
                                    TextEntry::make('somatometricReading.observations')->label('Observaciones')->columnSpanFull(),
                                ]),
                        ]);
                    }),
                EditAction::make(),
            ])
            ->bulkActions([]);
    }
}