<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // Redirect to Google's OAuth page
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle the callback from Google
    public function handleGoogleCallback()
    {
        try {
            // Get the user from Google
            $googleUser = Socialite::driver('google')->user();

            // Check if a user with this email already exists
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // If the user exists but doesn't have a provider, link the Google account
                if (!$user->provider) {
                    $user->provider = 'google';
                    $user->provider_id = $googleUser->id;
                    $user->save();
                }
                Auth::login($user);
            } else {
                // Create a new user
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'provider' => 'google',
                    'provider_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(16)), // Random password for social login users
                    'email_verified_at' => now(), // Google verifies emails, so mark as verified
                ]);
                Auth::login($newUser);
            }

            return redirect('/home');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Unable to login with Google. Please try again.');
        }
    }
}
