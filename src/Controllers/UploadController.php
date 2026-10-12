<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class UploadController extends Controller
{
    public function image(array $params = []): void
    {
        header('Content-Type: application/json');

        if (empty($_FILES['upload']['name'])) {
            http_response_code(400);
            echo json_encode(['error' => ['message' => 'No se recibió ningún archivo.']]);
            return;
        }

        $file    = $_FILES['upload'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => ['message' => 'Error al recibir el archivo.']]);
            return;
        }

        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['error' => ['message' => 'La imagen supera el límite de 5 MB.']]);
            return;
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (!isset($mimeToExt[$realMime])) {
            http_response_code(400);
            echo json_encode(['error' => ['message' => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.']]);
            return;
        }

        $dir = BASE_PATH . '/public/uploads/content/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = uniqid('img_', true) . '.' . $mimeToExt[$realMime];

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            http_response_code(500);
            echo json_encode(['error' => ['message' => 'No se pudo guardar el archivo.']]);
            return;
        }

        echo json_encode(['url' => '/uploads/content/' . $filename]);
    }
}
