<?php

namespace App\Filament\Resources\UserPhotos\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class UserPhotoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                ImageColumn::make('photo_url')
                    ->label('Photo')
                    ->square()
                    ->size(80),
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
            ->recordActions([
                Action::make('view_photo')
                    ->label('View Photo')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn ($record) => new HtmlString(
                        '<div style="display:flex;justify-content:center;align-items:flex-start;min-height:50vh;overflow:auto;">'.
                        '<img src="'.e($record->photo_url).'" '.
                        'style="max-width:100%;max-height:80vh;object-fit:contain;border-radius:8px;cursor:zoom-in;transition:all 0.2s;" '.
                        'onclick="var img=this;if(img._full){img.style.maxWidth=\'100%\';img.style.maxHeight=\'80vh\';img._full=false;img.style.cursor=\'zoom-in\'}else{img.style.maxWidth=\'none\';img.style.maxHeight=\'none\';img._full=true;img.style.cursor=\'zoom-out\'}" />'.
                        '</div>'
                    ))
                    ->modalHeading('Photo Preview')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('7xl'),
            ])
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
