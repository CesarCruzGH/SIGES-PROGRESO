<?php

namespace App\Filament\Resources\ClinicSchedules;

use App\Filament\Resources\ClinicSchedules\Pages\CreateClinicSchedule;
use App\Filament\Resources\ClinicSchedules\Pages\EditClinicSchedule;
use App\Filament\Resources\ClinicSchedules\Pages\ListClinicSchedules;
use App\Filament\Resources\ClinicSchedules\Pages\DaySchedule;
use App\Filament\Resources\ClinicSchedules\Schemas\ClinicScheduleForm;
use App\Filament\Resources\ClinicSchedules\Tables\ClinicSchedulesTable;
use App\Models\ClinicSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClinicScheduleResource extends Resource
{
    protected static ?string $model = ClinicSchedule::class;
    // --- UX Improvements ---
    protected static ?string $navigationLabel = 'Horario de Consultorios';
    protected static ?string $pluralModelLabel = 'Horarios de Consultorios';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $recordTitleAttribute = 'clinic_name';

    public static function form(Schema $schema): Schema
    {
        return ClinicScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClinicSchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClinicSchedules::route('/'),
            'day' => DaySchedule::route('/day'),
            'create' => CreateClinicSchedule::route('/create'),
            'edit' => EditClinicSchedule::route('/{record}/edit'),
        ];
    }
    public static function getNavigationSort(): ?int
    {
    return 1;
    }

    
}
