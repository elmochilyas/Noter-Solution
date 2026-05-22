<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\Auth\MagicLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MagicLinkController extends Controller
{
    public function __construct(
        private readonly MagicLinkService $magicLink,
    ) {}

    public function create(): View
    {
        return view('auth.magic-link');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $locale = $request->route('locale');
        $email = $data['email'];
        $ip = $request->ip();

        $emailKey = 'magic-link:email:'.sha1($email);
        $ipKey = 'magic-link:ip:'.str_replace(':', '.', $ip ?? '0.0.0.0');

        if (RateLimiter::tooManyAttempts($emailKey, 3)) {
            return back()->withErrors(['email' => __('auth.throttle')]);
        }

        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            return back()->withErrors(['email' => __('auth.throttle')]);
        }

        RateLimiter::hit($emailKey, 3600);
        RateLimiter::hit($ipKey, 3600);

        $client = Client::where('email', $email)->first();

        if (! $client) {
            $client = Client::create([
                'uuid' => (string) Str::uuid(),
                'email' => $email,
                'phone' => '0000000000',
                'full_name' => $email,
                'preferred_locale' => $locale,
            ]);
        }

        $this->magicLink->send(
            $client,
            $locale,
            $request->input('intended', route('portal.dashboard', ['locale' => $locale])),
        );

        return to_route('portal.login.sent', ['locale' => $locale]);
    }

    public function sent(): View
    {
        return view('auth.magic-link-sent');
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $locale = $request->route('locale');

        if (! $request->hasValidSignature()) {
            return to_route('portal.login', ['locale' => $locale])
                ->withErrors(['link' => __('auth.magic_link_expired')]);
        }

        $client = $this->magicLink->verify($data['email'], $data['token']);

        if (! $client) {
            return to_route('portal.login', ['locale' => $locale])
                ->withErrors(['link' => __('auth.magic_link_invalid')]);
        }

        $client->update(['last_login_at' => now()]);

        Auth::guard('client')->login($client);

        $request->session()->regenerate();

        return redirect()->intended(route('portal.dashboard', ['locale' => $locale]));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('client')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('portal.login', ['locale' => $request->route('locale')]);
    }
}
