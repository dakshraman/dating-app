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
                TextInput::make('verification_photo'),
                Toggle::make('is_verified'),
                Toggle::make('is_active'),
                Toggle::make('is_banned')
                    ->label('Banned')
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $set('banned_at', now()->toDateTimeString());
                        } else {
                            $set('banned_at', null);
                            $set('ban_reason', null);
                        }
                    }),
                DateTimePicker::make('banned_at'),
                Textarea::make('ban_reason')
                    ->label('Ban Reason'),
                DateTimePicker::make('last_active_at'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('fcm_token')
                    ->columnSpanFull(),
            ]);
    }
}
