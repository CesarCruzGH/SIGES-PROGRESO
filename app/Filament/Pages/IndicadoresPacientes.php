<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class IndicadoresPacientes extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';
    protected static ?string $title = 'Indicadores — Pacientes';
    protected static ?string $navigationLabel = 'Indicadores (Pacientes)';
    protected static ?string $slug = 'reportes/indicadores-pacientes';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ApexTiposDePacienteChart::class,
            \App\Filament\Widgets\ApexPacientesNuevosVSRecurrentes::class,
        ];
    }
}