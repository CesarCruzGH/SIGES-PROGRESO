<?php

namespace App\Http\Controllers;

// --- Imports necesarios ---
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Filament\Actions\Action;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction; // Alias para la acción de la notificación
use App\Filament\Resources\Appointments\AppointmentResource;
class ApiController extends Controller
{
    /**
     * El "Traductor de Identidades" actualizado.
     * Ahora devuelve explícitamente el N° de Expediente.
     */
    public function searchPatient(Request $request): JsonResponse
    {
        $curp = $request->query('curp');
        // El otro sistema ahora buscará por 'record_number'
        $recordNumber = $request->query('record_number'); 

        if (!$curp && !$recordNumber) {
            return response()->json(['message' => 'Se requiere un CURP o número de expediente para la búsqueda.'], 400);
        }

        $patientQuery = Patient::query();

        if ($curp) {
            $patientQuery->where('curp', $curp);
        }
        
        if ($recordNumber) {
            // Buscamos a través de la relación del expediente
            $patientQuery->whereHas('medicalRecord', function ($query) use ($recordNumber) {
                $query->where('record_number', $recordNumber);
            });
        }
        
        // Cargamos la relación para poder acceder al número de expediente
        $patient = $patientQuery->with('medicalRecord')->first();

        if (!$patient) {
            return response()->json(['message' => 'Paciente no encontrado en SIGES-PROGRESO.'], 404);
        }

        return response()->json([
            'id' => $patient->id,
            'full_name' => $patient->full_name,
            'curp' => $patient->curp,
            'contact_phone' => $patient->contact_phone,
            // Devolvemos el N° de Expediente, que es la clave para la siguiente llamada
            'record_number' => $patient->medicalRecord->record_number, 
        ]);
    }

    /**
     * El método "Todo en Uno" refactorizado para la nueva arquitectura.
     */
    public function storeVisit(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'record_number' => ['nullable', 'string', 'exists:medical_records,record_number'],
            'ticket_number' => ['required', 'string', 'max:255', 'unique:appointments,ticket_number'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'reason_for_visit' => ['required', 'string'],
        ]);

        $medicalRecord = null;
        $newPatientCreated = false;

        DB::beginTransaction();
        try {
            if (isset($validatedData['record_number'])) {
                $medicalRecord = MedicalRecord::where('record_number', $validatedData['record_number'])->firstOrFail();
            } else {
                $patient = Patient::create(['status' => 'pending_review']);
                $medicalRecord = $patient->medicalRecord;
                $newPatientCreated = true;
            }

            $appointment = $medicalRecord->appointments()->create([
                'ticket_number' => $validatedData['ticket_number'],
                'service_id' => $validatedData['service_id'],
                'reason_for_visit' => $validatedData['reason_for_visit'],
            ]);
            
            DB::commit();

            // --- LÓGICA DE NOTIFICACIÓN CORREGIDA ---
            // Para pruebas, notificamos a TODOS los usuarios.
            $recipients = User::all(); 
            // Buscamos solo a los usuarios con el rol de recepcionista.
            //$recipients = User::where('role', 'recepcionista')->get();
            Notification::make()
                ->title('Nueva Visita Registrada')
                ->body("Se ha registrado una nueva visita con el ticket #{$appointment->ticket_number} para el servicio de '{$appointment->service->name}' con motivo de '{$appointment->reason_for_visit}'")
                ->icon('heroicon-s-ticket')
                ->info()
                ->duration(8000)
                ->actions([
                    // Usamos el alias que definimos arriba
                    Action::make('view')
                        ->label('Ver Visita')
                        ->url(AppointmentResource::getUrl('view', ['record' => $appointment]))
                        ->markAsRead()
                        ->button(),
                ])
                ->sendToDatabase($recipients);

            return response()->json([
                'message' => 'Visita registrada exitosamente en SIGES-PROGRESO.',
                'appointment_id' => $appointment->id,
                'medical_record_id' => $medicalRecord->id,
                'new_patient_created' => $newPatientCreated,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error interno al procesar la visita.', 'error' => $e->getMessage()], 500);
        }
    }
}