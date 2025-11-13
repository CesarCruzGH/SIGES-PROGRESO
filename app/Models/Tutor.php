<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Tutor extends Model
{
    use HasFactory;
    protected $fillable = [
        'full_name',
        'relationship',
        'phone_number',
        'address',
    ];

    protected $casts = [
        'full_name' => 'encrypted',
        'relationship' => 'encrypted',
        'phone_number' => 'encrypted',
        'address' => 'encrypted',
    ];

    // Relaciones
    public function patient()
    {
        return $this->hasMany(Patient::class);
    }
}
