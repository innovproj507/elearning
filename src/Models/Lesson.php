<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Lesson extends Model
{
    protected string $table = 'lessons';

    public const TYPE_TEXT  = 'text';
    public const TYPE_FILE  = 'file';
    public const TYPE_VIDEO = 'video';

    /**
     * Returns all lessons for a course ordered by order_num.
     */
    public function byCourse(int $courseId): array
    {
        $response = $this->api->get("/courses/{$courseId}/lessons");
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?? [];
        }
        return [];
    }

    /**
     * Returns the previous active lesson before the given order_num.
     */
    public function previousInCourse(int $courseId, int $orderNum): array|false
    {
        $response = $this->api->get("/courses/{$courseId}/lessons/previous", ['order_num' => $orderNum]);
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?: false;
        }
        return false;
    }

    /**
     * Checks whether a lesson is completed by the authenticated user.
     * The userId parameter is accepted for interface compatibility but auth is
     * handled server-side via Bearer token.
     */
    public function isCompleted(int $userId, int $lessonId): bool
    {
        $response = $this->api->get("/lessons/{$lessonId}");
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return (bool) ($response['data']['completed'] ?? false);
        }
        return false;
    }

    /**
     * Returns the list of completed lesson IDs for a student in a course.
     */
    public function completedByStudentInCourse(int $userId, int $courseId): array
    {
        $response = $this->api->get("/student/{$userId}/courses/{$courseId}/progress");
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?? [];
        }
        return [];
    }

    /**
     * Marks a lesson as complete for the authenticated user.
     */
    public function markComplete(int $userId, int $lessonId): bool
    {
        $response = $this->api->post("/lessons/{$lessonId}/complete", []);
        return (bool) ($response && isset($response['status']) && $response['status'] === 'success');
    }

    /**
     * Sets the active state of a lesson.
     */
    public function setActive(int $lessonId, bool $active): bool
    {
        $response = $this->api->post("/lessons/{$lessonId}/toggle", ['active' => $active]);
        return (bool) ($response && isset($response['status']) && $response['status'] === 'success');
    }

    /**
     * Deletes a lesson.
     */
    public function delete(int $lessonId): bool
    {
        $response = $this->api->post("/lessons/{$lessonId}/delete", []);
        return (bool) ($response && isset($response['status']) && $response['status'] === 'success');
    }

    /**
     * Updates a lesson.
     */
    public function update(int $lessonId, array $data): bool
    {
        $response = $this->api->post("/lessons/{$lessonId}/update", $data);
        return (bool) ($response && isset($response['status']) && $response['status'] === 'success');
    }
}
