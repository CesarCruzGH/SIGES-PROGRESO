<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\Patients\PatientResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\SelectAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
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
class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Visitas')
            ->description('Control de Visitas')
            ->poll('10s')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('ticket_number')
                            ->label('Ticket')
                            ->searchable()
                            ->weight('bold')
                            ->size('lg')
                            ->color('primary'),
                            
                        TextColumn::make('medicalRecord.record_number')
                            ->label('Expediente')
                            ->searchable()
                            ->sortable()
                            ->color('gray')
                            ->size('sm'),
                    ])->grow(false),
                    
                    Stack::make([
                        Split::make([
                            TextColumn::make('medicalRecord.patient.full_name')
                                ->label('Paciente')
                                ->searchable()
                                ->size('lg')
                                ->weight('bold')
                                ->default(fn ($record) => 'Expediente Pendiente')
                                ->weight('medium')
                                ->description(fn ($record) => $record->medicalRecord->patient->status === 'pending_review' 
                                    ? 'Requiere completar datos' 
                                    : null)
                                ->color(fn ($record) => $record->medicalRecord->patient->status === 'pending_review' 
                                    ? 'warning' 
                                    : 'gray'),
                        ]),
                        
                        Split::make([
                            IconColumn::make('service.name')                              
                                ->icon(fn (string $state): Heroicon => match($state) {
                                    'Medicina General' => Heroicon::OutlinedHeart,
                                    'Pediatría' => Heroicon::OutlinedFaceSmile,
                                    'Urgencias' => Heroicon::OutlinedExclamationTriangle,
                                    default => Heroicon::OutlinedClipboardDocumentList,
                                })
                                ->color(fn (string $state): string => match($state) {
                                    'Urgencias' => 'danger',
                                    default => 'primary',
                                })
                                ->grow(false),
                                
                            TextColumn::make('service.name')
                                ->label('Servicio')
                                ->sortable()
                                ->searchable(),
                        ]),
                    ]),
                ])->from('md'),
                
                Split::make([
                   /* Stack::make([
                        TextColumn::make('appointment_date')
                            ->label('Fecha')
                            ->date('d/m/Y')
                            ->sortable(),
                            
                        TextColumn::make('appointment_time')
                            ->label('Hora')
                            ->time('H:i')
                            ->sortable()
                            ->color('primary'),
                    ]),
                    */
                    Stack::make([
                        Split::make([
                            IconColumn::make('doctor.name')
                                ->label('')
                                ->icon(fn (string $state): Heroicon => Heroicon::OutlinedUserCircle)
                                ->color('success')
                                ->grow(false),
                                
                            TextColumn::make('doctor.name')
                                ->label('Médico')
                                ->searchable()
                                ->sortable()
                                ->default('Sin asignar')
                                ,
                        ]),
                        
                        Split::make([
                            IconColumn::make('clinic_room_number')
                                ->label('')
                                ->icon(fn (string $state): Heroicon => Heroicon::OutlinedBuildingOffice2)
                                ->color('primary')
                                ->grow(false),
                            TextColumn::make('clinic_room_number')
                                ->label('Consultorio')
                                ->prefix('Consultorio: ')
                                ->searchable(),
                        ]),
                    ]),
                ])->from('md'),
                
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
                    EditAction::make(),
                    
                    SelectAction::make('status')
                        ->label('Cambiar Estado')
                        ->icon('heroicon-o-arrow-path')
                        ->options(AppointmentStatus::class)
                        ->action(function ($record, $data) {
                            $record->update(['status' => $data['status']]);
                            
                            Notification::make()
                                ->title('Estado actualizado')
                                ->body("El estado de la cita {$record->ticket_number} ha sido actualizado.")
                                ->success()
                                ->send();
                        }),
                    
                    Action::make('complete_patient_record')
                        ->label('Completar Expediente')
                        ->icon('heroicon-o-identification')
                        ->color('warning')
                        ->visible(fn ($record) => $record->medicalRecord->patient->status === 'pending_review')
                        ->url(fn ($record): string => PatientResource::getUrl('edit', ['record' => $record->medicalRecord->patient,'appointment_id' => $record->id])),
                        
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