<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Resources\Patients\Schemas\PatientForm;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Form;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->record->medicalRecord->patient->status === 'active'),
                
            Action::make('complete_patient_record')
                ->label('Completar Expediente')
                ->icon('heroicon-o-identification')
                ->color('primary')
                ->visible(fn () => $this->record->medicalRecord->patient->status === 'pending_review')
                ->url(fn () => PatientResource::getUrl('edit', [
                    'record' => $this->record->medicalRecord->patient->id,
                    'redirect_to_appointment' => $this->record->id,
                ])),
        ];
    }
}
