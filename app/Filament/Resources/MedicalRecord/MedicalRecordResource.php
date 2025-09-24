<?php

namespace App\Filament\Resources\MedicalRecord;

use App\Models\MedicalRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MedicalRecordResource extends Resource
{
    protected static ?string $model = MedicalRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Oculto';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\MedicalRecord\RelationManagers\MedicalLeavesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\MedicalRecord\Pages\ListMedicalRecords::route('/'),
            'create' => \App\Filament\Resources\MedicalRecord\Pages\CreateMedicalRecord::route('/create'),
            'edit' => \App\Filament\Resources\MedicalRecord\Pages\EditMedicalRecord::route('/{record}/edit'),
        ];
    }
}


