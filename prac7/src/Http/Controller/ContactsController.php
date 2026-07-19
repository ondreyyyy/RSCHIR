<?php
declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\Container;

final class ContactsController extends AbstractController
{
    public function index(): void
    {
        $this->renderPage('contactsTitle', 'contacts');
    }
}
