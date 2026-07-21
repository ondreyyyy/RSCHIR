<?php

namespace App\Http\Middleware;

use App\Infrastructure\EloquentUserRepository;
use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AdminBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('admin') && $request->session()->get('admin') === true) {
            return $next($request);
        }

        $username = $request->getUser();
        $password = $request->getPassword();

        if ($username === 'admin') {
            $userService = new UserService(new EloquentUserRepository());
            if ($userService->verifyPassword($username, $password)) {
                $request->session()->put('admin', true);
                $request->session()->put('login', 'admin');
                return $next($request);
            }
        }

        return response('Invalid credentials.', 401, ['WWW-Authenticate' => 'Basic']);
    }
}
