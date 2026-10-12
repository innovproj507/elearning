<?php

declare(strict_types=1);

/**
 * Rutas Web — renderizado de vistas con sesiones y CSRF.
 *
 * Formato: $router->método('/ruta', 'Controlador@acción', ['middleware', ...])
 * Middlewares disponibles: 'auth', 'role:teacher,admin'
 */

// ─── Página principal ─────────────────────────────────────────────
$router->get('/',                                'HomeController@index');

// ─── Autenticación ────────────────────────────────────────────────
$router->get('/login',                           'AuthController@showLogin');
$router->post('/login',                          'AuthController@login');
// Registro deshabilitado — acceso solo por admisión oficial
// $router->get('/register',  'AuthController@showRegister');
// $router->post('/register', 'AuthController@register');

// Recuperación de contraseña
$router->get('/recuperar-contrasena',            'PasswordResetController@showRequest');
$router->post('/recuperar-contrasena',           'PasswordResetController@sendCode');
$router->get('/recuperar-contrasena/verificar',  'PasswordResetController@showVerify');
$router->post('/recuperar-contrasena/verificar', 'PasswordResetController@resetPassword');
$router->post('/logout',                         'AuthController@logout');

// ─── Cursos — rutas estáticas primero (antes del patrón {slug}) ───
$router->get('/cursos',                          'CourseController@index');
$router->get('/cursos/crear',                    'CourseController@create',  ['auth', 'role:teacher,admin']);
$router->post('/cursos/crear',                   'CourseController@store',   ['auth', 'role:teacher,admin']);

// ─── Cursos — patrones dinámicos ──────────────────────────────────
$router->get('/cursos/{id}/editar',              'CourseController@edit',    ['auth', 'role:teacher,admin']);
$router->post('/cursos/{id}/editar',             'CourseController@update',  ['auth', 'role:teacher,admin']);
$router->post('/cursos/{id}/eliminar',           'CourseController@destroy', ['auth', 'role:teacher,admin']);
$router->get('/cursos/{slug}',                   'CourseController@show');
$router->post('/cursos/{id}/inscribir',          'CourseController@enroll',  ['auth']);

// ─── Lecciones ────────────────────────────────────────────────────
$router->get('/cursos/{courseId}/lecciones/crear',           'LessonController@create',  ['auth', 'role:teacher,admin']);
$router->post('/cursos/{courseId}/lecciones/crear',          'LessonController@store',   ['auth', 'role:teacher,admin']);
$router->get('/cursos/{courseId}/lecciones/{id}/editar',     'LessonController@edit',    ['auth', 'role:teacher,admin']);
$router->post('/cursos/{courseId}/lecciones/{id}/editar',    'LessonController@update',  ['auth', 'role:teacher,admin']);
$router->post('/cursos/{courseId}/lecciones/{id}/eliminar',  'LessonController@destroy', ['auth', 'role:teacher,admin']);
$router->get('/cursos/{courseId}/lecciones/{id}',            'LessonController@show',    ['auth']);
$router->post('/cursos/{courseId}/lecciones/{id}/completar', 'LessonController@complete',['auth']);
$router->post('/cursos/{courseId}/lecciones/{id}/toggle',    'LessonController@toggle',   ['auth', 'role:teacher,admin']);

// ─── Quizzes ─────────────────────────────────────────────────────
$router->get('/cursos/{courseId}/quizzes/crear',             'QuizController@create',    ['auth', 'role:teacher,admin']);
$router->post('/cursos/{courseId}/quizzes/crear',            'QuizController@store',     ['auth', 'role:teacher,admin']);
$router->get('/cursos/{courseId}/quizzes/{id}/editar',       'QuizController@edit',      ['auth', 'role:teacher,admin']);
$router->post('/cursos/{courseId}/quizzes/{id}/editar',      'QuizController@update',    ['auth', 'role:teacher,admin']);
$router->post('/cursos/{courseId}/quizzes/{id}/eliminar',    'QuizController@destroy',   ['auth', 'role:teacher,admin']);
$router->post('/cursos/{courseId}/quizzes/{id}/preguntas/{questionId}/eliminar', 'QuizController@deleteQuestion', ['auth', 'role:teacher,admin']);
$router->get('/cursos/{courseId}/quizzes/{id}/resultados',   'QuizController@results',   ['auth']);
$router->get('/cursos/{courseId}/quizzes/{id}',              'QuizController@show',      ['auth']);
$router->post('/cursos/{courseId}/quizzes/{id}/responder',   'QuizController@submit',    ['auth']);

