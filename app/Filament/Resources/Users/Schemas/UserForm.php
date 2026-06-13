<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('gender'),
                DatePicker::make('birth_date'),
                Textarea::make('bio')
                    ->columnSpanFull(),
                TextInput::make('location'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('profile_photo'),
                Toggle::make('is_verified')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                DateTimePicker::make('last_active_at'),
                TextInput::make('phone')
                    ->tel(),
                Textarea::make('fcm_tokens')
                    ->columnSpanFull(),
            ]);
    }
}
