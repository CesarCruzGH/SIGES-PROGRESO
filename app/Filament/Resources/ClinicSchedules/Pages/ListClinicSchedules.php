<?php

namespace App\Filament\Resources\ClinicSchedules\Pages;

use App\Filament\Resources\ClinicSchedules\ClinicScheduleResource;
use App\Filament\Resources\ClinicSchedules\Pages\DaySchedule;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClinicSchedules extends ListRecords
{
    protected static string $resource = ClinicScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('Horario del Día')
                ->icon('heroicon-o-clock')
                ->url(DaySchedule::getUrl())
                ->color('primary'),
        ];
    }
}
