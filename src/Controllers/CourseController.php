<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Quiz;

class CourseController extends Controller
{
    private Course     $courseModel;
    private Category   $categoryModel;
    private Enrollment $enrollmentModel;

    public function __construct()
    {
        $this->courseModel     = new Course();
        $this->categoryModel   = new Category();
        $this->enrollmentModel = new Enrollment();
    }

    // ─── Listado público ──────────────────────────────────────────

    public function index(array $params = []): void
    {
        $page       = max(1, (int) ($params['page'] ?? $_GET['page'] ?? 1));
        $categoryId = (int) ($_GET['categoria'] ?? 0) ?: null;
        $courses    = $this->courseModel->published($page, 12, $categoryId);
        $categories = $this->categoryModel->allWithCourseCount();

        $this->view('courses.index', [
            'title'      => 'Cursos Disponibles',
            'courses'    => $courses,
            'categories' => $categories,
            'currentCat' => $categoryId,
        ]);
    }

    // ─── Detalle del curso ────────────────────────────────────────

    public function show(array $params = []): void
    {
        $course = $this->courseModel->findBySlug($params['slug'] ?? '');

        if (!$course) {
            http_response_code(404);
            include BASE_PATH . '/views/errors/404.php';
            return;
        }

        $content  = $this->courseModel->getLessonsAndQuizzes((int) $course['id']);
        $enrolled = false;
        $user     = $this->authUser();

        $completedLessonIds = [];
        $isManager          = $user && in_array($user['role'], ['admin', 'teacher'], true);
        if ($user) {
            $enrolled = $this->enrollmentModel->isEnrolled((int) $user['id'], (int) $course['id']);
            if ($enrolled) {
                $lessonModel        = new Lesson();
                $completedLessonIds = array_map('intval',
                    $lessonModel->completedByStudentInCourse((int) $user['id'], (int) $course['id'])
                );
            }
        }

        $quizzesWithQuestions = [];
        $lessonQuizzes        = $content['lesson_quizzes'] ?? [];
        if ($isManager) {
            $quizModel = new Quiz();
            foreach ($content['quizzes'] as $q) {
                $full = $quizModel->withQuestionsAndOptions((int) $q['id']);
                $quizzesWithQuestions[] = $full ?: $q;
            }
        }

        $this->view('courses.show', [
            'title'                => $course['title'],
            'course'               => $course,
            'lessons'              => $content['lessons'],
            'quizzes'              => $content['quizzes'],
            'quizzesWithQuestions' => $quizzesWithQuestions,
            'lessonQuizzes'        => $lessonQuizzes,
            'enrolled'             => $enrolled,
            'user'                 => $user,
            'completedLessonIds'   => $completedLessonIds,
            'isManager'            => $isManager,
            'categories'           => $isManager ? $this->categoryModel->all() : [],
        ]);
    }

    // ─── Inscripción web ─────────────────────────────────────────

    public function enroll(array $params = []): void
    {
        $this->verifyCsrf();
        $user   = $this->authUser();
        $course = $this->courseModel->findOrFail((int) ($params['id'] ?? 0));

        if ($this->enrollmentModel->isEnrolled((int) $user['id'], (int) $course['id'])) {
            $this->flashError('Ya estás inscrito en este curso.');
            $this->redirect('/cursos/' . $course['slug']);
        }

        $this->enrollmentModel->enroll((int) $user['id'], (int) $course['id']);
        $this->flashSuccess('¡Inscripción exitosa! Ya puedes acceder al contenido.');
        $this->redirect('/cursos/' . $course['slug']);
    }

    // ─── Crear curso ──────────────────────────────────────────────

    public function create(array $params = []): void
    {
        $this->view('courses.create', [
            'title'      => 'Crear Curso',
            'layout'     => 'dashboard',
            'categories' => $this->categoryModel->all(),
        ]);
    }

