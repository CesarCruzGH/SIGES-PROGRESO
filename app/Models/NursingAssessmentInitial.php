<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class NursingAssessmentInitial extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'nursing_assessments_initial';

    protected $fillable = [
        'medical_record_id',
        'user_id',
        'somatometric_reading_id',
        'notes',
    ];

    protected $casts = [
        'notes' => 'encrypted',
    ];

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function somatometricReading(): BelongsTo
    {
        return $this->belongsTo(SomatometricReading::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('medical')->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}