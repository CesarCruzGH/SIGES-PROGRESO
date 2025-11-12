<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Prescription extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'medical_record_id',
        'doctor_id',
        'folio',
        'issue_date',
        'diagnosis',
        'notes',
        'items',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'items' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Prescription $prescription) {
            if (empty($prescription->doctor_id)) {
                $user = Auth::user();
                if ($user) {
                    $prescription->doctor_id = $user->id;
                }
            }

            if (empty($prescription->folio)) {
                $nextVal = DB::select("select nextval('prescription_folio_seq')")[0]->nextval;
                $prescription->folio = 'REC-' . now()->year . '-' . str_pad($nextVal, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
