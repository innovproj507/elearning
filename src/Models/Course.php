<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Course extends Model
{
    protected string $table = 'courses';

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';

    /**
     * Cursos publicados consumiendo el API del CMS.
     */
    public function published(int $page = 1, int $perPage = 12, ?int $categoryId = null): array
    {
        return $this->all([
            'status'      => 'published',
            'page'        => $page,
            'perPage'     => $perPage,
            'category_id' => $categoryId
        ]);
    }

    /**
     * Buscar un curso por su Slug vía API
     */
    public function findBySlug(string $slug): array|false
    {
        $response = $this->api->get("/courses/slug/{$slug}");
        return ($response && $response['status'] === 'success') ? $response['data'] : false;
    }

    /**
     * Cursos de un profesor vía API
     */
    public function byTeacher(int $teacherId): array
    {
        $response = $this->api->get("/teacher/{$teacherId}/courses");
        return ($response && $response['status'] === 'success') ? $response['data'] : [];
    }

    public function createWithSlug(array $data): int|string
    {
        $data['slug'] = $this->generateSlug($data['title'] ?? '');
        $response = $this->api->post('/courses', $data);
        if (!$response || ($response['status'] ?? '') !== 'success') {
            throw new \RuntimeException('Error al crear el curso: ' . ($response['message'] ?? 'API error'));
        }
        return $response['data']['id'];
    }

    public function update(int $id, array $data): bool
    {
        $response = $this->api->post("/courses/{$id}/update", $data);
        return $response && ($response['status'] ?? '') === 'success';
    }

    public function delete(int $id): bool
    {
        $response = $this->api->post("/courses/{$id}/delete", []);
        return $response && ($response['status'] ?? '') === 'success';
    }

    public function countAll(): int
    {
        return $this->count();
    }

    public function countPublished(): int
    {
        return $this->count(['status' => 'published']);
    }

    /**
     * Obtener lecciones y cuestionarios de un curso vía API
     */
    public function getLessonsAndQuizzes(int $courseId): array
    {
        $response = $this->api->get("/courses/{$courseId}/content");
        return ($response && $response['status'] === 'success') ? $response['data'] : ['lessons' => [], 'quizzes' => []];
    }
}
