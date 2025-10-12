<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatus; // <-- Importar el nuevo Enum
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Appointment extends Model
{
    use HasFactory;

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
     * Lógica para generar tickets walk-in automáticamente
     */
    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            // Si no se proporciona ticket_number, generar uno local (walk-in)
            if (empty($appointment->ticket_number)) {
                $appointment->ticket_number = self::generateWalkInTicket();
            }
        });
    }

    /**
     * Genera un ticket local para pacientes walk-in
     */
    public static function generateWalkInTicket(): string
    {
        $year = now()->format('Y');
        $prefix = 'LOCAL-' . $year . '-';

        // Obtiene el último ticket del año y calcula el siguiente correlativo
        $lastTicket = self::where('ticket_number', 'like', $prefix . '%')
            ->orderBy('ticket_number', 'desc')
            ->value('ticket_number');

        $nextNumber = 1;
        if (! empty($lastTicket)) {
            $parts = explode('-', $lastTicket);
            $lastNumber = (int) ($parts[count($parts) - 1] ?? 0);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica si el ticket es de tipo walk-in
     */
    public function isWalkIn(): bool
    {
        return str_starts_with($this->ticket_number, 'LOCAL-');
    }

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
