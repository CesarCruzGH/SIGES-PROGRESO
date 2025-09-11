<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case UNIONIZED = 'sindicalizado';
    case TRUSTED = 'confianza';
    // AÑADIR ESTE MÉTODO
    public static function getOptions(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}