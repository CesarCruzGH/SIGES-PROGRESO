<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification; // <-- Importar Notificaciones
use App\Filament\Resources\Appointments\AppointmentResource; // <-- Importar AppointmentResource

class EditPatient extends EditRecord
{
    protected static string $resource = PatientResource::class;
    // --- NUEVA PROPIEDAD PARA GUARDAR LA "ETIQUETA" ---
    public ?int $appointment_id = null;

    protected static ?string $maxWidth = 'full';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        // Si la URL tiene el parámetro ?appointment_id=..., lo guardamos.
        if (request()->has('appointment_id')) {
            $this->appointment_id = (int) request()->get('appointment_id');
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. Verificamos el estado actual del paciente que estamos editando.
        //    La variable `$this->record` contiene el modelo del paciente.
        if ($this->record->status === 'pending_review') {
            // 2. Si está pendiente, forzamos el estado a 'active'.
            $data['status'] = 'active';

            // 3. (Opcional pero recomendado) Enviamos una notificación de éxito.
            Notification::make()
                ->title('Expediente Activado')
                ->body('El expediente del paciente ha sido completado y activado correctamente.')
                ->success()
                ->send();
        }

        return $data;
    }

        /**
     * ¡LA LÓGICA DE REDIRECCIÓN INTELIGENTE!
     * Este método decide a dónde ir después de guardar.
     */
    protected function getRedirectUrl(): string
    {
        // Si tenemos un ID de visita "etiquetado"...
        if ($this->appointment_id) {
            // ...construimos la URL para la página de edición de esa visita.
            return AppointmentResource::getUrl('edit', ['record' => $this->appointment_id]);
        }

        // Si no, hacemos lo de siempre: volver a la lista de pacientes.
        return $this->getResource()::getUrl('index');
    }

}
