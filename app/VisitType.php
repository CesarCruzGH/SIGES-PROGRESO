<?php

namespace App\Enums;

enum VisitType: string
{
    case FIRST_TIME = 'primera_vez';
    case SUBSEQUENT = 'subsecuente';
}