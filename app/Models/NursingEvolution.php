<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class NursingEvolution extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'medical_record_id',
        'appointment_id',
        'user_id',
        'problem',
        'subjective',
        'objective',
        'analysis',
        'plan',
        'somatometric_reading_id',
    ];

    protected $casts = [
        'problem' => 'encrypted',
        'subjective' => 'encrypted',
        'objective' => 'encrypted',
        'analysis' => 'encrypted',
        'plan' => 'encrypted',
    ];

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function somatometricReading(): BelongsTo
    {
        return $this->belongsTo(SomatometricReading::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('medical')->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}