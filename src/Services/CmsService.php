<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\ApiClient;

/**
 * CmsService — Capa de integración con el API del CMS (cms.test/api/v1).
 *
 * Centraliza todas las llamadas HTTP al CMS en métodos con nombre semántico.
 * Los controladores consumen esta clase en lugar de usar ApiClient directamente.
 */
class CmsService
{
    private ApiClient $client;

    public function __construct()
    {
        $this->client = ApiClient::getInstance();
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    /**
     * Autenticar usuario contra el CMS y almacenar el token JWT resultante.
     *
     * @return array{token: string, user: array}|false
     */
    public function login(string $email, string $password): array|false
    {
        $response = $this->client->post('login', compact('email', 'password'));

        if ($response && isset($response['token'])) {
            $this->client->setToken($response['token']);
            return $response;
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Cursos
    // -------------------------------------------------------------------------

    /**
     * Obtener lista paginada de cursos publicados.
     *
     * @param array{page?: int, limit?: int, category_id?: int} $filters
     */
    public function getCourses(array $filters = []): array
    {
        $response = $this->client->get('courses', $filters);
        return $response['data'] ?? $response ?? [];
    }

    /**
     * Obtener un curso por slug.
     */
    public function getCourse(string $slug): array|false
    {
        return $this->client->get("courses/{$slug}");
    }

    /**
     * Total de cursos publicados.
     */
    public function getCoursesCount(): int
    {
        $response = $this->client->get('courses/count');
        return (int) ($response['count'] ?? 0);
    }

    // -------------------------------------------------------------------------
    // Categorías
    // -------------------------------------------------------------------------

    /**
     * Obtener todas las categorías de cursos.
     */
    public function getCategories(): array
    {
        $response = $this->client->get('categories');
        return $response['data'] ?? $response ?? [];
    }

    // -------------------------------------------------------------------------
    // Estadísticas
    // -------------------------------------------------------------------------

    /**
     * Total de usuarios registrados.
     */
    public function getUsersCount(): int
    {
        $response = $this->client->get('users/count');
        return (int) ($response['count'] ?? 0);
    }

    /**
     * Stats generales del CMS para el dashboard.
     *
     * @return array{courses: int, users: int}
     */
    public function getDashboardStats(): array
    {
        return [
            'courses' => $this->getCoursesCount(),
            'users'   => $this->getUsersCount(),
        ];
    }
}
