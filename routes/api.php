<?php

declare(strict_types=1);

/**
 * Rutas API RESTful — responden siempre en JSON con JWT.
 *
 * Middleware 'api_auth' valida el Bearer token en cada petición.
 * Prefijo: /api/
 */

// ─── Autenticación ────────────────────────────────────────────────
$router->post('/api/auth/login',    'Api\AuthApiController@login');
$router->post('/api/auth/register', 'Api\AuthApiController@register');
$router->get('/api/auth/me',        'Api\AuthApiController@me', ['api_auth']);

// ─── Cursos (lectura pública) ─────────────────────────────────────
$router->get('/api/cursos',         'Api\CourseApiController@index');
$router->get('/api/cursos/{id}',    'Api\CourseApiController@show');

// ─── Inscripciones ────────────────────────────────────────────────
$router->post('/api/inscribir',     'Api\EnrollmentApiController@enroll',    ['api_auth']);
$router->get('/api/mis-cursos',     'Api\EnrollmentApiController@myCourses', ['api_auth']);

// ─── Progreso y calificaciones ────────────────────────────────────
$router->get('/api/progreso/{courseId}',      'Api\ProgressApiController@getCourseProgress', ['api_auth']);
$router->get('/api/calificaciones/{quizId}',  'Api\ProgressApiController@getQuizGrades',     ['api_auth']);
