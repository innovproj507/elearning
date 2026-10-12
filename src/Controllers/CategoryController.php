<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    private Category $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function index(array $params = []): void
    {
        $this->view('admin.categories', [
            'title'      => 'Categorías',
            'layout'     => 'dashboard',
            'categories' => $this->categoryModel->allWithCourseCount(),
        ]);
    }

    public function store(array $params = []): void
    {
        $this->verifyCsrf();

        $name = trim($this->sanitize($this->input('name', '')));
        if (empty($name)) {
            $this->flashError('El nombre es requerido.');
            $this->redirect('/admin/categorias');
        }

        $slug = $this->makeSlug($name);

        try {
            $this->categoryModel->create(['name' => $name, 'slug' => $slug]);
            $this->flashSuccess('Categoría creada correctamente.');
        } catch (\Throwable $e) {
            $this->flashError('Ya existe una categoría con ese nombre.');
        }

        $this->redirect('/admin/categorias');
    }

    public function update(array $params = []): void
    {
        $this->verifyCsrf();

        $id   = (int) ($params['id'] ?? 0);
        $name = trim($this->sanitize($this->input('name', '')));

        if (empty($name)) {
            $this->flashError('El nombre es requerido.');
            $this->redirect('/admin/categorias');
        }

        $slug = $this->makeSlug($name);

        try {
            $this->categoryModel->update($id, ['name' => $name, 'slug' => $slug]);
            $this->flashSuccess('Categoría actualizada.');
        } catch (\Throwable $e) {
            $this->flashError('Ya existe una categoría con ese nombre.');
        }

        $this->redirect('/admin/categorias');
    }

    public function destroy(array $params = []): void
    {
        $this->verifyCsrf();
        $id = (int) ($params['id'] ?? 0);
        $this->categoryModel->delete($id);
        $this->flashSuccess('Categoría eliminada.');
        $this->redirect('/admin/categorias');
    }

    private function makeSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }
}
