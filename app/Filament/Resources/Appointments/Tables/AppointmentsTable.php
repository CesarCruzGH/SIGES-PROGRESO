<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Enums\AppointmentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\SelectAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

// Importa estas clases al principio:
use App\Filament\Resources\Patients\PatientResource;
use Filament\Actions\Action;
class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        
        return $table
            ->heading('Visitas')
            ->description('Control de Visitas')
            ->poll('10s')
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket')
                    ->searchable(),
                TextColumn::make('medicalRecord.record_number')
                    ->label('Expediente')
                    ->searchable()
                    ->sortable(),
                    TextColumn::make('medicalRecord.patient.full_name')
                    ->label('Paciente')
                    ->searchable()
                    // Muestra un placeholder útil si el expediente está pendiente
                    ->default(fn ($record) => 'Expediente Pendiente')
                    ->description(fn ($record) => $record->medicalRecord->patient->status === 'pending_review' ? 'Requiere completar datos' : null)
                    ->url(fn ($record): string => PatientResource::getUrl('edit', ['record' => $record->medicalRecord->patient])),
                TextColumn::make('service.name')
                    ->label('Servicio')    
                    ->sortable(),
                TextColumn::make('doctor.name')
                    ->label('Médico Asignado')
                    ->searchable()
                    ->sortable()
                    ->default('Sin asignar'), 
                TextColumn::make('clinic_room_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable()
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                
                // SelectAction para cambio rápido de estado
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
                
                // --- ACCIÓN PERSONALIZADA ---
                Action::make('complete_patient_record')
                    ->label('Completar Expediente')
                    ->icon('heroicon-o-identification')
                    ->color('warning')
                    // Solo visible si el paciente está pendiente
                    ->visible(fn ($record) => $record->medicalRecord->patient->status === 'pending_review')
                    // Lleva directamente a la página de edición del paciente
                    //->url(fn ($record): string => PatientResource::getUrl('edit', ['record' => $record->medicalRecord->patient]))
                    ->url(fn ($record): string => PatientResource::getUrl('edit', ['record' => $record->medicalRecord->patient,'appointment_id' => $record->id])), // <-- La "etiqueta"
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make()->label('Crear Nueva Visita'),
            ])
            ;
    }
}
