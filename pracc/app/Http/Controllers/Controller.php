<?php

namespace App\Http\Controllers;

use App\Services\UiTextProvider;
use App\Services\PreferencesService;
use App\Services\UserPreferences;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    protected function preferences(Request $request): UserPreferences
    {
        return (new PreferencesService())->get($request);
    }

    protected function renderPage(Request $request, string $titleKey, string $view, array $data = []): Response
    {
        $preferences = $this->preferences($request);
        $ui = App::make(UiTextProvider::class)->get($preferences->language);
        $title = $ui[$titleKey] ?? $titleKey;

        return response()->view($view, array_merge($data, [
            'preferences' => $preferences->toArray(),
            'ui' => $ui,
            'title' => $title,
        ]));
    }
}
