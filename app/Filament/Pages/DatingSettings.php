<?php

namespace App\Filament\Pages;

use App\Models\DatingSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class DatingSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = DatingSetting::instance()->toArray();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Swipe Limits')
                    ->columns(2)
                    ->schema([
                        TextInput::make('daily_swipe_limit')
                            ->label('Daily Swipe Limit (Free Users)')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('daily_super_like_limit')
                            ->label('Daily Super Like Limit (Free Users)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                    ]),
                Section::make('Boost Settings')
                    ->columns(1)
                    ->schema([
                        TextInput::make('boost_duration_minutes')
                            ->label('Boost Duration (minutes)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->suffix('minutes'),
                    ]),
                Section::make('Verification')
                    ->columns(1)
                    ->schema([
                        Toggle::make('verification_required_for_swiping')
                            ->label('Require verification photo before swiping'),
                    ]),
                Actions::make([
                    Action::make('save')
                        ->label('Save Settings')
                        ->action('save'),
                ]),
            ]);
    }

    public function save(): void
    {
        $settings = DatingSetting::instance();
        $settings->update($this->data);

        cache()->forget('dating_settings');

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
