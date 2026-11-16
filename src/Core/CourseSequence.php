<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Orden del contenido de un curso: un quiz después de cada tres módulos.
 *
 * Los cursos importados traen primero todas las lecciones y después todos
 * los quizzes sueltos. Esta clase decide dónde se ve cada quiz sin tocar los
 * datos:
 *
 *  - Los quizzes que ya pertenecen a una lección (lesson_id) no entran aquí.
 *  - De los sueltos con preguntas, los que parecen examen final
 *    («Evaluation Test», «Examen…») van siempre al final.
 *  - Los demás ("Quiz #1", "Quiz #2"…) se colocan en orden natural: el
 *    primero tras el módulo 3, el segundo tras el 6, etc. Si sobran, se
 *    quedan al final, antes del examen final.
 *  - Cada quiz exige haber completado los módulos que lo preceden: 3, 6, 9…
 *    para los intercalados y todos para el final.
 *  - Los quizzes sin preguntas no se pueden presentar: los alumnos no los
 *    ven; quien gestiona el curso sí, marcados como vacíos.
 */
final class CourseSequence
{
    public const MODULOS_POR_QUIZ = 3;

    /** Un título con estas palabras es el examen final, no un quiz intermedio. */
    private const PATRON_FINAL = '/evaluation|evaluaci[oó]n|examen|exam\b|final|\btest\b/iu';

    /**
     * @param array<int,array> $lessons  Lecciones del curso, en su orden.
     * @param array<int,array> $quizzes  Quizzes sueltos (con question_count).
     * @return array{
     *   trasLeccion: array<int,array<int,array>>,
     *   alFinal: array<int,array>,
     *   requisitos: array<int,int>
     * } trasLeccion: id de lección → quizzes que se muestran tras ella;
     *   alFinal: el resto, en orden; requisitos: id de quiz → nº de módulos
     *   activos que hay que haber completado antes.
     */
    public static function ordenar(array $lessons, array $quizzes, bool $verVacios = false): array
    {
        $activas = array_values(array_filter($lessons, static fn($l) => !empty($l['is_active'])));
        $total   = count($activas);

        $usables = [];
        $vacios  = [];
        foreach ($quizzes as $q) {
            if ((int) ($q['question_count'] ?? 1) > 0) {
                $usables[] = $q;
            } elseif ($verVacios) {
                $vacios[] = $q;
            }
        }

        $finales    = [];
        $intermedios = [];
        foreach ($usables as $q) {
            if (preg_match(self::PATRON_FINAL, (string) ($q['title'] ?? ''))) {
                $finales[] = $q;
            } else {
                $intermedios[] = $q;
            }
        }

        $porTitulo = static function (array $a, array $b): int {
            return strnatcasecmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''))
                ?: ((int) $a['id'] <=> (int) $b['id']);
        };
        usort($intermedios, $porTitulo);

        $huecos = intdiv($total, self::MODULOS_POR_QUIZ);
        $trasLeccion = [];
        $requisitos  = [];

        foreach (array_slice($intermedios, 0, $huecos) as $n => $q) {
            $tras = ($n + 1) * self::MODULOS_POR_QUIZ;       // módulos que preceden
            $leccion = $activas[$tras - 1];
            $trasLeccion[(int) $leccion['id']][] = $q;
            $requisitos[(int) $q['id']] = $tras;
        }

        $alFinal = array_merge(array_slice($intermedios, $huecos), $finales, $vacios);
        foreach ($alFinal as $q) {
            $requisitos[(int) $q['id']] = $total;
        }

        return ['trasLeccion' => $trasLeccion, 'alFinal' => $alFinal, 'requisitos' => $requisitos];
    }

    /**
     * Cuántos módulos activos hay que haber completado antes de un quiz suelto,
     * o null si el quiz no está entre los sueltos del curso.
     */
    public static function requisitoDe(int $quizId, array $lessons, array $quizzes): ?int
    {
        $req = self::ordenar($lessons, $quizzes, true)['requisitos'];
        return $req[$quizId] ?? null;
    }
}
