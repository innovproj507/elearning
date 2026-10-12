/**
 * LmsApi — Cliente JavaScript para la API RESTful del LMS.
 * Usa fetch() con soporte para JWT almacenado en sessionStorage.
 */
const LmsApi = (() => {
    const BASE = '';  // Misma URL base; cambiar en producción si la API está en otro dominio

    /** Lee el token JWT del storage */
    function getToken() {
        return sessionStorage.getItem('lms_token') || localStorage.getItem('lms_token') || null;
    }

    /** Guarda el token tras login */
    function setToken(token, persistent = false) {
        if (persistent) {
            localStorage.setItem('lms_token', token);
        } else {
            sessionStorage.setItem('lms_token', token);
        }
    }

    /** Elimina el token (logout) */
    function clearToken() {
        sessionStorage.removeItem('lms_token');
        localStorage.removeItem('lms_token');
    }

    /** Cabeceras base para peticiones autenticadas */
    function headers(auth = true) {
        const h = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        if (auth) {
            const token = getToken();
            if (token) h['Authorization'] = `Bearer ${token}`;
        }
        return h;
    }

    /** Realiza una petición genérica a la API */
    async function request(method, endpoint, body = null, auth = true) {
        const opts = { method, headers: headers(auth) };
        if (body) opts.body = JSON.stringify(body);

        const res  = await fetch(BASE + endpoint, opts);
        const data = await res.json();

        // Si el servidor responde 401, limpiar el token
        if (res.status === 401) clearToken();

        return data;
    }

    // ─── Métodos públicos de la API ──────────────────────────────

    return {
        // Auth
        login: async (email, password, remember = false) => {
            const data = await request('POST', '/api/auth/login', { email, password }, false);
            if (data.success && data.data?.token) {
                setToken(data.data.token, remember);
            }
            return data;
        },

        logout: () => {
            clearToken();
        },

        me: () => request('GET', '/api/auth/me'),

        // Cursos
        getCourses: (page = 1, perPage = 12, categoryId = null) => {
            let url = `/api/cursos?page=${page}&per_page=${perPage}`;
            if (categoryId) url += `&categoria=${categoryId}`;
            return request('GET', url, null, false);
        },

        getCourse: (id) => request('GET', `/api/cursos/${id}`, null, false),

        // Inscripción
        enroll: (courseId) => request('POST', '/api/inscribir', { course_id: courseId }),

        myCourses: () => request('GET', '/api/mis-cursos'),

        // Progreso
        getProgress: (courseId) => request('GET', `/api/progreso/${courseId}`),

        // Calificaciones
        getGrades: (quizId) => request('GET', `/api/calificaciones/${quizId}`),

        // Utilidades
        isAuthenticated: () => !!getToken(),
        getToken,
        setToken,
        clearToken,
    };
})();

// ─── Integración con Moodle (LTI / REST) ────────────────────────
/**
 * MoodleBridge — Objeto auxiliar para cuando Moodle consume la API.
 *
 * Moodle puede usar el External Tool (LTI 1.1/1.3) o REST.
 * Estos helpers adaptan el formato de respuesta al esperado por Moodle.
 *
 * Ejemplo de uso en un plugin Moodle (PHP):
 *   $client = new moodle_rest_client($api_url, $jwt_token);
 *   $courses = $client->get('/api/cursos');
 */
const MoodleBridge = {
    /**
     * Retorna los cursos en formato compatible con el plugin
     * "External tool" de Moodle (campo "lti_launch_url" incluido).
     */
    getCourses: async () => {
        const result = await LmsApi.getCourses();
        if (!result.success) return null;

        return result.data.courses.map(c => ({
            id:             c.id,
            title:          c.title,
            description:    c.description,
            url:            c.lti_launch_url ?? `${window.location.origin}/cursos/${c.slug}`,
            teacher:        c.teacher_name,
            category:       c.category_name,
            price:          c.is_free ? 0 : parseFloat(c.price),
        }));
    },

    /**
     * Inscribe a un usuario vía la API e informa a Moodle el resultado.
     */
    enrollUser: async (courseId) => {
        const result = await LmsApi.enroll(courseId);
        return {
            success:       result.success,
            enrollment_id: result.data?.enrollment_id,
            message:       result.message,
        };
    },

    /**
     * Obtiene las calificaciones en formato Gradebook de Moodle.
     */
    getGrades: async (quizId) => {
        const result = await LmsApi.getGrades(quizId);
        if (!result.success) return null;

        const d = result.data;
        return {
            quiz_id:    d.quiz_id,
            quiz_title: d.quiz_title,
            grade:      d.best_score ?? 0,
            grademax:   100,
            passed:     d.passed,
            rawgrade:   d.best_score,
        };
    },
};
