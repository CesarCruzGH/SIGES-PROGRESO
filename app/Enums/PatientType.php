<?php

namespace App\Enums;

enum PatientType: string
{
    case EXTERNAL = 'externo';
    case EMPLOYEE = 'empleado';
    case EMPLOYEE_DEPENDENT = 'hijo_de_empleado';
    /* AÑADIR ESTE MÉTODO
    public static function getOptions(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
    */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            // La clave es el valor que se guarda (ej: 'empleado')
            // El valor es una etiqueta amigable para el usuario (ej: 'Empleado')
            $options[$case->value] = ucfirst(str_replace('_', ' ', strtolower($case->name)));
        }
        return $options;
    }
}