<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use App\Filament\Resources\MedicalRecords\MedicalRecordResource;
use App\Models\Patient;
use Filament\Resources\Pages\CreateRecord;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;
    protected static ?string $maxWidth = 'full';

    protected function handleRecordCreation(array $data): Patient
    {
        $curp = $data['curp'] ?? null;
        $curpHash = $curp ? hash('sha256', strtoupper(trim($curp))) : null;

        if ($curpHash) {
            $existing = Patient::where('curp_hash', $curpHash)->first();
            if ($existing) {
                return $existing;
            }
        }

        try {
            return static::getModel()::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($curpHash) {
                $existing = Patient::where('curp_hash', $curpHash)->first();
                if ($existing) {
                    return $existing;
                }
            }
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return MedicalRecordResource::getUrl('index');
    }
}
