<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

class ReceptionDashboard extends Dashboard
{
    protected static ?string $title = 'Panel de Recepción';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\UltimasVisitas::class,
        ];
    }

    public function getWidgets(): array
    {
        // Vaciar widgets del cuerpo del dashboard para que solo se muestre el encabezado.
        return [];
    }
}