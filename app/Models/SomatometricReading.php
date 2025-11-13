<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SomatometricReading extends Model
{
    use HasFactory;
    protected $fillable = [
        'medical_record_id',
        'appointment_id',
        'user_id',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'respiratory_rate',
        'temperature',
        'weight',
        'height',
        'blood_glucose',
        'oxygen_saturation',
        'observations',
    ];

    protected $casts = [
        'observations' => 'encrypted',
    ];

    // Relaciones
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

}
