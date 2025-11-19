<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MedicalInitialAssessment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'medical_record_id',
        'user_id',
        'allergies',
        'personal_pathological_history',
        'gyneco_obstetric_history',
        'current_illness',
        'physical_exam',
        'diagnosis',
        'treatment_note',
    ];

    protected $casts = [
        'allergies' => 'encrypted',
        'personal_pathological_history' => 'encrypted',
        'gyneco_obstetric_history' => 'encrypted',
        'current_illness' => 'encrypted',
        'physical_exam' => 'encrypted',
        'diagnosis' => 'encrypted',
        'treatment_note' => 'encrypted',
    ];

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
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