<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\Pages\EditReport;
use App\Filament\Resources\Reports\Pages\ListReports;
use App\Filament\Resources\Reports\Schemas\ReportForm;
use App\Filament\Resources\Reports\Tables\ReportTable;
use App\Models\Report;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'Moderation';

    public static function form(Schema $schema): Schema
    {
        return ReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReports::route('/'),
            'edit' => EditReport::route('/{record}/edit'),
        ];
    }
}
