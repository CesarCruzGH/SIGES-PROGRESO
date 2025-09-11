<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
class PatientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                Section::make('Información Personal')
                ->columns()
                ->schema([
                    TextEntry::make('full_name')->label('Nombre Completo'),
                    TextEntry::make('medical_record_number')->label('N° Expediente'),
                    TextEntry::make('age')->label('Edad'),
                    TextEntry::make('date_of_birth')->label('Fecha de Nacimiento')->date(),
                    TextEntry::make('curp')->label('CURP'),
                    TextEntry::make('sex')->label('Sexo'),
                    TextEntry::make('locality')->label('Localidad'),
                ]),

                Section::make('Clasificación y Detalles')
                ->columns()
                ->schema([
                    TextEntry::make('patient_type')->label('Tipo de Paciente')->badge(),
                    TextEntry::make('employee_status')->label('Estatus Empleado')->badge(),
                    TextEntry::make('attendingDoctor.name')->label('Médico que Atiende'),
                    TextEntry::make('visit_type')->label('Tipo de Visita'),
                    IconEntry::make('has_disability')->label('Tiene Discapacidad')->boolean(),
                    TextEntry::make('disability_details')->label('Detalles de Discapacidad'),
                ]),

                Section::make('Información del Tutor')
                ->visible(fn ($record) => $record->tutor()->exists())
                ->columns()
                ->schema([
                    TextEntry::make('tutor.full_name')->label('Nombre del Tutor'),
                    TextEntry::make('tutor.relationship')->label('Parentesco'),
                    TextEntry::make('tutor.phone_number')->label('Teléfono del Tutor'),
                    TextEntry::make('tutor.address')->label('Dirección del Tutor'),
                ])

            ]);
    }
}
