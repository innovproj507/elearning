<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Forum;

class ForumController extends Controller
{
    private Forum $forumModel;

    public function __construct()
    {
        $this->forumModel = new Forum();
    }

    public function index(array $params = []): void
    {
        $forums = $this->forumModel->all();
        $this->view('forum.index', [
            'title'  => 'Foros de Discusión',
            'layout' => 'main',
            'forums' => $forums,
        ]);
    }

    public function show(array $params = []): void
    {
        $slug = $params['slug'] ?? '';
        $data = $this->forumModel->findBySlug($slug);
        if (!$data) {
            http_response_code(404);
            include BASE_PATH . '/views/errors/404.php';
            exit;
        }

        $this->view('forum.show', [
            'title'  => $data['forum']['title'],
            'layout' => 'main',
            'forum'  => $data['forum'],
            'topics' => $data['topics'],
        ]);
    }

    public function topic(array $params = []): void
    {
        $id   = (int) ($params['id'] ?? 0);
        $data = $this->forumModel->findTopic($id);
        if (!$data) {
            http_response_code(404);
            include BASE_PATH . '/views/errors/404.php';
            exit;
        }

        $user      = $this->authUser();
        $isManager = $user && in_array($user['role'] ?? '', ['teacher', 'admin'], true);

        $this->view('forum.topic', [
            'title'     => $data['topic']['title'],
            'layout'    => 'main',
            'forum'     => $data['forum'],
            'topic'     => $data['topic'],
            'posts'     => $data['posts'],
            'isManager' => $isManager,
            'user'      => $user,
        ]);
    }

    public function createTopic(array $params = []): void
    {
        $user = $this->authUser();
        if (!$user) {
            $this->redirect('/login');
        }

        $slug = $params['slug'] ?? '';
        $data = $this->forumModel->findBySlug($slug);
        if (!$data) {
            http_response_code(404);
            include BASE_PATH . '/views/errors/404.php';
            exit;
        }

        $this->view('forum.create_topic', [
            'title'  => 'Nuevo Tema',
            'layout' => 'main',
            'forum'  => $data['forum'],
        ]);
    }

    public function storeTopic(array $params = []): void
    {
        $this->verifyCsrf();
        $user = $this->authUser();
        if (!$user) {
            $this->redirect('/login');
        }

        $slug  = $params['slug'] ?? '';
        $data  = $this->forumModel->findBySlug($slug);
        if (!$data) {
            $this->redirect('/foro');
        }

        $title   = trim($this->input('title', ''));
        $content = trim($this->input('content', ''));

        if (empty($title) || empty($content)) {
            $this->flashError('El título y contenido son requeridos.');
            $this->redirect('/foro/' . $slug . '/nuevo');
        }

        $topicId = $this->forumModel->createTopic([
            'forum_id' => (int) $data['forum']['id'],
            'title'    => $this->sanitize($title),
            'content'  => htmlspecialchars(strip_tags($content), ENT_QUOTES, 'UTF-8'),
        ]);

        if (!$topicId) {
            $this->flashError('No se pudo crear el tema. Intenta de nuevo.');
            $this->redirect('/foro/' . $slug);
        }

        $this->flashSuccess('Tema creado.');
        $this->redirect('/foro/tema/' . $topicId);
    }

    public function storePost(array $params = []): void
    {
        $this->verifyCsrf();
        $user = $this->authUser();
        if (!$user) {
            $this->redirect('/login');
        }

        $id      = (int) ($params['id'] ?? 0);
        $content = trim($this->input('content', ''));

        if (empty($content)) {
            $this->flashError('La respuesta no puede estar vacía.');
            $this->redirect('/foro/tema/' . $id);
        }

        $ok = $this->forumModel->createPost($id, htmlspecialchars(strip_tags($content), ENT_QUOTES, 'UTF-8'));

        if (!$ok) {
            $this->flashError('No se pudo enviar la respuesta.');
        } else {
            $this->flashSuccess('Respuesta enviada.');
        }

        $this->redirect('/foro/tema/' . $id);
    }

    public function storeCourseTopicInline(array $params = []): void
    {
        $this->verifyCsrf();
        $user = $this->authUser();
        if (!$user) {
            $this->redirect('/login');
        }

        $courseId = (int) ($params['courseId'] ?? 0);
        $title    = trim($this->input('title', ''));
        $content  = trim($this->input('content', ''));

        if (!empty($title) && !empty($content)) {
            $this->forumModel->createCourseTopic(
                $courseId,
                $this->sanitize($title),
                htmlspecialchars(strip_tags($content), ENT_QUOTES, 'UTF-8')
            );
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/cursos');
    }

    public function deleteTopic(array $params = []): void
    {
        $this->verifyCsrf();
        $user = $this->authUser();
        if (!$user) {
            $this->redirect('/login');
        }

        $id   = (int) ($params['id'] ?? 0);
        $data = $this->forumModel->findTopic($id);
        if (!$data) {
            $this->redirect('/foro');
        }

        $forumSlug = $data['forum']['slug'] ?? '';
        $this->forumModel->deleteTopic($id);
        $this->flashSuccess('Tema eliminado.');
        $this->redirect('/foro/' . $forumSlug);
    }

    public function deletePost(array $params = []): void
    {
        $this->verifyCsrf();
        $user = $this->authUser();
        if (!$user) {
            $this->redirect('/login');
        }

        $id      = (int) ($params['id'] ?? 0);
        $topicId = (int) ($params['topicId'] ?? 0);
        $this->forumModel->deletePost($id);
        $this->flashSuccess('Respuesta eliminada.');
        $this->redirect('/foro/tema/' . $topicId);
    }
}
