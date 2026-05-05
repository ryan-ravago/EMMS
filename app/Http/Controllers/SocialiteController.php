<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\User;
use App\Models\Usr;
use Laravel\Socialite\Facades\Socialite;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    // public function handleGoogleCallback()
    // {
    //     $key = 'google-login-attempt:' . request()->ip();

    //     // 1. Check if they are locked out
    //     if (RateLimiter::tooManyAttempts($key, 3)) {
    //         $seconds = RateLimiter::availableIn($key);

    //         Notification::make()
    //             ->title('Locked Out')
    //             ->body("Too many failed attempts. Please wait {$seconds}s.")
    //             ->danger()
    //             ->persistent()
    //             ->send();

    //         return redirect('/login');
    //     }

    //     try {
    //         $googleUser = Socialite::driver('google')->user();
    //         $email = $googleUser->getEmail();

    //         // Step 1: Verify if user exists in Usr model
    //         $usrUser = Usr::where('email', $email)->first();

    //         if (!$usrUser) {
    //             $currentAttempts = RateLimiter::hit($key, 60);

    //             if (RateLimiter::attempts($key) >= 3) {
    //                 RateLimiter::clear($key);
    //                 RateLimiter::hit($key, 60);
    //                 RateLimiter::hit($key, 60);
    //                 RateLimiter::hit($key, 60);

    //                 Notification::make()
    //                     ->title('Too many failures')
    //                     ->body('You are now locked out for a full 60 seconds.')
    //                     ->danger()
    //                     ->send();
    //             } else {
    //                 Notification::make()
    //                     ->title('Invalid Login')
    //                     ->body('User not found. Attempts remaining: ' . (3 - RateLimiter::attempts($key)))
    //                     ->warning()
    //                     ->send();
    //             }
    //             return redirect('/login');
    //         }

    //         // Step 2: Check if email exists in AppUser
    //         $appUser = AppUser::where('user_email', $email)->first();

    //         if (!$appUser) {
    //             $currentAttempts = RateLimiter::hit($key, 60);

    //             if (RateLimiter::attempts($key) >= 3) {
    //                 RateLimiter::clear($key);
    //                 RateLimiter::hit($key, 60);
    //                 RateLimiter::hit($key, 60);
    //                 RateLimiter::hit($key, 60);

    //                 Notification::make()
    //                     ->title('Too many failures')
    //                     ->body('You are now locked out for a full 60 seconds.')
    //                     ->danger()
    //                     ->send();
    //             } else {
    //                 Notification::make()
    //                     ->title('Invalid Login')
    //                     ->body('User account not found. Attempts remaining: ' . (3 - RateLimiter::attempts($key)))
    //                     ->warning()
    //                     ->send();
    //             }
    //             return redirect('/login');
    //         }

    //         RateLimiter::clear($key);
    //         Auth::login($appUser);
    //         Filament::auth()->login($appUser);

    //         // Regenerate CSRF token only (not full session)
    //         request()->session()->regenerateToken();

    //         Notification::make()
    //             ->title('Welcome back!')
    //             ->body("Hello {$appUser->getFilamentName()}")
    //             ->success()
    //             ->send();

    //         // return redirect(Filament::getPanel('admin')->getUrl());
    //         return redirect()->intended(Filament::getPanel('admin')->getUrl());
    //         // return redirect('/login');
    //     } catch (\Exception $e) {
    //         Log::error('Google login failed: ' . $e->getMessage());
    //         RateLimiter::hit($key, 60);
    //         Notification::make()
    //             ->title('Error')
    //             ->body('Something went wrong during Google sign-in.')
    //             ->danger()
    //             ->send();

    //         return redirect('/login')->withErrors([
    //             'error' => 'Failed to authenticate with Google.'
    //         ]);
    //     }
    // }

    public function handleGoogleCallback()
    {
        $key = 'google-login-attempt:' . request()->ip();
        $maxAttempts = 3;
        $decaySeconds = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            Notification::make()
                ->title('Locked Out')
                ->body("Too many failed attempts. Please wait {$seconds}s.")
                ->danger()
                ->persistent()
                ->send();

            return redirect('/login');
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $email = $googleUser->getEmail();

            $usrUser = Usr::where('email', $email)->first();
            $appUser = AppUser::where('user_email', $email)->first();

            if (!$usrUser || !$appUser) {
                RateLimiter::hit($key, $decaySeconds);

                $remaining = $maxAttempts - RateLimiter::attempts($key);
                $lockedOut = RateLimiter::tooManyAttempts($key, $maxAttempts);

                Notification::make()
                    ->title($lockedOut ? 'Too Many Failures' : 'Invalid Login')
                    ->body(
                        $lockedOut
                            ? "You are now locked out for {$decaySeconds} seconds."
                            : "User not found. Attempts remaining: {$remaining}"
                    )
                    ->when($lockedOut, fn($n) => $n->danger())
                    ->when(!$lockedOut, fn($n) => $n->warning())
                    ->send();

                return redirect('/login');
            }

            RateLimiter::clear($key);

            // Single login via Filament (handles Auth guard internally)
            Filament::auth()->login($appUser);

            // Full session regeneration to prevent session fixation
            request()->session()->regenerate();

            Notification::make()
                ->title('Welcome back!')
                ->body("Hello {$appUser->getFilamentName()}")
                ->success()
                ->send();

            return redirect()->intended(Filament::getPanel('admin')->getUrl());
        } catch (\Exception $e) {
            Log::error('Google login failed', [
                'message' => $e->getMessage(),
                'ip'      => request()->ip(),
            ]);

            RateLimiter::hit($key, $decaySeconds);

            Notification::make()
                ->title('Error')
                ->body('Something went wrong during Google sign-in.')
                ->danger()
                ->send();

            return redirect('/login');
        }
    }
}
