<?php

namespace App\Enums;

enum VisitType: string
{
    case FIRST_TIME = 'primera_vez';
    case SUBSEQUENT = 'subsecuente';
    // AÑADIR ESTE MÉTODO
    public static function getOptions(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}