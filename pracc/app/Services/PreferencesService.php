<?php

namespace App\Services;

use App\Infrastructure\EloquentUserRepository;
use App\Services\UserService;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

final class PreferencesService
{
    public function get(Request $request): UserPreferences
    {
        $defaults = $this->defaults();

        $login = $request->session()->get('login');
        $login = $login !== null ? $login : $request->cookie('login', $defaults->login);

        $language = $request->session()->get('language');
        $language = $language !== null ? $language : $request->cookie('language', $defaults->language);

        $theme = $request->session()->get('theme');
        $theme = $theme !== null ? $theme : $request->cookie('theme', $defaults->theme);

        $preferences = new UserPreferences(
            $this->normalizeLogin($login),
            $this->normalizeLanguage($language),
            $this->normalizeTheme($theme)
        );

        $request->session()->put('login', $preferences->login);
        $request->session()->put('language', $preferences->language);
        $request->session()->put('theme', $preferences->theme);

        return $preferences;
    }

    public function handleForm(Request $request): ?RedirectResponse
    {
        if ($request->getMethod() !== 'POST') {
            return null;
        }

        if ($request->input('action') !== 'preferences') {
            return null;
        }

        $preferences = new UserPreferences(
            $this->normalizeLogin($request->input('login')),
            $this->normalizeLanguage($request->input('language')),
            $this->normalizeTheme($request->input('theme'))
        );

        if ($preferences->login !== 'admin') {
            $request->session()->forget(['admin', 'admin_login']);
        }

        $request->session()->put('login', $preferences->login);
        $request->session()->put('language', $preferences->language);
        $request->session()->put('theme', $preferences->theme);

        return redirect()->to($request->getRequestUri())
            ->withCookie(cookie('login', $preferences->login, 60 * 24 * 30, '/', null, false, true, false, 'Lax'))
            ->withCookie(cookie('language', $preferences->language, 60 * 24 * 30, '/', null, false, true, false, 'Lax'))
            ->withCookie(cookie('theme', $preferences->theme, 60 * 24 * 30, '/', null, false, true, false, 'Lax'));
    }

    public function clearAdminSession(Request $request): void
    {
        $request->session()->forget(['admin', 'admin_login']);
    }

    public function requireAdmin(Request $request): void
    {
        if (!$request->session()->has('admin') || $request->session()->get('admin') !== true) {
            throw new \RuntimeException('Доступ запрещён.', 403);
        }
    }

    private function defaults(): UserPreferences
    {
        return new UserPreferences('гость', 'ru', 'light');
    }

    private function normalizeLogin(?string $login): string
    {
        $login = trim(strip_tags((string) $login));
        return $login === '' ? 'гость' : $login;
    }

    private function normalizeLanguage(?string $language): string
    {
        $language = strtolower(trim((string) $language));
        return in_array($language, ['ru', 'en'], true) ? $language : 'ru';
    }

    private function normalizeTheme(?string $theme): string
    {
        $theme = strtolower(trim((string) $theme));
        return in_array($theme, ['light', 'dark'], true) ? $theme : 'light';
    }
}
