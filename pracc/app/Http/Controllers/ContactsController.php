<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContactsController extends Controller
{
    public function index(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        return $this->renderPage($request, 'contactsTitle', 'contacts');
    }
}
