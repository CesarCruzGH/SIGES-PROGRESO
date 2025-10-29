<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class IndicadoresVisitas extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';
    protected static ?string $title = 'Indicadores — Visitas';
    protected static ?string $navigationLabel = 'Indicadores (Visitas)';
    protected static ?string $slug = 'reportes/indicadores-visitas';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ApexEstadoVisitasChart::class,
            \App\Filament\Widgets\ApexVisitasSemanalesChart::class,
        ];
    }
}