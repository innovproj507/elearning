<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Controller base — renderizado de vistas, redirecciones,
 * manejo de flash messages y protección CSRF.
 */
abstract class Controller
{
    // ─── Vistas ───────────────────────────────────────────────────

    protected function view(string $view, array $data = []): void
    {
        // Extraer variables para la vista
        extract($data, EXTR_SKIP);

        $viewPath = BASE_PATH . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("Vista no encontrada: {$view}");
        }

        // Capturar el contenido de la vista (content)
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Cargar el layout por defecto (layouts/main.php)
        $layout = $data['layout'] ?? 'main';
        $layoutPath = BASE_PATH . '/views/layouts/' . $layout . '.php';

        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    // ─── Redirecciones ────────────────────────────────────────────

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    // ─── Flash messages ───────────────────────────────────────────

    protected function flash(string $key, string $message): void
    {
        Session::set("flash_{$key}", $message);
    }

    protected function flashError(string $message): void
    {
        $this->flash('error', $message);
    }

    protected function flashSuccess(string $message): void
    {
        $this->flash('success', $message);
    }

    // ─── Input ────────────────────────────────────────────────────

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function inputs(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->input($key);
        }
        return $result;
    }

    protected function sanitize(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    protected function purifyHtml(string $html): string
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed',
            'p,br,strong,em,u,s,ul,ol,li,h1,h2,h3,h4,blockquote,pre,code,' .
            'table,thead,tbody,tr,th,td,a[href|target|rel],' .
            'img[src|alt|width|height|style|class],figure[class],figcaption,hr,span[class]'
        );
        $config->set('HTML.AllowedAttributes',
            'a.href,a.target,a.rel,' .
            'img.src,img.alt,img.width,img.height,img.style,img.class,' .
            'figure.class,span.class'
        );
        $config->set('CSS.AllowedProperties', ['max-width', 'width', 'height', 'float', 'margin', 'display', 'text-align']);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'data' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetBlank', true);
        $config->set('Cache.SerializerPath', sys_get_temp_dir());

        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }

    // ─── CSRF ─────────────────────────────────────────────────────

    protected function verifyCsrf(): void
    {
        $token = $_POST['_token'] ?? '';
        if (!CSRF::verify($token)) {
            $this->flashError('Token de seguridad inválido. Inténtalo de nuevo.');
            $this->back();
        }
    }

    // ─── JSON ─────────────────────────────────────────────────────

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ─── Usuario autenticado ──────────────────────────────────────

    protected function authUser(): ?array
    {
        return Session::get('user');
    }

    protected function isAuth(): bool
    {
        return Session::get('user') !== null;
    }
}
