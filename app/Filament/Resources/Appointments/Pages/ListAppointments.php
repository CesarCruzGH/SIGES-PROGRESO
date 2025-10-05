<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\AppointmentStatus;
use Filament\Schemas\Components\Tabs\Tab;
class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

 

    protected function getHeaderActions(): array
    {
        return [
           // CreateAction::make()->label('Crear Nueva Visita'),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Todas las Visitas'),
        ];

        foreach (AppointmentStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status))
                ->badge(AppointmentResource::getModel()::query()->where('status', $status)->count())
                ->badgeColor($status->getColor());
        }

        return $tabs;
    }
}
