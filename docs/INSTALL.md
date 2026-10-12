# Guía de Instalación — PAMEL E-Learning LMS

## Requisitos del sistema

| Herramienta | Versión mínima |
|-------------|---------------|
| PHP         | 8.1+          |
| MySQL       | 8.0+ / MariaDB 10.5+ |
| Composer    | 2.x           |
| Apache/Nginx| Cualquier versión con mod_rewrite |

---

## Paso 1 — Clonar / copiar el proyecto

Coloca el proyecto en tu servidor web:

```
c:\laragon\www\elearning\      (Laragon en Windows)
/var/www/html/elearning/       (Apache en Linux)
```

---

## Paso 2 — Instalar dependencias PHP

```bash
cd c:/laragon/www/elearning
composer install
```

Esto instala:
- `firebase/php-jwt` — autenticación JWT para la API
- `vlucas/phpdotenv` — carga de variables de entorno

---

## Paso 3 — Configurar el entorno (.env)

```bash
cp .env.example .env
```

Edita `.env` con tus valores:

```env
APP_NAME="PAMEL E-Learning LMS"
APP_ENV=development
APP_URL=http://elearning.test
APP_DEBUG=true
APP_KEY=genera_una_clave_de_32_caracteres_!!

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=elearning_lms
DB_USER=root
DB_PASS=tu_password_mysql

JWT_SECRET=clave_secreta_muy_larga_para_jwt_minimo_32_chars
JWT_EXPIRY=3600
```

### Generar claves seguras (PHP):
```php
// Ejecuta esto en tu terminal para generar la APP_KEY y JWT_SECRET
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

---

## Paso 4 — Crear la base de datos

### Opción A: Laragon (HeidiSQL / phpMyAdmin)
1. Abre HeidiSQL → Conecta a localhost
2. Crea la base de datos `elearning_lms`
3. Importa `database/schema.sql`

### Opción B: Línea de comandos
```bash
mysql -u root -p < database/schema.sql
```

### Opción C: Script PHP
```bash
php -r "
\$pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
\$pdo->exec(file_get_contents('database/schema.sql'));
echo 'Base de datos creada.' . PHP_EOL;
"
```

---

## Paso 5 — Configurar el virtual host

### Laragon (Windows) — automático
Laragon detecta automáticamente la carpeta. Accede en:
`http://elearning.test`

Asegúrate de tener en `c:\laragon\etc\apache2\sites-enabled\auto.elearning.test.conf`:
```apache
<VirtualHost *:80>
    DocumentRoot "c:/laragon/www/elearning"
    ServerName elearning.test
    <Directory "c:/laragon/www/elearning">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Apache (Linux)
```apache
<VirtualHost *:80>
    ServerName elearning.test
    DocumentRoot /var/www/html/elearning
    <Directory /var/www/html/elearning>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/elearning_error.log
</VirtualHost>
```
Activa: `a2enmod rewrite && systemctl reload apache2`

---

## Paso 6 — Permisos de escritura

```bash
# Linux/Mac
chmod -R 755 public/uploads/
mkdir -p public/uploads/courses public/uploads/files

# Windows (Laragon ya tiene los permisos correctos)
```

---

## Paso 7 — Verificar la instalación

Abre el navegador en `http://elearning.test`.

### Credenciales por defecto (del schema.sql):
| Rol   | Email             | Contraseña   |
|-------|-------------------|--------------|
| Admin | admin@pamel.com   | Admin1234!   |

> **Cambia la contraseña del admin inmediatamente** en `/dashboard/perfil`.

---

## API RESTful — Referencia rápida

### Autenticación

```bash
# Login → obtiene el JWT token
curl -X POST http://elearning.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pamel.com","password":"Admin1234!"}'

# Respuesta:
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1Qi...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": { "id": 1, "name": "Administrador", "role": "admin" }
  }
}
```

### Endpoints disponibles

| Método | Endpoint                        | Auth | Descripción                        |
|--------|---------------------------------|------|------------------------------------|
| POST   | `/api/auth/login`               | No   | Login → devuelve JWT               |
| POST   | `/api/auth/register`            | No   | Registro → devuelve JWT            |
| GET    | `/api/auth/me`                  | JWT  | Perfil del usuario autenticado     |
| GET    | `/api/cursos`                   | No   | Lista de cursos publicados         |
| GET    | `/api/cursos/{id}`              | No   | Detalle de un curso                |
| POST   | `/api/inscribir`                | JWT  | Inscribir al usuario en un curso   |
| GET    | `/api/mis-cursos`               | JWT  | Cursos del usuario con progreso    |
| GET    | `/api/progreso/{courseId}`      | JWT  | Progreso en un curso               |
| GET    | `/api/calificaciones/{quizId}`  | JWT  | Calificaciones de un quiz          |

