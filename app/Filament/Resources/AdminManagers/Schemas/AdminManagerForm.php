<?php

namespace App\Filament\Resources\AdminManagers\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Exists;

class AdminManagerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_fname')
                    ->label('First Name')
                    ->required(),
                TextInput::make('user_mname')
                    ->label('Middle Name'),
                TextInput::make('user_lname')
                    ->label('Last Name')
                    ->required(),
                TextInput::make('user_email')
                    ->label('Email')
                    ->email()
                    ->required(),
                Select::make('user_dep_id')
                    ->label('Department')
                    ->relationship(
                        'department',
                        'dep_name',
                        fn(Builder $query) => $query->where('is_maintenance', 0)
                    )
                    ->exists('departments', 'dep_id', modifyRuleUsing: fn(Exists $rule) => $rule->where('is_maintenance', 0))
                    ->native(false)
                    ->required(),
                // ->createOptionForm([
                //     TextInput::make('dep_code')
                //         ->required()
                //         ->unique('departments', 'dep_code')
                //         ->maxLength(10)
                //         ->label('Code'),

                //     TextInput::make('dep_name')
                //         ->required()
                //         ->label('Name'),
                // ])
                // ->createOptionAction(function (Action $action) {
                //     return $action
                //         ->modalHeading('Add New Department')
                //         ->modalWidth('xs'); // xs, sm, md, lg, xl, 2xl
                // }),
                TextInput::make('user_contact_no')
                    ->label('Contact #'),
                TextInput::make('user_fb_profile_link')
                    ->label('Facebook Profile Link'),
            ]);
    }
}
