<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tutor extends Model
{
    protected $fillable = [
        'full_name',
        'relationship',
        'phone_number',
        'address',
    ];

    // Relaciones
    public function patient()
    {
        return $this->hasMany(Patient::class);
    }
}
