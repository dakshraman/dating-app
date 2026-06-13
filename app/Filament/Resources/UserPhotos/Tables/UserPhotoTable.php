<?php

namespace App\Filament\Resources\UserPhotos\Tables;

use Filament\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UserPhotoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('photo_url')
                    ->limit(40)
                    ->searchable(),
                IconColumn::make('is_approved')
                    ->boolean()
                    ->label('Approved'),
                IconColumn::make('is_primary')
                    ->boolean()
                    ->label('Primary'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([])
            ->toolbarActions([
                BulkAction::make('approve')
                    ->label('Approve selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Collection $records) => $records->each->update(['is_approved' => true]))
                    ->requiresConfirmation(),
                BulkAction::make('reject')
                    ->label('Reject selected')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn (Collection $records) => $records->each->delete())
                    ->requiresConfirmation(),
            ]);
    }
}
