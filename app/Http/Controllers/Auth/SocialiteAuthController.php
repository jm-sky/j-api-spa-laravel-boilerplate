<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SocialiteDriver;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SocialiteAuthController extends Controller
{
    public function redirect(SocialiteDriver $driver = SocialiteDriver::Github): RedirectResponse
    {
        return Socialite::driver($driver->value)->redirect();
    }

    public function callback(SocialiteDriver $driver = SocialiteDriver::Github): RedirectResponse
    {
        /** @var SocialiteUser */
        $providerUser = Socialite::driver($driver->value)->user();

        /** @var ?User */
        $user = User::where('provider', $driver->value)->where('provider_id', $providerUser->getId())->first();

        /** @var ?User */
        $user = $user ?? User::where('email', $providerUser->getEmail())->first();

        if ($user) {
            $user->provider = $driver->value;
            $user->provider_id = $providerUser->getId();
            $user->provider_token = $providerUser->token;
            $user->provider_refresh_token = $providerUser->refreshToken;
            $user->save();
        }

        /** @var User */
        $user = $user ?? User::create([
            'name' => $providerUser->name ?? $providerUser->nickname, // @phpstan-ignore-line
            'email' => $providerUser->email,
            'password' => Hash::make(Str::random(32)),
            'provider' => $driver->value,
            'provider_id' => $providerUser->getId(),
            'provider_token' => $providerUser->token,
            'provider_refresh_token' => $providerUser->refreshToken,
        ]);

        Auth::login($user);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
