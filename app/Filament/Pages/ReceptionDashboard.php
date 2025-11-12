<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

class ReceptionDashboard extends Dashboard
{
    protected static ?string $title = 'Panel de Recepción';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\RecepcionStats::class,
            \App\Filament\Widgets\QuickActionsWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\ColaRecepcionTable::class,
            \App\Filament\Widgets\UltimasVisitas::class,
        ];
    }
}
