<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

// Enums
use App\Enums\PatientType;
use App\Enums\EmployeeStatus;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'record_number',
        'patient_type',
        'employee_status',
        'consent_form_path',
    ];

    protected $casts = [
        'patient_type' => PatientType::class,
        'employee_status' => EmployeeStatus::class,
        'consent_form_path' => 'encrypted',
    ];

    protected static function booted(): void
    {
        static::creating(function (MedicalRecord $record) {
            if (empty($record->record_number)) {
                $nextValResult = DB::select("select nextval('medical_record_number_seq')");
                $nextVal = $nextValResult[0]->nextval;
                $record->record_number = 'EXP-' . str_pad($nextVal, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relaciones
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalLeaves()
    {
        return $this->hasMany(MedicalLeave::class);
    }

    public function somatometricReadings()
    {
        return $this->hasMany(SomatometricReading::class);
    }

    public function nursingAssessment() {
    return $this->hasOne(NursingAssessment::class);
    }
    public function medicalDocuments() {
        return $this->hasMany(MedicalDocument::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}

