<?php

declare(strict_types=1);

/**
 * Helpers globales del LMS.
 *
 * Se carga desde public/index.php justo después de poblar $_ENV.
 */

if (!function_exists('cms_url')) {
    /**
     * Origen del CMS principal (sin barra final).
     *
     * Se deriva de API_BASE_URL quitando el sufijo /api/vN, de modo que no
     * haya dos valores que mantener sincronizados entre entornos.
     */
    function cms_url(): string
    {
        static $base = null;

        if ($base === null) {
            $configured = $_ENV['CMS_URL'] ?? '';
            if ($configured === '') {
                $configured = preg_replace('#/api/v\d+/?$#', '', $_ENV['API_BASE_URL'] ?? '');
            }
            $base = rtrim((string) $configured, '/');
        }

        return $base;
    }
}

if (!function_exists('cms_asset')) {
    /**
     * Convierte una ruta de archivo del CMS en una URL absoluta.
     *
     * El CMS guarda las imágenes de curso como rutas relativas al host
     * (`/uploads/courses/x.jpg`). Servidas tal cual desde el subdominio del
     * LMS el navegador las resuelve contra elearning.pamel.edu.pa, donde no
     * existen, y devuelven 404.
     *
     * @param string|null $path Ruta almacenada en el CMS.
     * @return string URL absoluta, o cadena vacía si no hay imagen.
     */
    function cms_asset(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return '';
        }

        // Ya es absoluta (http://, https://, //cdn..., data:)
        if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }

        return cms_url() . '/' . ltrim($path, '/');
    }
}
