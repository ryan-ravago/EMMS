<?php

namespace App\Filament\Helper;

use App\Models\Usr;
use Closure;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * Filament's profile page reduced to a password form. The password is not on AppUser:
 * it lives in the company users directory (usr.userPassword), matched by email.
 */
class ChangePassword extends EditProfile
{
    public static function getLabel(): string
    {
        return 'Change Password';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getPasswordFormComponent()->required(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label('Current password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->dehydrated(false)
            ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                if (! Hash::check((string) $value, (string) $this->getCompanyAccount()->userPassword)) {
                    $fail('The current password is incorrect.');
                }
            });
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // $data['password'] is already hashed by the form field.
        $this->getCompanyAccount()->update(['userPassword' => $data['password']]);

        return $record;
    }

    private function getCompanyAccount(): Usr
    {
        return Usr::where('email', $this->getUser()->user_email)->firstOrFail();
    }
}
