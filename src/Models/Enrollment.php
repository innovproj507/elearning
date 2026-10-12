<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Enrollment extends Model
{
    protected string $table = 'enrollments';

    public function getStudentCourses(int $studentId): array
    {
        $response = $this->api->get('/student/courses', ['student_id' => $studentId]);
        return ($response && $response['status'] === 'success') ? $response['data'] : [];
    }

    public function byStudentWithProgress(int $studentId): array
    {
        $response = $this->api->get("/student/{$studentId}/courses");
        return ($response && $response['status'] === 'success') ? $response['data'] : [];
    }

    public function isEnrolled(int $userId, int $courseId): bool
    {
        $response = $this->api->get("/courses/{$courseId}/check-enrollment", ['user_id' => $userId]);
        return ($response && $response['status'] === 'success' && $response['enrolled'] === true);
    }

    public function enroll(int $userId, int $courseId): bool
    {
        $response = $this->api->post('/enrollments', [
            'user_id'   => $userId,
            'course_id' => $courseId,
        ]);
        return $response && $response['status'] === 'success';
    }

    public function countTotal(): int
    {
        return $this->count();
    }
}
