<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AppointmentStatus : string implements HasLabel, HasColor
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pendiente',
            self::IN_PROGRESS => 'En consulta',
            self::COMPLETED => 'Completada',
            self::CANCELLED => 'Cancelada',
        };
    }
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'icon', // Azul
            self::IN_PROGRESS => 'warning', // Naranja/Amarillo
            self::COMPLETED => 'success', // Verde
            self::CANCELLED => 'danger',  // Rojo
        };
    }
}
