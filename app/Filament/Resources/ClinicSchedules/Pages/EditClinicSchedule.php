<?php

namespace App\Filament\Resources\ClinicSchedules\Pages;

use App\Filament\Resources\ClinicSchedules\ClinicScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClinicSchedule extends EditRecord
{
    protected static string $resource = ClinicScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
