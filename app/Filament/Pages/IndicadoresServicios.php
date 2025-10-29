<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class IndicadoresServicios extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';
    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';
    protected static ?string $title = 'Indicadores — Servicios';
    protected static ?string $navigationLabel = 'Indicadores (Servicios)';
    protected static ?string $slug = 'reportes/indicadores-servicios';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ApexServiciosMasSolicitadosChart::class,
            \App\Filament\Widgets\ApexVisitasPorMedicoChart::class,
        ];
    }
}