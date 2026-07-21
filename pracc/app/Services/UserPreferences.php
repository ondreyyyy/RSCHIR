<?php

namespace App\Services;

final class UserPreferences
{
    public function __construct(
        public string $login,
        public string $language,
        public string $theme
    ) {
    }

    public function toArray(): array
    {
        return [
            'login' => $this->login,
            'language' => $this->language,
            'theme' => $this->theme,
        ];
    }
}