    public function store(array $params = []): void
    {
        $this->verifyCsrf();
        $user = $this->authUser();

        $data = [
            'teacher_id'  => $user['id'],
            'category_id' => (int) $this->input('category_id') ?: null,
            'title'       => $this->sanitize($this->input('title', '')),
            'description' => $this->sanitize($this->input('description', '')),
            'price'       => (float) $this->input('price', 0),
            'is_free'     => $this->input('is_free') === '1' ? 1 : 0,
            'level'       => $this->input('level', 'beginner'),
            'status'      => $this->input('status', Course::STATUS_DRAFT),
        ];

        // Subida de imagen
        $image = $this->handleImageUpload();
        if ($image) $data['image'] = $image;

        if (empty($data['title'])) {
            $this->flashError('El título es requerido.');
            $this->redirect('/cursos/crear');
        }

        try {
            $id     = $this->courseModel->createWithSlug($data);
            $course = $this->courseModel->find((int) $id);
            $this->flashSuccess('Curso creado. Ahora agrega lecciones y evaluaciones.');
            $this->redirect('/cursos/' . $course['slug']);
        } catch (\Throwable $e) {
            $this->flashError('Error al crear el curso: ' . $e->getMessage());
            $this->redirect('/cursos/crear');
        }
    }

    // ─── Editar curso ─────────────────────────────────────────────

    public function edit(array $params = []): void
    {
        $course = $this->courseModel->findOrFail((int) ($params['id'] ?? 0));
        $this->authorizeOwner($course);

        $this->view('courses.edit', [
            'title'      => 'Editar: ' . $course['title'],
            'layout'     => 'dashboard',
            'course'     => $course,
            'categories' => $this->categoryModel->all(),
        ]);
    }

    public function update(array $params = []): void
    {
        $this->verifyCsrf();
        $course = $this->courseModel->findOrFail((int) ($params['id'] ?? 0));
        $this->authorizeOwner($course);

        $data = [
            'category_id'          => (int) $this->input('category_id') ?: null,
            'title'                => $this->sanitize($this->input('title', '')),
            'description'          => $this->sanitize($this->input('description', '')),
            'price'                => (float) $this->input('price', 0),
            'is_free'              => $this->input('is_free') === '1' ? 1 : 0,
            'level'                => $this->input('level', 'beginner'),
            'status'               => $this->input('status', Course::STATUS_DRAFT),
            'satisfaction_enabled' => $this->input('satisfaction_enabled') === '1' ? 1 : 0,
        ];

        $image = $this->handleImageUpload();
        if ($image) $data['image'] = $image;

        $this->courseModel->update((int) $course['id'], $data);
        $this->flashSuccess('Curso actualizado.');
        $this->redirect('/cursos/' . $course['slug']);
    }

    // ─── Eliminar curso ───────────────────────────────────────────

    public function destroy(array $params = []): void
    {
        $this->verifyCsrf();
        $course = $this->courseModel->findOrFail((int) ($params['id'] ?? 0));
        $this->authorizeOwner($course);

        $this->courseModel->delete((int) $course['id']);
        $this->flashSuccess('Curso eliminado.');
        $this->redirect('/dashboard');
    }

    // ─── Helpers privados ─────────────────────────────────────────

    private function authorizeOwner(array $course): void
    {
        $user = $this->authUser();
        if ($user['role'] === 'admin') return;

        if ((int) $course['teacher_id'] !== (int) $user['id']) {
            http_response_code(403);
            include BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }

    private function handleImageUpload(): ?string
    {
        if (empty($_FILES['image']['name'])) return null;

        $file    = $_FILES['image'];
        $maxSize = (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760);

        if ($file['size'] > $maxSize) {
            $this->flashError('La imagen supera el tamaño máximo permitido.');
            return null;
        }

        // Verificar MIME real del archivo (no el del cliente)
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($mimeToExt[$realMime])) {
            $this->flashError('Solo se permiten imágenes JPG, PNG o WEBP.');
            return null;
        }

        $filename = uniqid('course_', true) . '.' . $mimeToExt[$realMime];
        $dir      = BASE_PATH . '/public/uploads/courses/';

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            $this->flashError('Error al subir la imagen.');
            return null;
        }

        return '/uploads/courses/' . $filename;
    }
}
