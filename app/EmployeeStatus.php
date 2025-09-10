<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case UNIONIZED = 'sindicalizado';
    case TRUSTED = 'confianza';
}