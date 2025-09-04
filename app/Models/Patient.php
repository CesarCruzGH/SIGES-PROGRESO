<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Patient extends Model
{
    protected $fillable = [
        'medical_record_number',
        'full_name',
        'date_of_birth',
        'sex',
        'patient_type',
        'service',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    // Accesor para la edad
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    // Relaciones
    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class);
    }

    public function somatometricReadings(): HasMany
    {
        return $this->hasMany(SomatometricReading::class);
    }
}
