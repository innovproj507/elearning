<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    /**
     * Obtener el perfil del usuario actual desde el CMS
     */
    public function profile(int $userId): array|false
    {
        return $this->find($userId);
    }

    /**
     * Obtener estudiantes de un curso vía API
     */
    public function countByRole(string $role): int
    {
        return $this->count(['role' => $role, 'is_active' => 1]);
    }
}
