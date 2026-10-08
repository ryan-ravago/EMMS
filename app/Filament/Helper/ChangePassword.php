<?php

namespace App\Filament\Helper;

use App\Models\Usr;
use Closure;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Js;

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

    // Full page load back to home: this page uses the simple layout, and an SPA/history.back()
    // jump to the main layout leaves the old form on screen.
    protected function getCancelFormAction(): Action
    {
        return Action::make('back')
            ->label(__('filament-panels::auth/pages/edit-profile.actions.cancel.label'))
            ->alpineClickHandler('window.location.href = '.Js::from(filament()->getUrl()))
            ->color('gray');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // $data['password'] is already hashed by the form field. forceFill keeps Usr fully guarded.
        $this->getCompanyAccount()->forceFill(['userPassword' => $data['password']])->save();

        return $record;
    }

    // Filament only resets password and passwordConfirmation after saving.
    protected function afterSave(): void
    {
        $this->data['currentPassword'] = null;
    }

    private function getCompanyAccount(): Usr
    {
        return Usr::where('email', $this->getUser()->user_email)->firstOrFail();
    }
}
