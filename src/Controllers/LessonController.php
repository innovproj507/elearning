<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Lesson;
use App\Models\Course;
use App\Models\Enrollment;

class LessonController extends Controller
{
    private Lesson     $lessonModel;
    private Course     $courseModel;
    private Enrollment $enrollmentModel;

    public function __construct()
    {
        $this->lessonModel     = new Lesson();
        $this->courseModel     = new Course();
        $this->enrollmentModel = new Enrollment();
    }

    public function create(array $params = []): void
    {
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));

        $this->view('lessons.create', [
            'title'  => 'Agregar Lección',
            'layout' => 'dashboard',
            'course' => $course,
        ]);
    }

    public function store(array $params = []): void
    {
        $this->verifyCsrf();
        $course   = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $user     = $this->authUser();

        // Solo el profesor dueño o admin puede agregar lecciones
        if ($user['role'] !== 'admin' && (int) $course['teacher_id'] !== (int) $user['id']) {
            $this->flashError('No tienes permiso para agregar lecciones a este curso.');
            $this->redirect('/dashboard');
        }

        $type    = $this->input('type', Lesson::TYPE_TEXT);
        $data    = [
            'course_id' => $course['id'],
            'title'     => $this->sanitize($this->input('title', '')),
            'content'   => $this->purifyHtml($this->input('content', '')),
            'video_url' => $this->sanitize($this->input('video_url', '')),
            'type'      => $type,
            'order_num' => (int) $this->input('order_num', 0),
            'duration'  => (int) $this->input('duration', 0),
        ];

        // Subida de archivo descargable
        if ($type === Lesson::TYPE_FILE && !empty($_FILES['lesson_file']['name'])) {
            $path = $this->handleFileUpload();
            if ($path) $data['file_path'] = $path;
        }

        $this->lessonModel->create($data);
        $this->flashSuccess('Lección creada.');
        $this->redirect('/cursos/' . $course['slug']);
    }

    public function show(array $params = []): void
    {
        $user     = $this->authUser();
        $rawId    = $params['courseId'] ?? '0';
        $courseId = (int) $rawId;
        $course   = $courseId > 0
            ? $this->courseModel->findOrFail($courseId)
            : ($this->courseModel->findBySlug($rawId) ?: $this->courseModel->findOrFail(0));
        $lesson = $this->lessonModel->findOrFail((int) ($params['id'] ?? 0));

        $isManager = in_array($user['role'], ['teacher', 'admin'], true);

        // Bloquear lección desactivada (sólo managers pueden verla)
        if (!$lesson['is_active'] && !$isManager) {
            $this->flashError('Esta lección no está disponible actualmente.');
            $this->redirect('/cursos/' . $course['slug']);
        }

        // Verificar inscripción
        if (!$isManager) {
            if (!$this->enrollmentModel->isEnrolled((int) $user['id'], (int) $course['id'])) {
                $this->flashError('Debes inscribirte para acceder al contenido.');
                $this->redirect('/cursos/' . $course['slug']);
            }

            // Enforcement secuencial: verificar que la lección anterior esté completada
            $prev = $this->lessonModel->previousInCourse((int) $course['id'], (int) $lesson['order_num']);
            if ($prev && !$this->lessonModel->isCompleted((int) $user['id'], (int) $prev['id'])) {
                $this->flashError('Debes completar la lección anterior antes de continuar: "' . $prev['title'] . '".');
                $this->redirect('/cursos/' . $course['slug'] . '/lecciones/' . $prev['id']);
            }
        }

        $isCompleted        = $this->lessonModel->isCompleted((int) $user['id'], (int) $lesson['id']);
        $allLessons         = $this->lessonModel->byCourse((int) $course['id']);
        $completedLessonIds = array_map('intval',
            $this->lessonModel->completedByStudentInCourse((int) $user['id'], (int) $course['id'])
        );
        $quizModel       = new \App\Models\Quiz();
        $quizzes         = $quizModel->byCourse((int) $course['id']);
        $lessonQuiz      = $quizModel->byLesson((int) $lesson['id']);
        $lessonQuizPassed = false;
        if ($lessonQuiz) {
            $best = $quizModel->getBestAttempt((int) $user['id'], (int) $lessonQuiz['id']);
            $lessonQuizPassed = $best && !empty($best['passed']);
        }

        $this->view('lessons.show', [
            'title'              => $lesson['title'],
            'layout'             => 'main',
            'course'             => $course,
            'lesson'             => $lesson,
            'allLessons'         => $allLessons,
            'isCompleted'        => $isCompleted,
            'completedLessonIds' => $completedLessonIds,
            'quizzes'            => $quizzes,
            'lessonQuiz'         => $lessonQuiz,
            'lessonQuizPassed'   => $lessonQuizPassed,
            'isManager'          => $isManager,
        ]);
    }

    public function edit(array $params = []): void
    {
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $lesson = $this->lessonModel->findOrFail((int) ($params['id'] ?? 0));
        $user   = $this->authUser();

        if ($user['role'] !== 'admin' && (int) $course['teacher_id'] !== (int) $user['id']) {
            $this->flashError('Sin permiso.');
            $this->redirect('/dashboard');
        }

        $this->view('lessons.edit', [
            'title'  => 'Editar Lección',
            'layout' => 'dashboard',
            'course' => $course,
            'lesson' => $lesson,
        ]);
    }

    public function update(array $params = []): void
    {
        $this->verifyCsrf();
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $lesson = $this->lessonModel->findOrFail((int) ($params['id'] ?? 0));
        $user   = $this->authUser();

        if ($user['role'] !== 'admin' && (int) $course['teacher_id'] !== (int) $user['id']) {
            $this->flashError('Sin permiso.');
            $this->redirect('/dashboard');
        }

        $type = $this->input('type', $lesson['type']);
        $data = [
            'title'     => $this->sanitize($this->input('title', '')),
            'content'   => $this->purifyHtml($this->input('content', '')),
            'video_url' => $this->sanitize($this->input('video_url', '')),
            'type'      => $type,
            'order_num' => (int) $this->input('order_num', 0),
            'duration'  => (int) $this->input('duration', 0),
        ];

        if ($type === Lesson::TYPE_FILE && !empty($_FILES['lesson_file']['name'])) {
            $path = $this->handleFileUpload();
            if ($path) $data['file_path'] = $path;
        }

        $ok = $this->lessonModel->update((int) $lesson['id'], $data);
        if (!$ok) {
            error_log('[LessonController] update failed for lesson ' . $lesson['id'] . ' — API may have returned error');
            $this->flashError('No se pudo guardar. Intenta de nuevo.');
            $this->redirect('/cursos/' . $course['slug']);
        }
        $this->flashSuccess('Lección actualizada.');
        $this->redirect('/cursos/' . $course['slug']);
    }

    public function destroy(array $params = []): void
    {
        $this->verifyCsrf();
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $lesson = $this->lessonModel->findOrFail((int) ($params['id'] ?? 0));
        $user   = $this->authUser();

        if ($user['role'] !== 'admin' && (int) $course['teacher_id'] !== (int) $user['id']) {
            $this->flashError('Sin permiso.');
            $this->redirect('/dashboard');
        }

        $this->lessonModel->delete((int) $lesson['id']);
        $this->flashSuccess('Lección eliminada.');
        $this->redirect('/cursos/' . $course['slug']);
    }

    public function complete(array $params = []): void
    {
        $this->verifyCsrf();
        $user   = $this->authUser();
        $lesson = $this->lessonModel->findOrFail((int) ($params['id'] ?? 0));
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));

        // Si la lección tiene un quiz obligatorio, el estudiante debe haberlo aprobado
        $quizModel  = new \App\Models\Quiz();
        $lessonQuiz = $quizModel->byLesson((int) $lesson['id']);
        if ($lessonQuiz && !in_array($user['role'], ['admin', 'teacher'], true)) {
            $best = $quizModel->getBestAttempt((int) $user['id'], (int) $lessonQuiz['id']);
            if (!$best || empty($best['passed'])) {
                $this->flashError('Debes aprobar la evaluación de esta lección antes de continuar.');
                $this->redirect('/cursos/' . $course['slug'] . '/lecciones/' . $lesson['id']);
            }
        }

        $this->lessonModel->markComplete((int) $user['id'], (int) $lesson['id']);
        $this->flashSuccess('¡Lección completada!');
        $this->redirect('/cursos/' . $course['slug'] . '/lecciones/' . $lesson['id']);
    }

    public function toggle(array $params = []): void
    {
        $this->verifyCsrf();
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $lesson = $this->lessonModel->findOrFail((int) ($params['id'] ?? 0));
        $user   = $this->authUser();

        if ($user['role'] !== 'admin' && (int) $course['teacher_id'] !== (int) $user['id']) {
            $this->flashError('Sin permiso.');
            $this->redirect('/cursos/' . $course['slug']);
        }

        $newState = empty($lesson['is_active']);
        $this->lessonModel->setActive((int) $lesson['id'], $newState);
        $this->flashSuccess($newState ? 'Lección activada.' : 'Lección desactivada.');
        $this->redirect('/cursos/' . $course['slug']);
    }

    private function handleFileUpload(): ?string
    {
        $file    = $_FILES['lesson_file'];
        $maxSize = (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760);

        if ($file['size'] > $maxSize) {
            $this->flashError('El archivo supera el tamaño máximo.');
            return null;
        }

        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'mp4', 'mp3'];
        if (!in_array($ext, $allowed, true)) {
            $this->flashError('Tipo de archivo no permitido. Permitidos: ' . implode(', ', $allowed));
            return null;
        }

        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'application/zip',
            'application/x-zip-compressed',
            'video/mp4',
            'audio/mpeg',
        ];
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($realMime, $allowedMimes, true)) {
            $this->flashError('Tipo de contenido del archivo no permitido.');
            return null;
        }

        $filename = uniqid('file_', true) . '.' . $ext;
        $dir      = BASE_PATH . '/public/uploads/files/';

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        move_uploaded_file($file['tmp_name'], $dir . $filename);
        return '/public/uploads/files/' . $filename;
    }
}
