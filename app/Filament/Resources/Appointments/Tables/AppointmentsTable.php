<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Enums\AppointmentStatus;
use App\Enums\VisitType;
use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Resources\MedicalRecords\RelationManagers\SomatometricReadingsRelationManager;
use App\Models\NursingAssessment;
use App\Models\SomatometricReading;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\SelectAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
//use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Visitas')
            ->description('Control de Visitas')
            ->poll('10s')
            ->columns([
                
                TextColumn::make('medicalRecord.patient.full_name')
                    ->label('Paciente')
                    ->searchable()
                    ->size('lg')
                    ->weight('bold')
                    ->tooltip(fn ($record): string => $record->reason_for_visit)
                    ->description(fn ($record): ?string => $record->ticket_number ?? null),

                TextColumn::make('medicalRecord.record_number')
                    ->label('Expediente')
                    ->searchable()
                    ->sortable()
                    ->color('gray')
                    ->size('sm'),

                TextColumn::make('service.name')
                    ->label('Servicio')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('doctor.name')
                    ->label('Médico')
                    ->searchable()
                    ->sortable()
                    ->default('Sin asignar'),

                TextColumn::make('clinic_room_number')
                    ->label('Consultorio')
                    ->prefix('Consultorio: ')
                    ->searchable()
                    ->placeholder('Sin asignar')
                    ->weight(fn (?string $state): string => is_null($state) ? 'bold' : 'normal')
                    ->color(fn (?string $state): string => is_null($state) ? 'gray' : 'indigo'),
                TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match ($state->value ?? $state) {
                        AppointmentStatus::PENDING->value, 'pending' => 'Revisión',
                        AppointmentStatus::IN_PROGRESS->value, 'in_progress' => 'En consulta',
                        AppointmentStatus::COMPLETED->value, 'completed' => 'Completada',
                        AppointmentStatus::CANCELLED->value, 'cancelled' => 'Cancelada',
                        default => (string) $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state->value ?? $state) {
                        AppointmentStatus::PENDING->value, 'pending' => 'icon',
                        AppointmentStatus::IN_PROGRESS->value, 'in_progress' => 'warning',
                        AppointmentStatus::COMPLETED->value, 'completed' => 'success',
                        AppointmentStatus::CANCELLED->value, 'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn ($state) => match ($state->value ?? $state) {
                        AppointmentStatus::PENDING->value, 'pending' => 'heroicon-o-calendar',
                        AppointmentStatus::IN_PROGRESS->value, 'in_progress' => 'heroicon-o-clock',
                        AppointmentStatus::COMPLETED->value, 'completed' => 'heroicon-o-check-circle',
                        AppointmentStatus::CANCELLED->value, 'cancelled' => 'heroicon-o-x-circle',
                        default => '',
                    })
                    ->searchable(),
            ])
            ->filters([
                TernaryFilter::make('status')
                    ->label('Estado')
                    ->placeholder('Todas las citas')
                    ->trueLabel('Solo completadas')
                    ->falseLabel('Pendientes')
                    ->queries(
                        true: fn (Builder $query) => $query->where('status', AppointmentStatus::COMPLETED),
                        false: fn (Builder $query) => $query->whereIn('status', [AppointmentStatus::PENDING, AppointmentStatus::IN_PROGRESS]),
                        blank: fn (Builder $query) => $query,
                    ),
                    
                SelectFilter::make('service')
                    ->relationship('service', 'name')
                    ->label('Servicio')
                    ->preload(),
                    
                SelectFilter::make('doctor')
                    ->relationship('doctor', 'name')
                    ->label('Médico')
                    ->preload(),
                    
                Filter::make('appointment_date')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde'),
                        DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('appointment_date', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('appointment_date', '<=', $date),
                            );
                    }),
            ])
            ->recordUrl(fn ($record): string => route('filament.dashboard.resources.appointments.view', ['record' => $record]))
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->visible(fn ($record) => $record->medicalRecord->patient->status === 'active')
                    ,   
                                    
                    Action::make('complete_patient_record')
                        ->label('Completar Expediente')
                        ->icon('heroicon-o-identification')
                        ->color('warning')
                        ->visible(fn ($record) => $record->medicalRecord->patient->status === 'pending_review')
                        ->url(fn ($record): string => PatientResource::getUrl('edit', ['record' => $record->medicalRecord->patient,'appointment_id' => $record->id])),

                    // Hoja Inicial (Valoración de Enfermería)
                    Action::make('fill_initial_sheet')
                        ->label('Registrar Hoja Inicial')
                        ->icon('heroicon-o-document-plus')
                        ->visible(function ($record) {
                            // Verificar si es primera vez Y si NO existe una valoración inicial
                            $hasAssessment = NursingAssessment::where('medical_record_id', $record->medical_record_id)->exists();
                            return $record->visit_type === VisitType::PRIMERA_VEZ->value && !$hasAssessment;
                        })
                        ->form(function ($record) {
                            // Buscar valoración existente para prellenar el formulario
                            $assessment = NursingAssessment::where('medical_record_id', $record->medical_record_id)->first();
                            
                            return [
                                Textarea::make('allergies')
                                    ->label('Alergias')
                                    ->rows(3)
                                    ->default($assessment->allergies ?? null),
                                Textarea::make('personal_pathological_history')
                                    ->label('Antecedentes personales patológicos')
                                    ->rows(3)
                                    ->default($assessment->personal_pathological_history ?? null),
                            ];
                        })
                        ->action(function ($record, array $data) {
                            // Actualizar o crear la valoración
                            NursingAssessment::updateOrCreate(
                                ['medical_record_id' => $record->medical_record_id],
                                [
                                    'user_id' => Auth::id(),
                                    'allergies' => $data['allergies'] ?? null,
                                    'personal_pathological_history' => $data['personal_pathological_history'] ?? null,
                                ]
                            );
                            
                            Notification::make()
                                ->title('Hoja Inicial guardada')
                                ->success()
                                ->send();
                        }),

                    // Hoja Diaria (Somatometría)
                    Action::make('register_somatometrics')
                        ->label('Registrar Hoja Diaria')
                        ->icon('heroicon-o-heart')
                        ->visible(fn ($record) => !(($record->visit_type === VisitType::PRIMERA_VEZ->value)
                            && empty($record->medicalRecord->nursingAssessment)))
                        ->schema(SomatometricReadingsRelationManager::getFormSchema())
                        ->action(function ($record, array $data) {
                            SomatometricReading::create(array_merge($data, [
                                'medical_record_id' => $record->medical_record_id,
                                'appointment_id' => $record->id,
                                'user_id' => Auth::id(),
                            ]));
                        }),
                        
                    Action::make('confirm_attendance')
                        ->label('Confirmar Asistencia')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === AppointmentStatus::PENDING)
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => AppointmentStatus::IN_PROGRESS]);
                            
                            Notification::make()
                                ->title('Asistencia confirmada')
                                ->body("Se ha confirmado la asistencia para la cita {$record->ticket_number}.")
                                ->success()
                                ->send();
                        }),
                        
                    Action::make('cancel_appointment')
                        ->label('Cancelar Cita')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->visible(fn ($record) => in_array($record->status, [AppointmentStatus::PENDING, AppointmentStatus::IN_PROGRESS]))
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->update(['status' => AppointmentStatus::CANCELLED]);
                            
                            Notification::make()
                                ->title('Cita cancelada')
                                ->body("La cita {$record->ticket_number} ha sido cancelada.")
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make()->label('Crear Nueva Visita')->tooltip('Crear una nueva visita'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('hoy')
                    ->label('Hoy')
                    ->default(true)
                    ->query(fn (Builder $query) => $query->whereDate('created_at', now())),
                Filter::make('completadas')
                    ->label('Completadas')
                    ->query(fn (Builder $query) => $query->where('status', AppointmentStatus::COMPLETED)),
                Filter::make('canceladas')
                    ->label('Canceladas')
                    ->query(fn (Builder $query) => $query->whereIn('status', [AppointmentStatus::CANCELLED])),
                Filter::make('todas')
                    ->label('Todas')
                    ->query(fn (Builder $query) => $query),
            ]);
    }
}