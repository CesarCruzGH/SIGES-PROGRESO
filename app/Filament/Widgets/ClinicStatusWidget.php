<?php

namespace App\Filament\Widgets;

use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ClinicStatusWidget extends Widget
{
    protected string $view = 'filament.widgets.clinic-status-widget';
    protected int | string | array $columnSpan = 'full';

    public array $clinics = [
        ['id' => 1, 'name' => 'Consultorio 1 - Medicina General', 'is_active' => true, 'queue_count' => 5],
        ['id' => 2, 'name' => 'Consultorio 2 - Nutrición', 'is_active' => false, 'queue_count' => 0],
        ['id' => 3, 'name' => 'Consultorio 3 - Dental', 'is_active' => true, 'queue_count' => 2],
    ];

    public function updatedClinics($value, $key): void
    {
        [$index, $property] = explode('.', $key);

        if ($property === 'is_active') {
            // --- CÓDIGO DE VALIDACIÓN INTEGRADO AQUÍ ---
            
            $clinic = $this->clinics[$index];
            $newStatus = (bool) $value;

            // VALIDACIÓN CLAVE: "Preguntar antes de actuar"
            // Si hay pacientes en cola Y se está intentando CERRAR el consultorio...
            if ($clinic['queue_count'] > 0 && $newStatus === false) {
                
                // 1. Mostramos una notificación de error clara al usuario.
                Notification::make()
                    ->title('Acción Bloqueada')
                    ->body('No se puede cerrar un consultorio con pacientes en la cola de espera.')
                    ->danger()
                    ->send();

                // 2. Revertimos el interruptor a su posición original (encendido).
                // Livewire se encargará de actualizar la vista automáticamente.
                $this->clinics[$index]['is_active'] = true;

                // 3. Detenemos la ejecución. El resto del código no se ejecutará.
                return;
            }

            // Si la validación pasa, continuamos con la llamada a la API...
            try {
                $response = Http::post('https://api.sistemadeturnos.com/clinics/update-status', [
                    'clinic_id' => $clinic['id'],
                    'is_active' => $newStatus ? 1 : 0,
                ]);

                $response->throw();

                Notification::make()
                    ->title('Estado del consultorio actualizado')
                    ->success()->send();

            } catch (\Exception $e) {
                // Si la API falla, revertimos el cambio y notificamos.
                $this->clinics[$index]['is_active'] = !$newStatus;

                Notification::make()
                    ->title('Error de Comunicación')
                    ->body('No se pudo comunicar con el sistema de turnos. Intente de nuevo.')
                    ->danger()->send();
            }
        }
    }
}
