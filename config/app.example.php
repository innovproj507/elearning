<?php

/**
 * Configuración de la aplicación elearning.
 * Este archivo reemplaza al .env — no contiene credenciales de base de datos
 * porque el LMS es un consumidor puro del API del CMS.
 *
 * Para producción: cambia JWT_SECRET, MAIL_USERNAME y MAIL_PASSWORD.
 */

return [
    // ── Entorno (detección automática por hostname) ───────────────────────
    'APP_NAME'  => 'PAMEL E-Learning LMS',
    'APP_ENV'   => strpos($_SERVER['HTTP_HOST'] ?? '', 'pamel.edu.pa') !== false ? 'production' : 'local',
    'APP_URL'   => strpos($_SERVER['HTTP_HOST'] ?? '', 'pamel.edu.pa') !== false
                    ? 'https://elearning.pamel.edu.pa'
                    : 'http://elearning.test',
    'APP_DEBUG' => strpos($_SERVER['HTTP_HOST'] ?? '', 'pamel.edu.pa') !== false ? 'false' : 'true',

    // ── CMS API ───────────────────────────────────────────────────────────
    // Se selecciona automáticamente según el entorno
    'API_BASE_URL' => strpos($_SERVER['HTTP_HOST'] ?? '', 'pamel.edu.pa') !== false
                        ? 'https://pamel.edu.pa/api/v1'
                        : 'http://pamel.test/api/v1',

    // Origen del CMS, usado para resolver las imágenes que éste almacena
    // como rutas relativas al host (/uploads/...).
    'CMS_URL' => strpos($_SERVER['HTTP_HOST'] ?? '', 'pamel.edu.pa') !== false
                        ? 'https://pamel.edu.pa'
                        : 'http://pamel.test',

    // ── JWT (API interna del LMS) ─────────────────────────────────────────
    'JWT_SECRET' => '',
    'JWT_EXPIRY' => '3600',

    // ── Sesión ────────────────────────────────────────────────────────────
    'SESSION_NAME'     => 'elearning_session',
    'SESSION_LIFETIME' => '7200',

    // ── Uploads ───────────────────────────────────────────────────────────
    'UPLOAD_MAX_SIZE' => '10485760',
    'UPLOAD_PATH'     => 'public/uploads',

    // ── Correo SMTP ───────────────────────────────────────────────────────
    'MAIL_DRIVER'       => 'smtp',
    'MAIL_HOST'         => 'smtp.gmail.com',
    'MAIL_PORT'         => '587',
    'MAIL_USERNAME'     => '',
    'MAIL_PASSWORD'     => '',
    'MAIL_ENCRYPTION'   => 'tls',
    'MAIL_FROM_ADDRESS' => 'noreply@pamel.edu.pa',
    'MAIL_FROM_NAME'    => 'PAMEL LMS',
];
