<?php

declare(strict_types=1);

namespace App\Core;

class PluginManager
{
    private static array $hooks = [];
    private static array $loaded = [];

    public function __construct()
    {
        // El PluginManager ahora solo gestiona hooks, no necesita DB local
    }

    // ─── Escanear y cargar plugins activos ───────────────────────

    public function boot(): void
    {
        $active = $this->db->query(
            "SELECT slug FROM plugins WHERE is_active = 1"
        );

        foreach ($active as $row) {
            $this->load($row['slug']);
        }
    }

    public function load(string $slug): bool
    {
        $file = BASE_PATH . "/plugins/{$slug}/plugin.php";
        if (!file_exists($file)) return false;

        require_once $file;
        self::$loaded[] = $slug;
        return true;
    }

    // ─── Escanear plugins instalados en disco ────────────────────

    public function scanDisk(): array
    {
        $dirs = glob(BASE_PATH . '/plugins/*', GLOB_ONLYDIR) ?: [];
        $found = [];

        foreach ($dirs as $dir) {
            $slug     = basename($dir);
            $manifest = $dir . '/manifest.json';
            if (!file_exists($manifest)) continue;

            $meta = json_decode(file_get_contents($manifest), true);
            if (!$meta) continue;

            $found[$slug] = $meta;
        }

        return $found;
    }

    // ─── Sincronizar disco → DB ──────────────────────────────────

    public function sync(): void
    {
        $disk = $this->scanDisk();

        foreach ($disk as $slug => $meta) {
            $exists = $this->db->queryOne(
                "SELECT id FROM plugins WHERE slug = ?", [$slug]
            );

            if (!$exists) {
                $this->db->execute(
                    "INSERT INTO plugins (name, slug, version, description, author)
                     VALUES (?, ?, ?, ?, ?)",
                    [
                        $meta['name']        ?? $slug,
                        $slug,
                        $meta['version']     ?? '1.0.0',
                        $meta['description'] ?? '',
                        $meta['author']      ?? '',
                    ]
                );
            }
        }
    }

    // ─── Activar / desactivar ─────────────────────────────────────

    public function activate(int $id): bool
    {
        $plugin = $this->db->queryOne("SELECT slug FROM plugins WHERE id = ?", [$id]);
        if (!$plugin) return false;

        if (!$this->load($plugin['slug'])) return false;

        $this->db->execute("UPDATE plugins SET is_active = 1 WHERE id = ?", [$id]);
        return true;
    }

    public function deactivate(int $id): void
    {
        $this->db->execute("UPDATE plugins SET is_active = 0 WHERE id = ?", [$id]);
    }

    public function all(): array
    {
        return $this->db->query("SELECT * FROM plugins ORDER BY name ASC");
    }

    // ─── Sistema de hooks ────────────────────────────────────────

    public static function addHook(string $event, callable $callback, int $priority = 10): void
    {
        self::$hooks[$event][] = ['fn' => $callback, 'priority' => $priority];
    }

    public static function fire(string $event, mixed $payload = null): mixed
    {
        if (empty(self::$hooks[$event])) return $payload;

        $callbacks = self::$hooks[$event];
        usort($callbacks, fn($a, $b) => $a['priority'] <=> $b['priority']);

        foreach ($callbacks as $cb) {
            $result  = ($cb['fn'])($payload);
            $payload = $result ?? $payload;
        }

        return $payload;
    }
}
