<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\CSRF;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;

class DashboardController extends Controller
{
    public function index(array $params = []): void
    {
        $user = $this->authUser();

        if (($user['role'] ?? '') === 'admin') {
            $this->redirect('/cursos');
        }

        $data = match ($user['role']) {
            'admin'   => $this->adminData(),
            'teacher' => $this->teacherData($user),
            default   => $this->studentData($user),
        };

        $layout = $user['role'] === 'admin' ? 'dashboard' : 'main';

        $this->view('dashboard.' . $user['role'], array_merge([
            'title'  => 'Dashboard',
            'layout' => $layout,
            'user'   => $user,
        ], $data));
    }

    public function profile(array $params = []): void
    {
        $user = $this->authUser();
        $this->view('dashboard.profile', [
            'title'  => 'Mi Perfil',
            'layout' => $user['role'] === 'admin' ? 'dashboard' : 'main',
            'user'   => $user,
        ]);
    }

    public function updateProfile(array $params = []): void
    {
        $this->verifyCsrf();
        $user  = $this->authUser();
        $model = new User();

        $data = [
            'name' => $this->sanitize($this->input('name', '')),
            'bio'  => $this->sanitize($this->input('bio', '')),
        ];

        // Cambio de contraseña opcional
        $newPassword = $this->input('new_password', '');
        $oldPassword = $this->input('current_password', '');

        if (!empty($newPassword)) {
            $fullUser = $model->find((int) $user['id']);
            if (!password_verify($oldPassword, $fullUser['password'])) {
                $this->flashError('La contraseña actual es incorrecta.');
                $this->redirect('/dashboard/perfil');
            }
            if (strlen($newPassword) < 8) {
                $this->flashError('La nueva contraseña debe tener al menos 8 caracteres.');
                $this->redirect('/dashboard/perfil');
            }
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $model->update((int) $user['id'], $data);

        // Actualizar sesión
        $updated = $model->sanitize($model->find((int) $user['id']));
        \App\Core\Session::set('user', $updated);

        $this->flashSuccess('Perfil actualizado.');
        $this->redirect('/dashboard/perfil');
    }

    // ─── Data por rol ─────────────────────────────────────────────

    private function studentData(array $user): array
    {
        $enrollmentModel = new Enrollment();
        return [
            'enrollments' => $enrollmentModel->byStudentWithProgress((int) $user['id']),
        ];
    }

    private function teacherData(array $user): array
    {
        $courseModel     = new Course();
        $enrollmentModel = new Enrollment();
        $courses         = $courseModel->byTeacher((int) $user['id']);

        return [
            'courses'         => $courses,
            'total_students'  => array_sum(array_column($courses, 'total_students')),
        ];
    }

    private function adminData(): array
    {
        $userModel       = new User();
        $courseModel     = new Course();
        $enrollmentModel = new Enrollment();

        return [
            'stats' => [
                'students'    => $userModel->countByRole('student'),
                'teachers'    => $userModel->countByRole('teacher'),
                'courses'     => $courseModel->countAll(),
                'published'   => $courseModel->countPublished(),
                'enrollments' => $enrollmentModel->countTotal(),
            ],
            'courses'        => $courseModel->all(['order' => 'created_at DESC']),
        ];
    }
}
