<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

class RoleMiddleware
{
    public function handle(array $allowedRoles): void
    {
        $user = Session::get('user');

        if (!$user || !in_array($user['role'], $allowedRoles, true)) {
            http_response_code(403);
            include BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }
}
