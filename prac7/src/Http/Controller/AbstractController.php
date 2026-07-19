<?php
declare(strict_types=1);

namespace App\Http\Controller;

use App\Application\Localization\UiTextProvider;
use App\Application\Preferences\PreferencesService;
use App\Http\Container;
use App\Http\Response;
use App\Http\View\View;
use RuntimeException;

abstract class AbstractController
{
    protected Container $container;
    protected View $view;
    protected UiTextProvider $ui;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->ui = $container->uiText();
        $templatesDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'templates';
        $this->view = new View($templatesDir);
    }

    protected function preferences(): \App\Application\Preferences\UserPreferences
    {
        return $this->container->preferences()->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderPage(string $titleKey, string $bodyTemplate, array $data = []): void
    {
        $preferences = $this->preferences();
        $ui = $this->ui->get($preferences->language);

        $body = $this->view->render($bodyTemplate, array_merge($data, [
            'preferences' => $preferences,
            'ui' => $ui,
        ]));

        $html = $this->view->render('layout', [
            'preferences' => $preferences,
            'ui' => $ui,
            'title' => $ui[$titleKey] ?? $titleKey,
            'body' => $body,
        ]);

        Response::html($html);
    }

    protected function requireAdmin(): void
    {
        try {
            $this->container->preferences()->requireAdmin();
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 403);
        }
    }
}
