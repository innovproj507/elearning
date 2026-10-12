<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\PluginManager;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class AdminController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ─── Gestión de cursos ───────────────────────────────────────

    public function courses(array $params = []): void
    {
        $courseModel = new Course();
        $this->view('admin.courses', [
            'title'   => 'Gestión de Cursos',
            'layout'  => 'dashboard',
            'courses' => $courseModel->all('created_at DESC'),
        ]);
    }

    // ─── Crear usuario ───────────────────────────────────────────

    public function createUser(array $params = []): void
    {
        $this->view('admin.create-user', [
            'title'  => 'Crear Usuario',
            'layout' => 'dashboard',
        ]);
    }

    public function storeUser(array $params = []): void
    {
        $this->verifyCsrf();

        $name     = $this->sanitize($this->input('name', ''));
        $email    = $this->sanitize($this->input('email', ''));
        $password = $this->input('password', '');
        $role     = $this->input('role', 'student');

        if (empty($name) || empty($email) || strlen($password) < 8) {
            $this->flashError('Completa todos los campos. La contraseña debe tener al menos 8 caracteres.');
            $this->redirect('/admin/usuarios/nuevo');
        }

        if (!\in_array($role, ['admin', 'teacher', 'student'], true)) {
            $role = 'student';
        }

        if ($this->userModel->findByEmail($email)) {
            $this->flashError('Ya existe un usuario con ese email.');
            $this->redirect('/admin/usuarios/nuevo');
        }

        $this->userModel->create([
            'name'      => $name,
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'role'      => $role,
            'is_active' => 1,
        ]);

        $this->flashSuccess("Usuario «{$name}» creado correctamente.");
        $this->redirect('/admin/usuarios/' . $role . 's');
    }

    // ─── Lista de usuarios por rol ────────────────────────────────

    public function users(array $params = []): void
    {
        $role = $params['role'] ?? 'student';
        if (!in_array($role, ['student', 'teacher'], true)) $role = 'student';

        $this->view('admin.users', [
            'title'  => $role === 'teacher' ? 'Gestión de Profesores' : 'Gestión de Estudiantes',
            'layout' => 'dashboard',
            'users'  => $this->userModel->byRole($role),
            'role'   => $role,
        ]);
    }

    // ─── Editar usuario ───────────────────────────────────────────

    public function editUser(array $params = []): void
    {
        $user = $this->userModel->findOrFail((int) ($params['id'] ?? 0));

        $this->view('admin.edit-user', [
            'title'    => 'Editar Usuario',
            'layout'   => 'dashboard',
            'editUser' => $user,
        ]);
    }

    public function updateUser(array $params = []): void
    {
        $this->verifyCsrf();
        $user = $this->userModel->findOrFail((int) ($params['id'] ?? 0));

        $data = [
            'name' => $this->sanitize($this->input('name', '')),
            'email' => $this->sanitize($this->input('email', '')),
            'role'  => $this->input('role', $user['role']),
            'bio'   => $this->sanitize($this->input('bio', '')),
        ];

        if (!in_array($data['role'], ['admin', 'teacher', 'student'], true)) {
            $data['role'] = $user['role'];
        }

        $newPassword = $this->input('new_password', '');
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                $this->flashError('La contraseña debe tener al menos 8 caracteres.');
                $this->redirect('/admin/usuarios/' . $user['id'] . '/editar');
            }
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $this->userModel->update((int) $user['id'], $data);
        $this->flashSuccess('Usuario actualizado.');
        $this->redirect('/admin/usuarios/' . $user['role'] . 's');
    }

    // ─── Estudiantes de un curso ─────────────────────────────────

    public function courseStudents(array $params = []): void
    {
        $courseModel     = new Course();
        $enrollmentModel = new Enrollment();

        $course  = $courseModel->findOrFail((int) ($params['id'] ?? 0));
        $students = $enrollmentModel->byCourseWithResults((int) $course['id']);

        $allStudents = $this->userModel->byRole('student');

        $enrolledIds = array_column($students, 'student_id');
        $available   = array_filter($allStudents, fn($u) => !in_array((int)$u['id'], $enrolledIds, true));

        $this->view('admin.course-students', [
            'title'     => 'Estudiantes — ' . $course['title'],
            'layout'    => 'dashboard',
            'course'    => $course,
            'students'  => $students,
            'available' => array_values($available),
        ]);
    }

    public function enrollStudent(array $params = []): void
    {
        $this->verifyCsrf();

        $courseId  = (int) ($params['id'] ?? 0);
        $studentId = (int) $this->input('student_id', 0);

        if (!$courseId || !$studentId) {
            $this->flashError('Datos inválidos.');
            $this->redirect("/admin/cursos/{$courseId}/estudiantes");
        }

        $courseModel = new Course();
        $courseModel->findOrFail($courseId);

        $student = $this->userModel->findOrFail($studentId);
        if ($student['role'] !== 'student') {
            $this->flashError('El usuario seleccionado no es un estudiante.');
            $this->redirect("/admin/cursos/{$courseId}/estudiantes");
        }

        try {
            $enrollmentModel = new Enrollment();
            $enrollmentModel->enroll($studentId, $courseId);
            $this->flashSuccess("«{$student['name']}» inscrito correctamente.");
        } catch (\LogicException $e) {
            $this->flashError($e->getMessage());
        }

        $this->redirect("/admin/cursos/{$courseId}/estudiantes");
    }

    public function unenrollStudent(array $params = []): void
    {
        $this->verifyCsrf();

        $courseId    = (int) ($params['id'] ?? 0);
        $enrollmentId = (int) ($params['enrollmentId'] ?? 0);

        $db  = \App\Core\Database::getInstance();
        $row = $db->queryOne("SELECT * FROM enrollments WHERE id = ? AND course_id = ?", [$enrollmentId, $courseId]);

        if (!$row) {
            $this->flashError('Inscripción no encontrada.');
            $this->redirect("/admin/cursos/{$courseId}/estudiantes");
        }

        $db->execute("DELETE FROM enrollments WHERE id = ?", [$enrollmentId]);
        $this->flashSuccess('Inscripción eliminada.');
        $this->redirect("/admin/cursos/{$courseId}/estudiantes");
    }

    // ─── Plugins ────────────────────────────────────────────────

    public function plugins(array $params = []): void
    {
        $manager = new PluginManager();
        $manager->sync();

        $this->view('admin.plugins', [
            'title'   => 'Gestión de Plugins',
            'layout'  => 'dashboard',
            'plugins' => $manager->all(),
        ]);
    }

    public function togglePlugin(array $params = []): void
    {
        $this->verifyCsrf();
        $id      = (int) ($params['id'] ?? 0);
        $manager = new PluginManager();
        $db      = \App\Core\Database::getInstance();
        $plugin  = $db->queryOne("SELECT * FROM plugins WHERE id = ?", [$id]);

        if (!$plugin) {
            $this->flashError('Plugin no encontrado.');
            $this->redirect('/admin/plugins');
        }

        if ($plugin['is_active']) {
            $manager->deactivate($id);
            $this->flashSuccess("Plugin \"{$plugin['name']}\" desactivado.");
        } else {
            if ($manager->activate($id)) {
                $this->flashSuccess("Plugin \"{$plugin['name']}\" activado.");
            } else {
                $this->flashError("No se pudo activar el plugin — verifica que plugin.php existe.");
            }
        }

        $this->redirect('/admin/plugins');
    }

    // ─── Bloquear / desbloquear ───────────────────────────────────

    public function toggleBlock(array $params = []): void
    {
        $this->verifyCsrf();
        $user = $this->userModel->findOrFail((int) ($params['id'] ?? 0));

        if ($user['role'] === 'admin') {
            $this->flashError('No puedes bloquear a un administrador.');
            $this->redirect('/admin/usuarios/students');
        }

        $newState = empty($user['is_active']) ? true : false;
        $this->userModel->setActive((int) $user['id'], $newState);

        $msg = $newState ? 'Usuario desbloqueado.' : 'Usuario bloqueado.';
        $this->flashSuccess($msg);
        $this->redirect('/admin/usuarios/' . $user['role'] . 's');
    }
}
