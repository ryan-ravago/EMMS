<?php

namespace App\Filament\Resources\AppUsers;

use App\Filament\Resources\AppUsers\Pages\CreateAppUser;
use App\Filament\Resources\AppUsers\Pages\EditAppUser;
use App\Filament\Resources\AppUsers\Pages\ListAppUsers;
use App\Filament\Resources\AppUsers\Pages\ViewAppUser;
use App\Filament\Resources\AppUsers\Schemas\AppUserForm;
use App\Filament\Resources\AppUsers\Schemas\AppUserInfolist;
use App\Filament\Resources\AppUsers\Tables\AppUsersTable;
use App\Models\AppUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AppUserResource extends Resource
{
    protected static ?string $model = AppUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = "Users";
    protected static ?string $modelLabel = 'User'; // Singular

    protected static ?string $pluralModelLabel = 'Users'; // Plural

    protected static ?string $recordTitleAttribute = 'user_fname';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        // $user = auth()->user();

        // return ($user && $user->roles()->count() > 1)
        //     ? 'Super Admin'
        //     : null;
        return 'Super Admin';
    }

    public static function form(Schema $schema): Schema
    {
        return AppUserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppUserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppUsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppUsers::route('/'),
            'create' => CreateAppUser::route('/create'),
            'view' => ViewAppUser::route('/{record}'),
            'edit' => EditAppUser::route('/{record}/edit'),
        ];
    }
}
