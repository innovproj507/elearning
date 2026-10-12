<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;

class CourseApiController extends ApiController
{
    private Course $courseModel;
    private Lesson $lessonModel;
    private Quiz   $quizModel;

    public function __construct()
    {
        $this->courseModel = new Course();
        $this->lessonModel = new Lesson();
        $this->quizModel   = new Quiz();
    }

    /**
     * GET /api/cursos
     * Parámetros opcionales: ?page=1&per_page=12&categoria=3
     *
     * Formato compatible con REST estándar (LTI-ready).
     */
    public function index(array $params = []): void
    {
        $page       = max(1, (int) ($_GET['page']     ?? 1));
        $perPage    = min(50, max(1, (int) ($_GET['per_page'] ?? 12)));
        $categoryId = (int) ($_GET['categoria'] ?? 0) ?: null;

        $result = $this->courseModel->published($page, $perPage, $categoryId);

        $this->success([
            'courses'      => $result['data'],
            'pagination'   => [
                'total'        => $result['total'],
                'per_page'     => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page'    => $result['last_page'],
            ],
        ]);
    }

    /**
     * GET /api/cursos/{id}
     * Retorna un curso con sus lecciones y quizzes.
     *
     * El campo `lti_launch_url` permite integración con Moodle vía LTI.
     */
    public function show(array $params = []): void
    {
        $course = $this->courseModel->find((int) ($params['id'] ?? 0));

        if (!$course || $course['status'] !== Course::STATUS_PUBLISHED) {
            $this->error('Curso no encontrado.', 404);
        }

        $lessons = $this->lessonModel->byCourse((int) $course['id']);
        $quizzes = $this->quizModel->byCourse((int) $course['id']);

        $this->success([
            'course'          => $course,
            'lessons'         => $lessons,
            'quizzes'         => $quizzes,
            'lti_launch_url'  => ($_ENV['APP_URL'] ?? '') . '/lti/launch/' . $course['id'],
        ]);
    }
}
