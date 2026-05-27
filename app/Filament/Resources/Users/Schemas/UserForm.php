<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

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
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state)),
                Select::make('company_id')
                    ->relationship('company', 'name'),
                Select::make('roles')
                    ->label('Rol')
                    ->multiple()
                    ->preload()
                    ->relationship(
                        'roles',
                        'name',
                        fn ($query) => $query->where('guard_name', 'web')->orderBy('name')
                    )
                    ->getOptionLabelFromRecordUsing(fn (Role $record): string => $record->name)
                    ->searchable(),
            ]);
    }
}
