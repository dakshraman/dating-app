<?php

namespace App\Filament\Pages;

use App\Models\DatingSetting;
use BackedEnum;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class DatingSettings extends Page
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected string $view = 'filament.pages.dating-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = DatingSetting::instance();
        $this->form->fill($settings->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
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
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = DatingSetting::instance();
        $settings->update($data);

        cache()->forget('dating_settings');

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
