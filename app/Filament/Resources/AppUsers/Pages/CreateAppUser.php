<?php

namespace App\Filament\Resources\AppUsers\Pages;

use App\Filament\Resources\AppUsers\AppUserResource;
use App\Mail\EmmsAccessGrantedMail;
use App\Models\Usr;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CreateAppUser extends CreateRecord
{
    protected static string $resource = AppUserResource::class;

    protected function afterCreate(): void
    {
        try {
            if (! Usr::where('email', $this->record->user_email)->exists()) {
                Notification::make()
                    ->title('No company account found for this email')
                    ->body('They cannot sign in until IT creates it in the users directory (dbusers).')
                    ->warning()
                    ->persistent()
                    ->send();

                return;
            }

            if (! $this->record->is_active) {
                return;
            }

            Mail::to($this->record->user_email)->queue(new EmmsAccessGrantedMail($this->record));

            Notification::make()
                ->title('Access email sent')
                ->body("Emailed to {$this->record->user_email}.")
                ->success()
                ->send();
        } catch (Throwable $e) {
            report($e);

            Notification::make()
                ->title('User created, but the email could not be sent')
                ->warning()
                ->persistent()
                ->send();
        }
    }
}
