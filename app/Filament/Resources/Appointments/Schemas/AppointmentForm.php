<?php

namespace App\Filament\Resources\Appointments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ticket_number')
                    ->required(),
                Select::make('medical_record_id')
                    ->label('Expediente Médico')
                    ->relationship('medicalRecord', 'record_number')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('service_id')->required()->numeric(),
                Textarea::make('reason_for_visit')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('doctor_id')->numeric(),
                TextInput::make('clinic_room_number'),
                DateTimePicker::make('appointment_time')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
            ]);
    }
}
