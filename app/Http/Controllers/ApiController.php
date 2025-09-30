<?php

namespace App\Http\Controllers;

use App\Enums\PatientType;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
            // Ya no esperamos datos del paciente. solo el N° de Expediente si existe.
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
                // Caso 1: Paciente existente. Lo encontramos.
                $medicalRecord = MedicalRecord::where('record_number', $validatedData['record_number'])->firstOrFail();
            } else {
                // --- ¡LA NUEVA LÓGICA CLAVE! ---
                // Caso 2: Paciente nuevo. No vienen datos personales.
                
                // a) Creamos el "paciente fantasma". Estará casi vacío.
                $patient = Patient::create([
                    'status' => 'pending_review', // Marcado para la recepcionista.
                    // full_name, date_of_birth, etc., son nullable, así que no hay problema.
                ]);

                // b) El evento 'created' del modelo Patient crea automáticamente el MedicalRecord.
                //    Simplemente lo recuperamos.
                $medicalRecord = $patient->medicalRecord;
                $newPatientCreated = true;
            }

            // c) Creamos la visita y la vinculamos al expediente (ya sea el encontrado o el nuevo).
            $appointment = $medicalRecord->appointments()->create([
                'ticket_number' => $validatedData['ticket_number'],
                'service_id' => $validatedData['service_id'],
                'reason_for_visit' => $validatedData['reason_for_visit'],
            ]);
            
            DB::commit();

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