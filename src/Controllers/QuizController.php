<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Course;
use App\Models\Enrollment;

class QuizController extends Controller
{
    private Quiz       $quizModel;
    private Question   $questionModel;
    private Course     $courseModel;
    private Enrollment $enrollmentModel;

    public function __construct()
    {
        $this->quizModel       = new Quiz();
        $this->questionModel   = new Question();
        $this->courseModel     = new Course();
        $this->enrollmentModel = new Enrollment();
    }

    // ─── Crear quiz ───────────────────────────────────────────────

    public function create(array $params = []): void
    {
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));

        $this->view('quizzes.create', [
            'title'  => 'Crear Quiz',
            'layout' => 'dashboard',
            'course' => $course,
        ]);
    }

    public function store(array $params = []): void
    {
        $this->verifyCsrf();

        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $user   = $this->authUser();

        if ($user['role'] !== 'admin' && (int) $course['teacher_id'] !== (int) $user['id']) {
            $this->flashError('Sin permisos para crear quizzes en este curso.');
            $this->redirect('/dashboard');
        }

        // 1. Crear el quiz
        $lessonId  = (int) $this->input('lesson_id', 0) ?: null;
        $quizData  = [
            'course_id'       => $course['id'],
            'title'           => $this->sanitize($this->input('title', '')),
            'description'     => $this->sanitize($this->input('description', '')),
            'pass_percentage' => (float) $this->input('pass_percentage', 70),
            'time_limit'      => (int) $this->input('time_limit', 0),
        ];
        if ($lessonId) $quizData['lesson_id'] = $lessonId;

        $quizId = $this->quizModel->create($quizData);

        // 2. Procesar preguntas enviadas en el formulario
        // Formato: questions[0][text], questions[0][type], questions[0][points]
        //          questions[0][options][0][text], questions[0][options][0][is_correct]
        $questions = $_POST['questions'] ?? [];

        foreach ($questions as $i => $qData) {
            $options = array_map(
                fn($opt) => [
                    'text'       => $this->sanitize($opt['text'] ?? ''),
                    'is_correct' => isset($opt['is_correct']) ? 1 : 0,
                ],
                $qData['options'] ?? []
            );

            if (empty($options)) continue;

            $this->questionModel->createWithOptions([
                'quiz_id'   => $quizId,
                'question'  => $this->sanitize($qData['text'] ?? ''),
                'type'      => $qData['type'] ?? Question::TYPE_MULTIPLE_CHOICE,
                'points'    => (int) ($qData['points'] ?? 1),
                'order_num' => $i,
            ], $options);
        }

        $this->flashSuccess('Quiz creado con ' . count($questions) . ' preguntas.');
        $this->redirect('/cursos/' . $course['slug']);
    }

    // ─── Mostrar quiz para responder ──────────────────────────────

    public function show(array $params = []): void
    {
        $user   = $this->authUser();
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $quiz   = $this->quizModel->withQuestionsAndOptions((int) ($params['id'] ?? 0));

        if (!$quiz) {
            http_response_code(404);
            include BASE_PATH . '/views/errors/404.php';
            return;
        }

        $isManager = \in_array($user['role'], ['teacher', 'admin'], true);

        // Bloquear quiz desactivado
        if (!($quiz['is_active'] ?? 1) && !$isManager) {
            $this->flashError('Este quiz no está disponible actualmente.');
            $this->redirect('/cursos/' . $course['slug']);
        }

        // Verificar inscripción
        if (!$isManager) {
            if (!$this->enrollmentModel->isEnrolled((int) $user['id'], (int) $course['id'])) {
                $this->flashError('Debes estar inscrito para realizar el quiz.');
                $this->redirect('/cursos/' . $course['slug']);
            }

            $lessonModel = new \App\Models\Lesson();

            if (!empty($quiz['lesson_id'])) {
                // Quiz de lección: verificar acceso secuencial a la lección correspondiente
                $lessonId  = (int) $quiz['lesson_id'];
                $allCourse = $lessonModel->byCourse((int) $course['id']);
                $lessonRow = current(array_filter($allCourse, fn($l) => (int) $l['id'] === $lessonId)) ?: null;
                if ($lessonRow) {
                    $prevLesson = $lessonModel->previousInCourse((int) $course['id'], (int) $lessonRow['order_num']);
                    if ($prevLesson && !$lessonModel->isCompleted((int) $user['id'], (int) $prevLesson['id'])) {
                        $this->flashError('Debes completar la lección anterior antes de acceder a esta evaluación.');
                        $this->redirect('/cursos/' . $course['slug'] . '/lecciones/' . $lessonId);
                    }
                }
            } else {
                // Quiz de curso: todas las lecciones activas deben estar completadas
                $activeLessons = array_values(array_filter(
                    $lessonModel->byCourse((int) $course['id']),
                    fn($l) => !empty($l['is_active'])
                ));
                $completedIds = $lessonModel->completedByStudentInCourse((int) $user['id'], (int) $course['id']);

                $pending = array_filter($activeLessons, fn($l) => !\in_array((int) $l['id'], array_map('intval', $completedIds)));
                if (!empty($pending)) {
                    $first = reset($pending);
                    $this->flashError('Debes completar todas las lecciones antes de realizar el quiz. Pendiente: "' . $first['title'] . '".');
                    $this->redirect('/cursos/' . $course['slug'] . '/lecciones/' . $first['id']);
                }
            }
        }

        $bestAttempt = $this->quizModel->getBestAttempt((int) $user['id'], (int) $quiz['id']);

        // Estado de los intentos según el CMS: aprobado es aprobado y no se
        // repite para subir nota, y además hay un tope de intentos. Las dos
        // cosas cierran el examen; aquí sólo se refleja lo que dice la API.
        $intentos   = $isManager
            ? ['bloqueado' => false, 'restantes' => null, 'realizados' => 0, 'limite' => 0,
               'aprobado' => false, 'motivo' => null, 'mensaje' => null]
            : $this->quizModel->estadoIntentos((int) $quiz['id']);
        $yaAprobado = !empty($intentos['aprobado']);
        $cerrado    = !empty($intentos['bloqueado']);

        $quiz['questions'] = $cerrado ? [] : $this->sortearPreguntas($quiz, (int) $user['id']);

        $this->view('quizzes.show', [
            'title'       => $quiz['title'],
            'layout'      => 'main',
            'course'      => $course,
            'quiz'        => $quiz,
            'bestAttempt' => $bestAttempt,
            'yaAprobado'  => $yaAprobado,
            'cerrado'     => $cerrado,
            'intentos'    => $intentos,
        ]);
    }

    /**
     * Devuelve las preguntas que se le presentan al estudiante.
     *
     * Si el quiz tiene questions_per_attempt, se sortea ese número de
     * entre todas las del banco. El sorteo se guarda en la sesión: al
     * recargar la página salen las mismas, de modo que nadie pueda
     * recargar hasta que le toquen las fáciles. Se descarta al enviar.
     *
     * @return array Preguntas a mostrar, en orden aleatorio.
     */
    private function sortearPreguntas(array $quiz, int $userId): array
    {
        $todas  = $quiz['questions'] ?? [];
        $cuanto = (int) ($quiz['questions_per_attempt'] ?? 0);

        if ($cuanto <= 0 || $cuanto >= count($todas)) {
            return $todas;
        }

        $clave = 'quiz_sorteo_' . (int) $quiz['id'] . '_' . $userId;
        $porId = [];
        foreach ($todas as $p) {
            $porId[(int) $p['id']] = $p;
        }

        // ¿Hay un sorteo vigente y siguen existiendo esas preguntas?
        $guardado = $_SESSION[$clave] ?? null;
        if (is_array($guardado)) {
            $vigentes = array_values(array_filter($guardado, fn($id) => isset($porId[$id])));
            if (count($vigentes) === $cuanto) {
                return array_map(fn($id) => $porId[$id], $vigentes);
            }
        }

        $ids = array_keys($porId);
        shuffle($ids);
        $ids = array_slice($ids, 0, $cuanto);

        $_SESSION[$clave] = $ids;

        return array_map(fn($id) => $porId[$id], $ids);
    }

    // ─── Enviar respuestas ────────────────────────────────────────

    public function submit(array $params = []): void
    {
        $this->verifyCsrf();

        $user   = $this->authUser();
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $quizId = (int) ($params['id'] ?? 0);

        // Examen cerrado —aprobado o sin intentos— no admite otro envío. Se
        // comprueba aquí y no sólo al pintar la pantalla: sin esto bastaría
        // con enviar el formulario a mano. El CMS lo rechaza igualmente al
        // iniciar el intento; esto es para dar un mensaje decente.
        $intentos = $this->quizModel->estadoIntentos($quizId);
        if (!empty($intentos['bloqueado'])) {
            $mejor = $this->quizModel->getBestAttempt((int) $user['id'], $quizId);
            $this->flashError($intentos['mensaje'] ?: 'Esta evaluación ya no admite nuevos intentos.');
            $this->redirect($mejor
                ? "/cursos/{$course['id']}/quizzes/{$quizId}/resultados?attempt=" . (int) $mejor['id']
                : "/cursos/{$course['slug']}");
        }

        // Iniciar intento
        $attemptId = $this->quizModel->startAttempt((int) $user['id'], $quizId);

        // El CMS tiene la última palabra: si rechazó el intento (tope
        // agotado en una pestaña abierta hace rato), no hay nada que
        // calificar y no debe crearse una nota de la nada.
        if (!$attemptId) {
            $this->flashError('Esta evaluación ya no admite nuevos intentos.');
            $this->redirect("/cursos/{$course['id']}/quizzes/{$quizId}");
        }

        // Respuestas: answers[question_id] = option_id
        $answers = array_map('intval', $_POST['answers'] ?? []);

        // Preguntas que se le presentaron. Manda el sorteo guardado en la
        // sesión, no lo que llegue del formulario: así nadie puede acortar
        // su propio examen. El formulario sólo sirve de respaldo si la
        // sesión se perdió. El CMS las valida de nuevo contra el quiz.
        $clave  = 'quiz_sorteo_' . $quizId . '_' . (int) $user['id'];
        $asked  = $_SESSION[$clave] ?? array_map('intval', $_POST['asked'] ?? []);
        unset($_SESSION[$clave]);

        $result = $this->quizModel->gradeAttempt((int) $attemptId, $quizId, $answers, $asked);

        // Si es quiz de lección y el estudiante aprobó → marcar lección como completada
        if ($result['passed']) {
            $quizData = $this->quizModel->withQuestionsAndOptions($quizId);
            if ($quizData && !empty($quizData['lesson_id'])) {
                (new \App\Models\Lesson())->markComplete((int) $user['id'], (int) $quizData['lesson_id']);
            }
        }

        $this->flashSuccess(
            sprintf(
                'Quiz completado. Puntuación: %.1f%%. %s',
                $result['score'],
                $result['passed'] ? '¡Aprobado!' : 'No aprobado, puedes intentarlo de nuevo.'
            )
        );

        $this->redirect("/cursos/{$course['id']}/quizzes/{$quizId}/resultados?attempt={$attemptId}");
    }

    // ─── Resultados ───────────────────────────────────────────────

    public function edit(array $params = []): void
    {
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $quiz   = $this->quizModel->withQuestionsAndOptions((int) ($params['id'] ?? 0));
        $user   = $this->authUser();

        if (!$quiz) {
            http_response_code(404);
            include BASE_PATH . '/views/errors/404.php';
            return;
        }

        if ($user['role'] !== 'admin' && (int) $course['teacher_id'] !== (int) $user['id']) {
            $this->flashError('Sin permiso.');
            $this->redirect('/dashboard');
        }

        $this->view('quizzes.edit', [
            'title'  => 'Editar Quiz',
            'layout' => 'dashboard',
            'course' => $course,
            'quiz'   => $quiz,
        ]);
    }

    public function update(array $params = []): void
    {
        $this->verifyCsrf();
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $user   = $this->authUser();
        $quizId = (int) ($params['id'] ?? 0);

        if ($user['role'] !== 'admin' && (int) $course['teacher_id'] !== (int) $user['id']) {
            $this->flashError('Sin permiso.');
            $this->redirect('/dashboard');
        }

        $this->quizModel->update($quizId, [
            'title'           => $this->sanitize($this->input('title', '')),
            'description'     => $this->sanitize($this->input('description', '')),
            'pass_percentage' => (float) $this->input('pass_percentage', 70),
            'time_limit'      => (int) $this->input('time_limit', 0),
        ]);

        $questions = $_POST['questions'] ?? [];
        foreach ($questions as $i => $qData) {
            $options = array_map(
                fn($opt) => [
                    'text'       => $this->sanitize($opt['text'] ?? ''),
                    'is_correct' => isset($opt['is_correct']) ? 1 : 0,
                ],
                $qData['options'] ?? []
            );
            if (empty($options)) continue;
            $this->questionModel->createWithOptions([
                'quiz_id'   => $quizId,
                'question'  => $this->sanitize($qData['text'] ?? ''),
                'type'      => $qData['type'] ?? Question::TYPE_MULTIPLE_CHOICE,
                'points'    => (int) ($qData['points'] ?? 1),
                'order_num' => 999 + $i,
            ], $options);
        }

        $this->flashSuccess('Quiz actualizado.');
        $this->redirect('/cursos/' . $course['slug']);
    }

    public function deleteQuestion(array $params = []): void
    {
        $this->verifyCsrf();
        $course     = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $quizId     = (int) ($params['id'] ?? 0);
        $questionId = (int) ($params['questionId'] ?? 0);
        $user       = $this->authUser();

        if ($user['role'] !== 'admin' && (int) $course['teacher_id'] !== (int) $user['id']) {
            $this->flashError('Sin permiso.');
            $this->redirect('/dashboard');
        }

        $this->questionModel->deleteQuestion($questionId);
        $this->flashSuccess('Pregunta eliminada.');
        $this->redirect('/cursos/' . $course['slug']);
    }

    public function destroy(array $params = []): void
    {
        $this->verifyCsrf();
        $course = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $quiz   = $this->quizModel->findOrFail((int) ($params['id'] ?? 0));
        $user   = $this->authUser();

        if ($user['role'] !== 'admin' && (int) $course['teacher_id'] !== (int) $user['id']) {
            $this->flashError('Sin permiso.');
            $this->redirect('/dashboard');
        }

        $this->quizModel->delete((int) $quiz['id']);
        $this->flashSuccess('Quiz eliminado.');
        $this->redirect('/cursos/' . $course['slug']);
    }

    public function results(array $params = []): void
    {
        $user      = $this->authUser();
        $course    = $this->courseModel->findOrFail((int) ($params['courseId'] ?? 0));
        $quiz      = $this->quizModel->findOrFail((int) ($params['id'] ?? 0));
        $attemptId = (int) ($_GET['attempt'] ?? 0);

        $attempt = $this->quizModel->getAttemptWithAnswers($attemptId);

        if (!$attempt || (int) $attempt['student_id'] !== (int) $user['id']) {
            $this->flashError('Intento no encontrado.');
            $this->redirect('/dashboard');
        }

        $this->view('quizzes.results', [
            'title'   => 'Resultados: ' . $quiz['title'],
            'layout'  => 'dashboard',
            'course'  => $course,
            'quiz'    => $quiz,
            'attempt' => $attempt,
        ]);
    }
}
