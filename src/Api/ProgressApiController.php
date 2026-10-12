<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Course;
use App\Models\Enrollment;

class ProgressApiController extends ApiController
{
    private Lesson     $lessonModel;
    private Quiz       $quizModel;
    private Course     $courseModel;
    private Enrollment $enrollmentModel;

    public function __construct()
    {
        $this->lessonModel     = new Lesson();
        $this->quizModel       = new Quiz();
        $this->courseModel     = new Course();
        $this->enrollmentModel = new Enrollment();
    }

    /**
     * GET /api/progreso/{courseId}   [Requiere JWT]
     *
     * Retorna el progreso completo del usuario en un curso.
     * Formato compatible con reporting estándar LMS (xAPI-ready).
     */
    public function getCourseProgress(array $params = []): void
    {
        $studentId = $this->jwtUserId();
        $courseId  = (int) ($params['courseId'] ?? 0);

        $course = $this->courseModel->find($courseId);
        if (!$course) {
            $this->error('Curso no encontrado.', 404);
        }

        if (!$this->enrollmentModel->isEnrolled($studentId, $courseId)) {
            $this->error('No estás inscrito en este curso.', 403);
        }

        $allLessons       = $this->lessonModel->byCourse($courseId);
        $completedIds     = $this->lessonModel->completedByStudentInCourse($studentId, $courseId);
        $totalLessons     = count($allLessons);
        $completedLessons = count($completedIds);
        $progressPct      = $totalLessons > 0
            ? round(($completedLessons / $totalLessons) * 100, 1)
            : 0;

        $quizzes  = $this->quizModel->byCourse($courseId);
        $quizData = array_map(function ($q) use ($studentId) {
            $best = $this->quizModel->getBestAttempt($studentId, (int) $q['id']);
            return [
                'quiz_id'    => $q['id'],
                'title'      => $q['title'],
                'attempted'  => $best !== false,
                'best_score' => $best ? (float) $best['score'] : null,
                'passed'     => $best ? (bool) $best['passed'] : false,
            ];
        }, $quizzes);

        $this->success([
            'course_id'         => $courseId,
            'student_id'        => $studentId,
            'total_lessons'     => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_pct'      => $progressPct,
            'completed_lesson_ids' => $completedIds,
            'quizzes'           => $quizData,
        ]);
    }

    /**
     * GET /api/calificaciones/{quizId}   [Requiere JWT]
     *
     * Retorna las calificaciones de un quiz para el usuario autenticado.
     * Admins/Profesores ven las calificaciones de todos los estudiantes.
     */
    public function getQuizGrades(array $params = []): void
    {
        $userId  = $this->jwtUserId();
        $role    = $this->jwtUserRole();
        $quizId  = (int) ($params['quizId'] ?? 0);

        $quiz = $this->quizModel->find($quizId);
        if (!$quiz) {
            $this->error('Quiz no encontrado.', 404);
        }

        // Admin o teacher puede ver todas las calificaciones
        if (in_array($role, ['admin', 'teacher'], true)) {
            $grades = $this->quizModel->getStudentGradesByQuiz($quizId);
            $this->success([
                'quiz_id'    => $quizId,
                'quiz_title' => $quiz['title'],
                'grades'     => $grades,
                'total'      => count($grades),
            ]);
        }

        // Estudiante: solo sus propias calificaciones
        $best = $this->quizModel->getBestAttempt($userId, $quizId);
        $this->success([
            'quiz_id'    => $quizId,
            'quiz_title' => $quiz['title'],
            'student_id' => $userId,
            'attempted'  => $best !== false,
            'best_score' => $best ? (float) $best['score'] : null,
            'passed'     => $best ? (bool) $best['passed'] : false,
            'pass_score' => (float) $quiz['pass_percentage'],
        ]);
    }
}
