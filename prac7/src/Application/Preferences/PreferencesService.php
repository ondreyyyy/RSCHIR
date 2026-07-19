<?php
declare(strict_types=1);

namespace App\Application\Preferences;

use RuntimeException;

final class PreferencesService
{
    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (extension_loaded('redis')) {
            ini_set('session.save_handler', 'redis');
            ini_set('session.save_path', 'tcp://redis:6379');
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        session_name('RSCHIRSESSID');
        session_start();
    }

    public function get(): UserPreferences
    {
        $this->startSession();
        $defaults = $this->defaults();

        $preferences = new UserPreferences(
            $this->normalizeLogin($_SESSION['login'] ?? $_COOKIE['login'] ?? $defaults->login),
            $this->normalizeLanguage($_SESSION['language'] ?? $_COOKIE['language'] ?? $defaults->language),
            $this->normalizeTheme($_SESSION['theme'] ?? $_COOKIE['theme'] ?? $defaults->theme)
        );

        $_SESSION['login'] = $preferences->login;
        $_SESSION['language'] = $preferences->language;
        $_SESSION['theme'] = $preferences->theme;

        return $preferences;
    }

    public function handleForm(): bool
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return false;
        }

        if (($_POST['action'] ?? '') !== 'preferences') {
            return false;
        }

        $this->startSession();
        $preferences = new UserPreferences(
            $this->normalizeLogin($_POST['login'] ?? null),
            $this->normalizeLanguage($_POST['language'] ?? null),
            $this->normalizeTheme($_POST['theme'] ?? null)
        );

        if ($preferences->login !== 'admin') {
            $this->clearAdminSession();
        }

        $_SESSION['login'] = $preferences->login;
        $_SESSION['language'] = $preferences->language;
        $_SESSION['theme'] = $preferences->theme;
        $this->setCookies($preferences);

        $redirectTo = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: ' . $redirectTo);
        exit;
    }

    public function clearAdminSession(): void
    {
        $this->startSession();
        unset($_SESSION['admin']);
        unset($_SESSION['admin_login']);
    }

    public function requireAdmin(): void
    {
        $this->startSession();
        if (empty($_SESSION['admin']) || $_SESSION['admin'] !== true) {
            throw new RuntimeException('Доступ запрещён.', 403);
        }
    }

    private function setCookies(UserPreferences $preferences): void
    {
        $expires = time() + (60 * 60 * 24 * 30);
        $cookieOptions = [
            'expires' => $expires,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        setcookie('login', $preferences->login, $cookieOptions);
        setcookie('language', $preferences->language, $cookieOptions);
        setcookie('theme', $preferences->theme, $cookieOptions);
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
