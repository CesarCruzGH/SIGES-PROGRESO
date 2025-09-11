<?php

namespace App\Enums;

enum Shift: string
{
    case MORNING = 'matutino';
    case EVENING = 'vespertino';
    case NIGHT = 'nocturno';
    case WEEKEND = 'fin_de_semana';
    // AÑADIR ESTE MÉTODO
    public static function getOptions(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}