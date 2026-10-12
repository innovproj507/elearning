# Registro de Cambios: Migración a Vue 3 SPA (LMS)

Este documento registrará los pasos, cambios, decisiones y errores encontrados durante la refactorización del proyecto LMS (e-learning) a una Single Page Application (SPA) con Vue 3 y TailwindCSS.

## Fase 1: Inicialización y Pivot

### Cambios
- [x] Creación de este archivo de registro (`REFACTOR_LOG.md`).
- [x] Respaldo del antiguo código PHP en la carpeta `old_backend/`.
- [x] Inicialización fallida/cancelada de Vue 3 SPA (Vite).
- [x] **PIVOT:** Restauración del sistema base en PHP por preferencia del usuario.
- [x] Re-activación del micro-framework PHP original para actuar como "Consumer" del API.

### Errores / Notas
- Se intentó migrar a Vue 3, pero se decidió volver a PHP para mantener la facilidad de desarrollo del usuario.
- El sistema ahora funcionará como un frontend desacoplado escrito en PHP que consume el API del CMS.

