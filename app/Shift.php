<?php

namespace App\Enums;

enum Shift: string
{
    case MORNING = 'matutino';
    case EVENING = 'vespertino';
    case NIGHT = 'nocturno';
    case WEEKEND = 'fin_de_semana';
}