// ─── Admin — gestión de cursos ───────────────────────────────────
$router->get('/admin/cursos',                                        'AdminController@courses',        ['auth', 'role:admin']);
$router->get('/admin/cursos/{id}/estudiantes',                       'AdminController@courseStudents', ['auth', 'role:admin']);
$router->post('/admin/cursos/{id}/estudiantes/inscribir',            'AdminController@enrollStudent',  ['auth', 'role:admin']);
$router->post('/admin/cursos/{id}/estudiantes/{enrollmentId}/eliminar', 'AdminController@unenrollStudent', ['auth', 'role:admin']);

// ─── Admin — categorías ──────────────────────────────────────────
$router->get('/admin/categorias',                            'CategoryController@index',    ['auth', 'role:admin']);
$router->post('/admin/categorias',                           'CategoryController@store',    ['auth', 'role:admin']);
$router->post('/admin/categorias/{id}/editar',               'CategoryController@update',   ['auth', 'role:admin']);
$router->post('/admin/categorias/{id}/eliminar',             'CategoryController@destroy',  ['auth', 'role:admin']);

// ─── Admin — gestión de usuarios ─────────────────────────────────
$router->get('/admin/usuarios/nuevo',                        'AdminController@createUser',  ['auth', 'role:admin']);
$router->post('/admin/usuarios/nuevo',                       'AdminController@storeUser',   ['auth', 'role:admin']);
$router->get('/admin/usuarios/{role}',                       'AdminController@users',       ['auth', 'role:admin']);
$router->get('/admin/usuarios/{id}/editar',                  'AdminController@editUser',    ['auth', 'role:admin']);
$router->post('/admin/usuarios/{id}/editar',                 'AdminController@updateUser',  ['auth', 'role:admin']);
$router->post('/admin/usuarios/{id}/bloquear',               'AdminController@toggleBlock', ['auth', 'role:admin']);
$router->get('/admin/plugins',                               'AdminController@plugins',        ['auth', 'role:admin']);
$router->post('/admin/plugins/{id}/toggle',                  'AdminController@togglePlugin',   ['auth', 'role:admin']);
$router->get('/admin/configuracion',                         'SettingsController@index',        ['auth', 'role:admin']);
$router->post('/admin/configuracion',                        'SettingsController@update',       ['auth', 'role:admin']);

// ─── Upload de imágenes (editor de lecciones) ────────────────────
$router->post('/upload/imagen',                      'UploadController@image',  ['auth', 'role:teacher,admin']);

// ─── Foro por curso (desde la página del curso) ───────────────────
$router->post('/foro/curso/{courseId}/nuevo',           'ForumController@storeCourseTopicInline', ['auth']);

// ─── Foros ────────────────────────────────────────────────────────
$router->get('/foro',                               'ForumController@index');
$router->get('/foro/{slug}/nuevo',                  'ForumController@createTopic',  ['auth']);
$router->post('/foro/{slug}/nuevo',                 'ForumController@storeTopic',   ['auth']);
$router->get('/foro/tema/{id}',                     'ForumController@topic');
$router->post('/foro/tema/{id}/responder',          'ForumController@storePost',    ['auth']);
$router->post('/foro/tema/{id}/eliminar',           'ForumController@deleteTopic',  ['auth']);
$router->post('/foro/post/{id}/eliminar/{topicId}', 'ForumController@deletePost',   ['auth']);
$router->get('/foro/{slug}',                        'ForumController@show');

// ─── Dashboard ────────────────────────────────────────────────────
$router->get('/dashboard',                       'DashboardController@index',         ['auth']);
$router->get('/dashboard/perfil',                'DashboardController@profile',       ['auth']);
$router->post('/dashboard/perfil',               'DashboardController@updateProfile', ['auth']);
