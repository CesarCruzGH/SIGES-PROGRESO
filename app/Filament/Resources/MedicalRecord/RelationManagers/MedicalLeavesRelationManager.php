<?php

namespace App\Filament\Resources\MedicalRecord\RelationManagers;

use App\Filament\Resources\Patients\RelationManagers\MedicalLeavesRelationManager as BaseManager;

class MedicalLeavesRelationManager extends BaseManager
{
    protected static string $relationship = 'medicalLeaves';
}


