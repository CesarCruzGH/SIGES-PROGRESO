<?php

namespace App\Enums;

enum PatientType: string
{
    case EXTERNAL = 'externo';
    case EMPLOYEE = 'empleado';
    case EMPLOYEE_DEPENDENT = 'hijo_de_empleado';
}