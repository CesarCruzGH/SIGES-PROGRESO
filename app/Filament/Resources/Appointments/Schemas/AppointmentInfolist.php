<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Prescription;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Nota de Consulta')
                ->columns(2)
                ->visible(fn ($record) => Prescription::where('medical_record_id', $record->medical_record_id)->exists())
                ->schema([
                    TextEntry::make('diagnosis')
                        ->label('Diagnóstico')
                        ->state(fn ($record) => Prescription::where('medical_record_id', $record->medical_record_id)->orderByDesc('id')->value('diagnosis'))
                        ->columnSpanFull(),
                    TextEntry::make('treatment_plan')
                        ->label('Plan de Tratamiento')
                        ->state(fn ($record) => Prescription::where('medical_record_id', $record->medical_record_id)->orderByDesc('id')->value('notes'))
                        ->columnSpanFull(),
                    RepeatableEntry::make('items')
                        ->label('Receta')
                        ->state(fn ($record) => Prescription::where('medical_record_id', $record->medical_record_id)->orderByDesc('id')->value('items') ?? [])
                        ->schema([
                            TextEntry::make('drug')->label('Medicamento'),
                            TextEntry::make('dose')->label('Dosis'),
                            TextEntry::make('frequency')->label('Frecuencia'),
                            TextEntry::make('duration')->label('Duración'),
                            TextEntry::make('route')->label('Vía'),
                            TextEntry::make('instructions')->label('Indicaciones'),
                        ])
                        ->columnSpanFull(),
                ]),
        ]);
    }
}

