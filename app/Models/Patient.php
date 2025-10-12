<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Cambiado
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

//enums
use App\Enums\Locality;

use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Patient extends Model
{
    use HasFactory;
    protected $fillable = [
        'tutor_id',
        'full_name',
        'date_of_birth',
        'sex',
        'curp',
        'locality',
        'contact_phone',
        'address',
        'has_disability',
        'disability_details',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'has_disability' => 'boolean',
        'locality' => Locality::class,
        'status' => 'string',

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
     * Relación uno a uno con su expediente médico.
     */
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }
    protected static function booted(): void
    {

        static::created(function (Patient $patient) {

            $patient->medicalRecord()->create([]);
        });
    }
}