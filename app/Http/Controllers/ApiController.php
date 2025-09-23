<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * El "Traductor de Identidades".
     * Recibe un identificador único compartido (CURP o N° Expediente)
     * y devuelve nuestro ID interno para ese paciente.
     * Este es el PRIMER paso obligatorio antes de registrar una cita.
     */
    public function searchPatient(Request $request): JsonResponse
    {
        $curp = $request->query('curp');
        $mrn = $request->query('medical_record_number');

        if (!$curp && !$mrn) {
            return response()->json(['message' => 'Se requiere un CURP o número de expediente para la búsqueda.'], 400);
        }

        $patient = Patient::query()
            ->when($curp, fn ($query, $curp) => $query->where('curp', $curp))
            ->when($mrn, fn ($query, $mrn) => $query->where('medical_record_number', $mrn))
            ->select('id', 'full_name', 'curp', 'medical_record_number')
            ->first();

        if (!$patient) {
            return response()->json(['message' => 'Paciente no encontrado en SIGES-PROGRESO.'], 404);
        }

        return response()->json($patient);
    }

    /**
     * Registra una nueva cita.
     * Es el SEGUNDO paso, y requiere el `patient_id` que se obtuvo de `searchPatient`.
     */
    public function storeAppointment(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'ticket_number' => ['required', 'string', 'max:255', 'unique:appointments,ticket_number'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'], // El ID de NUESTRO sistema
            'service_id' => ['required', 'integer', 'exists:services,id'], // El ID de NUESTRO catálogo de servicios
            'reason_for_visit' => ['required', 'string'],
            'clinic_room_number' => ['nullable', 'string', 'max:255'],
        ]);

        $appointment = Appointment::create($validatedData);

        return response()->json([
            'message' => 'Cita registrada exitosamente en SIGES-PROGRESO.',
            'appointment_id' => $appointment->id,
        ], 201);
    }

    /**
     * Gestiona la solicitud de un nuevo expediente para un paciente no encontrado.
     */
    public function requestNewPatient(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'sex' => ['required', 'string', 'max:50'],
            'curp' => ['nullable', 'string', 'max:18', 'unique:patients,curp'],
        ]);

        $validatedData['status'] = 'pending_review';

        $patient = Patient::create($validatedData);

        return response()->json([
            'message' => 'Solicitud de expediente recibida. El ID es provisional y está pendiente de revisión por la recepcionista.',
            'patient_id' => $patient->id,
        ], 201);
    }
}