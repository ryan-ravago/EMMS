<?php

namespace App\Filament\Helper;

use App\Models\AppUser;
use App\Models\Usr;
use Filament\Auth\Pages\Login;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CustomLogin extends Login
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                // $this->getRememberFormComponent(),
            ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        $key = 'login-attempt:' . request()->ip();

        // 1. Check if they are locked out
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            Notification::make()
                ->title('Locked Out')
                ->body("Too many failed attempts. Please wait {$seconds}s.")
                ->danger()
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'data.email' => 'These credentials do not match our records.',
            ]);
        }

        try {
            // Get form data
            $data = $this->form->getState();
            $email = $data['email'];
            $password = $data['password'];

            // Step 1: Verify credentials against Usr model
            $usrUser = Usr::where('email', $email)->first();

            if (!$usrUser || !Hash::check($password, $usrUser->userPassword)) {
                $currentAttempts = RateLimiter::hit($key, 60);

                if (RateLimiter::attempts($key) >= 3) {
                    // STRIKE THREE: Lock out for 60 seconds
                    RateLimiter::clear($key);
                    RateLimiter::hit($key, 60);
                    RateLimiter::hit($key, 60);
                    RateLimiter::hit($key, 60);

                    Notification::make()
                        ->title('Too many failures')
                        ->body('You are now locked out for a full 60 seconds.')
                        ->danger()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Invalid Login')
                        ->body('Email or password is incorrect. Attempts remaining: ' . (3 - RateLimiter::attempts($key)))
                        ->warning()
                        ->send();
                }

                // throw ValidationException::withMessages([
                //     'data.email' => 'These credentials do not match our records.',
                // ]);

                return null;
            }

            // Step 2: Check if email exists in AppUser
            $appUser = AppUser::where('user_email', $email)->first();

            if (!$appUser) {
                $currentAttempts = RateLimiter::hit($key, 60);

                if (RateLimiter::attempts($key) >= 3) {
                    // STRIKE THREE: Lock out for 60 seconds
                    RateLimiter::clear($key);
                    RateLimiter::hit($key, 60);
                    RateLimiter::hit($key, 60);
                    RateLimiter::hit($key, 60);

                    Notification::make()
                        ->title('Too many failures')
                        ->body('You are now locked out for a full 60 seconds.')
                        ->danger()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Invalid Login')
                        ->body('User account not found. Attempts remaining: ' . (3 - RateLimiter::attempts($key)))
                        ->warning()
                        ->send();
                }

                // throw ValidationException::withMessages([
                //     'data.email' => 'These credentials do not match our records.',
                // ]);
                return null;
            }

            // Clear rate limiter on successful validation
            RateLimiter::clear($key);

            // Log in with AppUser (do this first)
            Auth::login($appUser, $data['remember'] ?? false);
            Filament::auth()->login($appUser);

            // Regenerate CSRF token only (not full session to avoid "page expired")
            request()->session()->regenerateToken();

            Notification::make()
                ->title('Welcome back!')
                ->body("Hello {$appUser->getFilamentName()}")
                ->success()
                ->send();

            // Use parent's redirect method to handle the response properly
            return app(LoginResponse::class);
        } catch (ValidationException $e) {
            // Re-throw validation exceptions (for invalid credentials, lockout, etc.)
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login failed: ' . $e->getMessage());
            RateLimiter::hit($key, 60);

            Notification::make()
                ->title('Error')
                ->body('Something went wrong during sign-in.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'data.email' => 'These credentials do not match our records.',
            ]);
        }
    }
}
