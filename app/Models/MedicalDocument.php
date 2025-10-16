<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalDocument extends Model
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
        'name',
        'file_path',
    ];

    /**
     * Relación: El documento pertenece a un Expediente Médico.
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Relación: El documento fue subido por un Usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}