### Usar el token en peticiones autenticadas

```bash
# Ejemplo: inscribir en un curso
curl -X POST http://elearning.test/api/inscribir \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{"course_id": 1}'
```

---

## Integración con Moodle

### Opción 1: External Tool (LTI 1.1)

En Moodle:
1. Administración → Plugins → External Tool → Manage tools
2. Agregar herramienta:
   - **Tool URL**: `http://elearning.test/lti/launch/{course_id}`
   - **Consumer key**: Tu `APP_KEY` del `.env`
   - **Shared secret**: Tu `JWT_SECRET` del `.env`

### Opción 2: Consumo REST desde plugin Moodle (PHP)

```php
// En tu plugin Moodle (lib.php o similar)
class pamel_api_client {
    private string $base_url;
    private string $token;

    public function __construct(string $base_url, string $jwt_token) {
        $this->base_url = rtrim($base_url, '/');
        $this->token    = $jwt_token;
    }

    public function get_courses(int $page = 1): array {
        return $this->request('GET', "/api/cursos?page={$page}");
    }

    public function enroll_user(int $course_id): array {
        return $this->request('POST', '/api/inscribir', ['course_id' => $course_id]);
    }

    public function get_grades(int $quiz_id): array {
        return $this->request('GET', "/api/calificaciones/{$quiz_id}");
    }

    public function get_progress(int $course_id): array {
        return $this->request('GET', "/api/progreso/{$course_id}");
    }

    private function request(string $method, string $endpoint, array $body = []): array {
        $curl = curl_init($this->base_url . $endpoint);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);
        if (!empty($body)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true) ?? [];
    }
}

// Uso:
$client   = new pamel_api_client('http://elearning.test', $jwt_token);
$courses  = $client->get_courses();
$progress = $client->get_progress($course_id);
```

### Formato de respuesta de calificaciones (compatible Moodle Gradebook)

```json
{
  "success": true,
  "data": {
    "quiz_id": 1,
    "quiz_title": "Evaluación Final",
    "student_id": 42,
    "attempted": true,
    "best_score": 85.5,
    "passed": true,
    "pass_score": 70.0
  }
}
```

---

## Estructura de directorios

```
elearning/
├── .env.example            ← Plantilla de configuración
├── .htaccess               ← Reescritura de URLs
├── index.php               ← Front controller
├── composer.json           ← Dependencias PHP
├── INSTALL.md              ← Este archivo
├── database/
│   └── schema.sql          ← Schema + datos semilla
├── public/
│   ├── .htaccess           ← Protege uploads
│   ├── assets/
│   │   └── js/
│   │       └── api.js      ← Cliente JS para la API
│   └── uploads/            ← Imágenes y archivos (gitignored)
│       ├── courses/
│       └── files/
├── src/
│   ├── Core/               ← Framework mínimo
│   │   ├── Router.php
│   │   ├── Database.php    ← Singleton PDO
│   │   ├── Model.php       ← Active Record base
│   │   ├── Controller.php  ← Controller base
│   │   ├── Session.php
│   │   ├── CSRF.php
│   │   └── JWT.php
│   ├── Models/             ← Modelos de datos
│   │   ├── User.php
│   │   ├── Course.php
│   │   ├── Lesson.php
│   │   ├── Quiz.php
│   │   ├── Question.php
│   │   ├── Enrollment.php
│   │   └── Category.php
│   ├── Controllers/        ← Controladores web
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── CourseController.php
│   │   ├── LessonController.php
│   │   ├── QuizController.php
│   │   └── DashboardController.php
│   ├── Api/                ← Controladores REST
│   │   ├── ApiController.php
│   │   ├── AuthApiController.php
│   │   ├── CourseApiController.php
│   │   ├── EnrollmentApiController.php
│   │   └── ProgressApiController.php
│   └── Middleware/
│       ├── AuthMiddleware.php
│       ├── RoleMiddleware.php
│       └── ApiAuthMiddleware.php
└── views/                  ← Templates PHP + TailwindCSS
    ├── layouts/
    │   ├── main.php        ← Layout público
    │   ├── dashboard.php   ← Layout con sidebar
    │   └── auth.php        ← Layout de autenticación
    ├── home/index.php
    ├── auth/{login,register}.php
    ├── courses/{index,show,create,_card}.php
    ├── lessons/{show,create}.php
    ├── quizzes/{show,create,results}.php
    ├── dashboard/{student,teacher,admin,profile}.php
    └── errors/{403,404}.php
```
