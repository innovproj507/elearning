<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Forum extends Model
{
    protected string $table = 'forums';

    public function all(array $params = []): array
    {
        $response = $this->api->get('/forums');
        return ($response && $response['status'] === 'success') ? ($response['data'] ?? []) : [];
    }

    public function findBySlug(string $slug): array|false
    {
        $response = $this->api->get('/forums/' . $slug);
        if ($response && $response['status'] === 'success') {
            return $response['data'];
        }
        return false;
    }

    public function findTopic(int $id): array|false
    {
        $response = $this->api->get('/topics/' . $id);
        if ($response && $response['status'] === 'success') {
            return $response['data'];
        }
        return false;
    }

    public function createTopic(array $data): int|false
    {
        $response = $this->api->post('/topics', $data);
        if ($response && $response['status'] === 'success') {
            return (int) ($response['data']['id'] ?? 0);
        }
        return false;
    }

    public function createPost(int $topicId, string $content): bool
    {
        $response = $this->api->post('/topics/' . $topicId . '/reply', ['content' => $content]);
        return (bool) ($response && $response['status'] === 'success');
    }

    public function courseTopics(int $courseId): array
    {
        $response = $this->api->get('/courses/' . $courseId . '/forum');
        return ($response && $response['status'] === 'success') ? ($response['data'] ?? []) : [];
    }

    public function createCourseTopic(int $courseId, string $title, string $content): int|false
    {
        $response = $this->api->post('/courses/' . $courseId . '/forum', [
            'title'   => $title,
            'content' => $content,
        ]);
        if ($response && $response['status'] === 'success') {
            return (int) ($response['data']['id'] ?? 0);
        }
        return false;
    }

    public function deleteTopic(int $id): bool
    {
        $response = $this->api->post('/topics/' . $id . '/delete', []);
        return (bool) ($response && $response['status'] === 'success');
    }

    public function deletePost(int $id): bool
    {
        $response = $this->api->post('/posts/' . $id . '/delete', []);
        return (bool) ($response && $response['status'] === 'success');
    }
}
