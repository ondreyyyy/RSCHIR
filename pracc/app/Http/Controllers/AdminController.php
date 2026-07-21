<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminController extends Controller
{
    public function index(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $authUser = $request->server('PHP_AUTH_USER') ?? $request->server('REMOTE_USER') ?? null;
        if ($authUser === 'admin') {
            $request->session()->put('admin', true);
            $request->session()->put('login', 'admin');
        }

        return $this->renderPage($request, 'adminTitle', 'admin', [
            'now' => date('Y-m-d H:i:s'),
        ]);
    }
}
