<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Quiz extends Model
{
    protected string $table = 'quizzes';

    /**
     * Returns all quizzes for a course.
     * Reuses the courses/:id/content endpoint which returns both lessons and quizzes.
     */
    public function byCourse(int $courseId): array
    {
        $response = $this->api->get("/courses/{$courseId}/content");
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data']['quizzes'] ?? [];
        }
        return [];
    }

    /**
     * Returns a quiz with all its questions and options.
     */
    public function withQuestionsAndOptions(int $quizId): array|false
    {
        $response = $this->api->get("/quizzes/{$quizId}/questions");
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?: false;
        }
        return false;
    }

    /**
     * Returns the student's best attempt for a quiz.
     * Auth is derived from Bearer token server-side.
     */
    public function getBestAttempt(int $userId, int $quizId): array|false
    {
        $response = $this->api->get("/quizzes/{$quizId}/attempts/best");
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?: false;
        }
        return false;
    }

    /**
     * Intentos gastados, los que quedan y si el examen está cerrado.
     *
     * Quien decide es el CMS; esto sólo sirve para enseñárselo al
     * estudiante. Si la API no contesta se devuelve un estado abierto:
     * cortar el acceso por un fallo de red sería peor, y el intento se
     * rechaza igualmente al enviarlo.
     */
    public function estadoIntentos(int $quizId): array
    {
        $abierto = [
            'realizados' => 0, 'limite' => 0, 'restantes' => null,
            'aprobado' => false, 'bloqueado' => false, 'motivo' => null, 'mensaje' => null,
        ];

        // Sin caché a propósito: con la respuesta guardada 30 segundos, un
        // estudiante que acabara de gastar su último intento vería otra vez
        // el formulario al recargar.
        $response = $this->api->get("/quizzes/{$quizId}/attempts/state", [], true);
        if ($response && ($response['status'] ?? '') === 'success' && is_array($response['data'] ?? null)) {
            return $response['data'] + $abierto;
        }

        return $abierto;
    }

    /**
     * Starts a new quiz attempt. Returns the attempt ID.
     * Auth is derived from Bearer token server-side.
     */
    public function startAttempt(int $userId, int $quizId): int|false
    {
        $response = $this->api->post("/quizzes/{$quizId}/start", []);
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return (int) $response['data'];
        }
        return false;
    }

    /**
     * Grades an attempt and returns the score result.
     */
    /**
     * @param int[] $asked Preguntas presentadas, cuando el quiz sortea un
     *   subconjunto del banco. Vacío = se preguntaron todas.
     */
    public function gradeAttempt(int $attemptId, int $quizId, array $answers, array $asked = []): array|false
    {
        $cuerpo = ['answers' => $answers];
        if ($asked) {
            $cuerpo['asked'] = array_values(array_map('intval', $asked));
        }

        $response = $this->api->post("/quizzes/{$quizId}/attempts/{$attemptId}/grade", $cuerpo);
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'];
        }
        return false;
    }

    /**
     * Returns an attempt with all its answers and correct answers.
     */
    public function getAttemptWithAnswers(int $attemptId): array|false
    {
        $response = $this->api->get("/quiz-attempts/{$attemptId}");
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'] ?: false;
        }
        return false;
    }

    /**
     * Creates a quiz and returns its data array (including id).
     * The base Model::create() already handles POST /api/v1/quizzes.
     * Overridden here only to document that $data['id'] holds the new ID.
     */

    /**
     * Updates quiz fields.
     */
    public function update(int $quizId, array $data): bool
    {
        $response = $this->api->post("/quizzes/{$quizId}/update", $data);
        return (bool) ($response && isset($response['status']) && $response['status'] === 'success');
    }

    /**
     * Returns the quiz (with questions+options) attached to a lesson, or false.
     */
    public function byLesson(int $lessonId): array|false
    {
        $response = $this->api->get("/lessons/{$lessonId}/quiz");
        if ($response && ($response['status'] ?? '') === 'success') {
            return $response['data'] ?: false;
        }
        return false;
    }

    /**
     * Deletes a quiz and all its questions, options, and attempts.
     */
    public function delete(int $quizId): bool
    {
        $response = $this->api->post("/quizzes/{$quizId}/delete", []);
        return (bool) ($response && isset($response['status']) && $response['status'] === 'success');
    }
}
