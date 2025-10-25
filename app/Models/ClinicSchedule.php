<?php

namespace App\Models;

use App\Enums\Shift; // Make sure you have this Enum
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_name',
        'user_id',
        'service_id',
        'shift',
        'date',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
        'shift' => Shift::class,
    ];

    // A schedule belongs to one doctor (User)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // A schedule is for one specific Service
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}