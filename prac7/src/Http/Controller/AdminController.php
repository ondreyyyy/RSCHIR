<?php
declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Container;

final class AdminController extends AbstractController
{
    public function index(): void
    {
        $this->container->preferences()->startSession();
        $authUser = $_SERVER['PHP_AUTH_USER'] ?? $_SERVER['REMOTE_USER'] ?? null;
        if ($authUser === 'admin') {
            $_SESSION['admin'] = true;
            $_SESSION['login'] = 'admin';
        }

        $this->renderPage('adminTitle', 'admin', [
            'now' => date('Y-m-d H:i:s'),
        ]);
    }
}
