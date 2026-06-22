<?php

namespace App\Filament\Resources\Reports\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reporter.name')
                    ->label('Reported By')
                    ->searchable(),
                TextColumn::make('reported.name')
                    ->label('Reported User')
                    ->searchable(),
                TextColumn::make('reason')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('details')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}
