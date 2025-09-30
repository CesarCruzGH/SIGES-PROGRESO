<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListPatients extends ListRecords
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // --- NUEVA ACCIÓN DE DESCARGA ---
            Action::make('download_consent_template')
                ->label('Descargar Plantilla de Consentimiento')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(asset('storage/templates/consentimiento_plantilla.pdf'))
                ->openUrlInNewTab(),
            CreateAction::make(),
        ];
    }
}
