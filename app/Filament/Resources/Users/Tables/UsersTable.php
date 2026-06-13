<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gender')
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('profile_photo')
                    ->searchable(),
                TextColumn::make('verification_photo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_verified')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('is_banned')
                    ->boolean()
                    ->color(fn ($state): string => $state ? 'danger' : 'success')
                    ->label('Banned'),
                TextColumn::make('last_active_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('ban')
                        ->label('Ban selected')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'is_banned' => true,
                                'banned_at' => now(),
                                'ban_reason' => 'Banned by admin',
                            ]);
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unban')
                        ->label('Unban selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'is_banned' => false,
                                'banned_at' => null,
                                'ban_reason' => null,
                            ]);
                        })
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
