<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatus; // <-- Importar el nuevo Enum
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    protected $fillable = [
        'medical_record_id',
        'service_id',
        'doctor_id',
        'ticket_number',
        'shift',
        'visit_type',
        'clinic_room_number',
        'reason_for_visit',
        'notes',
        'status',
    ];
    protected $casts = [
        // Conectamos el campo 'status' a nuestro Enum
        'status' => AppointmentStatus::class,
    ];

    /**
     * Relación: Una cita pertenece a un Expediente Médico.
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Relación: Una cita corresponde a un Servicio.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Relación: Una cita es atendida por un Doctor (Usuario).
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
