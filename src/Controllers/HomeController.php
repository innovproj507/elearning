<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Models\Category;

class HomeController extends Controller
{
    public function index(array $params = []): void
    {
        $courseModel   = new Course();
        $categoryModel = new Category();

        $featured   = $courseModel->published(1, 8);
        $categories = $categoryModel->allWithCourseCount();

        $this->view('home.index', [
            'title'      => $_ENV['APP_NAME'],
            'featured'   => $featured['data'] ?? [],
            'categories' => $categories,
        ]);
    }
}
