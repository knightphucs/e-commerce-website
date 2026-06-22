<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(Request $request): View
    {
        $this->storeIntendedUrl($request);

        return view('pages.auth.signin');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        $user = Auth::user();

        $this->discardIntendedUrlIfWrongArea($request, $user);

        $default = $user->isEditor()
            ? route('dashboard', absolute: false)
            : route('shop.index', absolute: false);

        return redirect()->intended($default);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $request->routeIs('admin.logout')
            ? redirect()->route('login')
            : redirect()->route('shop.index');
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }

    private function storeIntendedUrl(Request $request): void
    {
        $redirectTo = $request->string('redirect_to')->toString();

        if (str_starts_with($redirectTo, '/') && ! str_starts_with($redirectTo, '//')) {
            $request->session()->put('url.intended', $redirectTo);
        }
    }

    /**
     * Drop the stored intended URL if it doesn't belong to the area the
     * logged-in user actually has access to (e.g. an editor's intended URL
     * was a client-only checkout page, or a customer's was an admin page).
     */
    private function discardIntendedUrlIfWrongArea(Request $request, User $user): void
    {
        $intendedPath = parse_url($request->session()->get('url.intended', ''), PHP_URL_PATH) ?? '';
        $isAdminArea = str_starts_with($intendedPath, '/admin');

        if ($isAdminArea !== $user->isEditor()) {
            $request->session()->forget('url.intended');
        }
    }
}
