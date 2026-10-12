<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\Enrollment;
use App\Models\Course;

class EnrollmentApiController extends ApiController
{
    private Enrollment $enrollmentModel;
    private Course     $courseModel;

    public function __construct()
    {
        $this->enrollmentModel = new Enrollment();
        $this->courseModel     = new Course();
    }

    /**
     * POST /api/inscribir   [Requiere JWT]
     * Body: { "course_id": 5 }
     *
     * Permite a Moodle o cualquier sistema externo inscribir usuarios.
     */
    public function enroll(array $params = []): void
    {
        $body      = $this->getJsonBody();
        $studentId = $this->jwtUserId();
        $courseId  = (int) ($body['course_id'] ?? 0);

        if (!$courseId) {
            $this->error('course_id es requerido.', 422);
        }

        $course = $this->courseModel->find($courseId);

        if (!$course || $course['status'] !== Course::STATUS_PUBLISHED) {
            $this->error('Curso no disponible para inscripción.', 404);
        }

        try {
            $enrollmentId = $this->enrollmentModel->enroll($studentId, $courseId);

            $this->success([
                'enrollment_id' => $enrollmentId,
                'student_id'    => $studentId,
                'course_id'     => $courseId,
                'enrolled_at'   => date('c'),
            ], 'Inscripción exitosa.', 201);
        } catch (\LogicException $e) {
            $this->error($e->getMessage(), 409);
        }
    }

    /**
     * GET /api/mis-cursos   [Requiere JWT]
     * Retorna los cursos del usuario autenticado con su progreso.
     */
    public function myCourses(array $params = []): void
    {
        $studentId   = $this->jwtUserId();
        $enrollments = $this->enrollmentModel->byStudentWithProgress($studentId);

        // Calcular porcentaje de progreso
        $enrollments = array_map(function ($e) {
            $total     = (int) $e['total_lessons'];
            $completed = (int) $e['completed_lessons'];
            $e['progress_pct'] = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
            return $e;
        }, $enrollments);

        $this->success([
            'courses' => $enrollments,
            'total'   => count($enrollments),
        ]);
    }
}
