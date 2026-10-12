<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Question extends Model
{
    protected string $table = 'questions';

    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    public const TYPE_TRUE_FALSE      = 'true_false';

    /**
     * Creates a question with its options via a single API call.
     * $data['quiz_id'] may be an int or the array returned by Quiz::create().
     *
     * @param array $data    ['quiz_id', 'question', 'type', 'points', 'order_num']
     * @param array $options [['text' => '...', 'is_correct' => 0|1], ...]
     */
    public function createWithOptions(array $data, array $options): int|false
    {
        // Quiz::create() returns the quiz data array; extract the id if needed
        $quizId = is_array($data['quiz_id'])
            ? (int) ($data['quiz_id']['id'] ?? 0)
            : (int) $data['quiz_id'];

        if (!$quizId) return false;

        $response = $this->api->post("/quizzes/{$quizId}/questions", [
            'question'  => $data['question']  ?? '',
            'type'      => $data['type']       ?? self::TYPE_MULTIPLE_CHOICE,
            'points'    => $data['points']     ?? 1,
            'order_num' => $data['order_num']  ?? 0,
            'options'   => $options,
        ]);

        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return (int) ($response['data']['id'] ?? 0);
        }
        return false;
    }

    public function deleteQuestion(int $questionId): bool
    {
        $response = $this->api->post("/questions/{$questionId}/delete", []);
        return (bool) ($response && ($response['status'] ?? '') === 'success');
    }

    /**
     * Returns questions with options for a quiz.
     * In practice, use Quiz::withQuestionsAndOptions() instead.
     */
    public function byQuiz(int $quizId): array
    {
        $response = $this->api->get("/quizzes/{$quizId}/questions");
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data']['questions'] ?? [];
        }
        return [];
    }
}
