<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Resources\Patients\Schemas\PatientForm;
use App\Enums\AppointmentStatus;
use App\Models\Prescription;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
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
                ->label('Editar Visita')
                ->color('primary')
                ->icon('heroicon-o-pencil')
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

            Action::make('register_consultation')
                ->label('Registrar Consulta')
                ->icon('heroicon-o-clipboard-document')
                ->color('success')
                ->visible(fn () => $this->record->status === AppointmentStatus::IN_PROGRESS && $this->record->doctor_id === Auth::id())
                ->form([
                    Textarea::make('diagnosis')
                        ->label('Diagnóstico')
                        ->required()
                        ->rows(4),
                    Textarea::make('treatment_plan')
                        ->label('Plan de Tratamiento')
                        ->rows(4),
                    Repeater::make('items')
                        ->label('Receta')
                        ->minItems(1)
                        ->addActionLabel('Añadir Medicamento')
                        ->columnSpanFull()
                        ->schema([
                            TextInput::make('drug')
                                ->label('Medicamento')
                                ->required(),
                            TextInput::make('dose')
                                ->label('Dosis')
                                ->placeholder('500 mg'),
                            TextInput::make('frequency')
                                ->label('Frecuencia')
                                ->placeholder('Cada 8 horas'),
                            TextInput::make('duration')
                                ->label('Duración')
                                ->placeholder('5 días'),
                            Select::make('route')
                                ->label('Vía')
                                ->options([
                                    'Oral' => 'Oral',
                                    'IM' => 'IM',
                                    'IV' => 'IV',
                                    'Topical' => 'Tópica',
                                ]),
                            Textarea::make('instructions')
                                ->label('Indicaciones')
                                ->rows(2),
                        ]),
                    Toggle::make('complete_visit')
                        ->label('Marcar visita como completada')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $prescription = Prescription::create([
                        'medical_record_id' => $this->record->medical_record_id,
                        'doctor_id' => Auth::id(),
                        'issue_date' => now()->toDateString(),
                        'diagnosis' => $data['diagnosis'],
                        'notes' => $data['treatment_plan'] ?? null,
                        'items' => $data['items'] ?? [],
                    ]);

                    if (($data['complete_visit'] ?? true) === true) {
                        $this->record->update(['status' => AppointmentStatus::COMPLETED]);
                    }

                    Notification::make()
                        ->title('Consulta registrada')
                        ->success()
                        ->send();

                    $this->redirect(route('prescription.download', ['prescriptionId' => $prescription->id, 'copyType' => 'patient']));
                }),
        ];
    }
        public function getTitle(): string
    {
        // La variable $this->record contiene la visita que se está viendo.
        // Construimos un título más descriptivo.
        return "Detalles de la Visita #{$this->getRecord()->ticket_number}";
    }
}
