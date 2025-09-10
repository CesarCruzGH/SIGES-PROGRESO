<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Cambiado
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Patient extends Model
{
    protected $fillable = [
        'medical_record_number',
        'full_name',
        'date_of_birth',
        // 'age', // Se elimina, se calcula con el accesor
        'sex',
        'patient_type',
        // 'service', // Se elimina
        'curp', // Añadido
        'employee_status', // Añadido
        'shift', // Añadido
        'visit_type', // Añadido
        'has_disability', // Añadido
        'disability_details', // Añadido
        'locality', // Añadido
        'tutor_id', // Añadido
        'attending_doctor_id', // Añadido
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'has_disability' => 'boolean', // Añadido
    ];

    // Este accesor ahora es la única fuente de verdad para la edad. ¡Perfecto!
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    /**
     * Un Paciente ahora PERTENECE A un Tutor.
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    /**
     * Un Paciente es atendido por un Médico (que es un Usuario).
     */
    public function attendingDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attending_doctor_id');
    }

    public function somatometricReadings(): HasMany
    {
        return $this->hasMany(SomatometricReading::class);
    }
}