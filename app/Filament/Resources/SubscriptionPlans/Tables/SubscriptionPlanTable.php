<?php

namespace App\Filament\Resources\SubscriptionPlans\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionPlanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug'),
                TextColumn::make('price')
                    ->money('USD'),
                TextColumn::make('duration_days')
                    ->suffix(' days'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('user_subscriptions_count')
                    ->label('Subscribers')
                    ->counts('userSubscriptions'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->defaultSort('price');
    }
}
