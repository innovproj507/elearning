<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class AuthMiddleware
{
    public function handle(array $params = []): void
    {
        if (Session::get('user') === null) {
            Session::set('intended_url', $_SERVER['REQUEST_URI']);
            header('Location: /login');
            exit;
        }
    }
}
