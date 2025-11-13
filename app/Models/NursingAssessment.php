<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NursingAssessment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'medical_record_id',
        'user_id',
        'allergies',
        'personal_pathological_history',
    ];

    protected $casts = [
        'allergies' => 'encrypted',
        'personal_pathological_history' => 'encrypted',
    ];

    /**
     * Relación: La valoración pertenece a un Expediente Médico.
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Relación: La valoración fue registrada por un Usuario (Enfermero/a).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
