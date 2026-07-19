<?php
declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Container;

final class AboutController extends AbstractController
{
    public function index(): void
    {
        $this->renderPage('aboutTitle', 'about');
    }
}
