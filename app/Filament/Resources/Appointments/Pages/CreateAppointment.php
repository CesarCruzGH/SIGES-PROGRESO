<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

        protected function getRedirectUrl(): string
    {
        // Tras guardar, redirige al listado principal de visitas
        return AppointmentResource::getUrl('index');
    }

}
