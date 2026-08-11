<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta de satisfacción - TRIMAX</title>
    <link rel="stylesheet" href="{{ asset('assets/css/survey.css') }}?v={{ filemtime(public_path('assets/css/survey.css')) }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/fv.png') }}">
</head>

<body>
    <div class="container">
        <div class="loading show" id="loading">
            <div class="spinner"></div>
            <p>Cargando encuesta...</p>
        </div>

        <div id="survey-container" style="display: none;">

            <div class="evaluado-info">
                <div class="name" id="evaluado-name"></div>
                <div class="type" id="evaluado-type"></div>
            </div>

            <form id="survey-form">
                <!-- Datos del cliente -->
                <div class="form-group">
                    <label>Razón social <span class="required-mark">*</span></label>
                    <input type="text" name="client_name" id="client_name" required
                        placeholder="Escribe la razón social...">
                </div>

                <div class="form-group sede-group">
                    <label>Sede con la que trabaja</label>
                    <div class="select-wrapper">
                        <select name="sede_id" id="sede_id">
                            <option value="">General (sin sede específica)</option>
                        </select>
                    </div>
                </div>

                <!-- Pregunta 1: Experiencia general -->
                <div class="question-container">
                    <div class="header-question">Encuesta de satisfacción - TRIMAX</div>
                    <div class="question">
                        <div class="question-text">¿Cómo calificarías tu experiencia en TRIMAX?</div>
                        <div class="ratings">
                            <div class="rating-btn muy-feliz" data-question="1" data-value="4">
                                <img class="emoji" src="{{ asset('assets/img/caras/muysatisfecho.png') }}" alt="">
                                <div class="rating-label">Muy<br>Buena</div>
                            </div>
                            <div class="rating-btn feliz" data-question="1" data-value="3">
                                <img class="emoji" src="{{ asset('assets/img/caras/satisfecho.png') }}" alt="">
                                <div class="rating-label">Buena</div>
                            </div>
                            <div class="rating-btn insatisfecho" data-question="1" data-value="2">
                                <img class="emoji" src="{{ asset('assets/img/caras/insatisfecho.png') }}" alt="">
                                <div class="rating-label">Insatisfecho</div>
                            </div>
                            <div class="rating-btn muy-insatisfecho" data-question="1" data-value="1">
                                <img class="emoji" src="{{ asset('assets/img/caras/muyinsatisfecho.png') }}" alt="">
                                <div class="rating-label">Muy<br>Insatisfecho</div>
                            </div>
                        </div>
                        <input type="hidden" name="experience_rating" id="experience_rating" required>
                    </div>
                </div>

                <!-- Pregunta 2: Satisfacción con la sede -->
                <div class="question-container">
                    <div class="header-question">Encuesta de satisfacción - TRIMAX</div>
                    <div class="question">
                        <div class="question-text">¿Cómo evaluarías la atención y el soporte de tu Sede TRIMAX?</div>
                        <div class="ratings">
                            <div class="rating-btn muy-feliz" data-question="2" data-value="4">
                                <img class="emoji" src="{{ asset('assets/img/caras/muysatisfecho.png') }}" alt="">
                                <div class="rating-label">Muy<br>Buena</div>
                            </div>
                            <div class="rating-btn feliz" data-question="2" data-value="3">
                                <img class="emoji" src="{{ asset('assets/img/caras/satisfecho.png') }}" alt="">
                                <div class="rating-label">Buena</div>
                            </div>
                            <div class="rating-btn insatisfecho" data-question="2" data-value="2">
                                <img class="emoji" src="{{ asset('assets/img/caras/insatisfecho.png') }}" alt="">
                                <div class="rating-label">Insatisfecho</div>
                            </div>
                            <div class="rating-btn muy-insatisfecho" data-question="2" data-value="1">
                                <img class="emoji" src="{{ asset('assets/img/caras/muyinsatisfecho.png') }}" alt="">
                                <div class="rating-label">Muy<br>Insatisfecho</div>
                            </div>
                        </div>
                        <input type="hidden" name="sede_rating" id="sede_rating" required>
                    </div>
                </div>

                <!-- Filtro: ¿tiene consultor? -->
                <div class="form-group">
                    <label>¿Actualmente es atendido por un consultor comercial de Trimax?</label>
                    <div class="filter-toggle">
                        <div class="filter-btn" id="tiene-consultor-si" data-value="1">Sí</div>
                        <div class="filter-btn" id="tiene-consultor-no" data-value="0">No</div>
                    </div>
                    <input type="hidden" name="tiene_consultor" id="tiene_consultor" required>
                </div>

                <!-- Rama consultor: solo visible si tiene_consultor = Sí -->
                <div class="form-group" id="consultor-select-group" style="display: none;">
                    <label>¿Cuál de los consultores comerciales de Trimax lo atiende?</label>
                    <div class="select-wrapper">
                        <select name="consultor_id" id="consultor_id">
                            <option value="">Selecciona un consultor...</option>
                            <option value="no_sabe">No sabe / No tiene consultor</option>
                        </select>
                    </div>
                </div>

                <div class="question-container" id="consultor-rating-question" style="display: none;">
                    <div class="header-question">Encuesta de satisfacción - TRIMAX</div>
                    <div class="question">
                        <div class="question-text">¿Cómo evaluarías la atención y el soporte de tu Consultor
                            Trimax?</div>
                        <div class="ratings">
                            <div class="rating-btn muy-feliz" data-question="3" data-value="4">
                                <img class="emoji" src="{{ asset('assets/img/caras/muysatisfecho.png') }}" alt="">
                                <div class="rating-label">Muy<br>Buena</div>
                            </div>
                            <div class="rating-btn feliz" data-question="3" data-value="3">
                                <img class="emoji" src="{{ asset('assets/img/caras/satisfecho.png') }}" alt="">
                                <div class="rating-label">Buena</div>
                            </div>
                            <div class="rating-btn insatisfecho" data-question="3" data-value="2">
                                <img class="emoji" src="{{ asset('assets/img/caras/insatisfecho.png') }}" alt="">
                                <div class="rating-label">Insatisfecho</div>
                            </div>
                            <div class="rating-btn muy-insatisfecho" data-question="3" data-value="1">
                                <img class="emoji" src="{{ asset('assets/img/caras/muyinsatisfecho.png') }}" alt="">
                                <div class="rating-label">Muy<br>Insatisfecho</div>
                            </div>
                        </div>
                        <input type="hidden" name="consultor_rating" id="consultor_rating">
                    </div>
                </div>

                <!-- Cierre: productos — oculto hasta responder el filtro de consultor.
                     Con "No" aparece de inmediato; con "Sí" aparece debajo del bloque
                     de consultor (ambas ramas convergen aquí). -->
                <div class="question-container" id="productos-question" style="display: none;">
                    <div class="header-question">Encuesta de satisfacción - TRIMAX</div>
                    <div class="question">
                        <div class="question-text">¿Cómo calificarías los productos Trimax?</div>
                        <div class="ratings">
                            <div class="rating-btn muy-feliz" data-question="4" data-value="4">
                                <img class="emoji" src="{{ asset('assets/img/caras/muysatisfecho.png') }}" alt="">
                                <div class="rating-label">Muy<br>Buenos</div>
                            </div>
                            <div class="rating-btn feliz" data-question="4" data-value="3">
                                <img class="emoji" src="{{ asset('assets/img/caras/satisfecho.png') }}" alt="">
                                <div class="rating-label">Buenos</div>
                            </div>
                            <div class="rating-btn insatisfecho" data-question="4" data-value="2">
                                <img class="emoji" src="{{ asset('assets/img/caras/insatisfecho.png') }}" alt="">
                                <div class="rating-label">Insatisfecho</div>
                            </div>
                            <div class="rating-btn muy-insatisfecho" data-question="4" data-value="1">
                                <img class="emoji" src="{{ asset('assets/img/caras/muyinsatisfecho.png') }}" alt="">
                                <div class="rating-label">Muy<br>Insatisfecho</div>
                            </div>
                        </div>
                        <input type="hidden" name="productos_rating" id="productos_rating" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Cuéntanos brevemente qué podríamos mejorar o qué te gustó más de nuestra atención.</label>
                    <textarea name="comments" id="comments" placeholder="Tu opinión es anónima y nos ayuda a mejorar..."></textarea>
                </div>

                <button type="submit" class="submit-btn" id="submit-btn">ENVIAR</button>

                <div class="trimax-logo">
                    <img src="{{ asset('assets/img/ltr.png') }}" alt="TRIMAX">
                </div>
            </form>
        </div>

        <div class="success-container" id="success-container">
            <div class="success-emoji">✅</div>
            <h2>¡Gracias por tu tiempo!</h2>
            <p>Tu opinión nos ayuda a mejorar cada día.<br>Equipo Laboratorio Trimax Óptico</p>
            <div class="trimax-logo">
                <img src="{{ asset('assets/img/ltr.png') }}" alt="TRIMAX">
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator" id="scroll-indicator">
        <div class="scroll-text">Desliza hacia abajo</div>
        <div class="scroll-arrow">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z" />
            </svg>
        </div>
    </div>

    <script src="{{ asset('assets/vendors/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/app/notify.js') }}"></script>
    <script>
        const token = '{{ $token }}';
        let selectedRatings = {
            1: null, // experience
            2: null, // sede
            3: null, // consultor
            4: null, // productos
        };
        let tieneConsultorValue = null; // '1' | '0' | null
        let consultores = []; // [{id, name, sede_ids: [...]}]
        let formStarted = false;
        let autoRefreshTimeout = null;

        // ===== SCROLL INDICATOR LOGIC =====
        function checkScrollIndicator() {
            const scrollIndicator = document.getElementById('scroll-indicator');
            const surveyContainer = document.getElementById('survey-container');

            if (surveyContainer.style.display === 'none') {
                scrollIndicator.classList.remove('show');
                return;
            }

            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            const hasMoreContent = documentHeight > windowHeight + 100;
            const isAtBottom = (windowHeight + scrollTop) >= documentHeight - 50;

            if (hasMoreContent && !isAtBottom) {
                scrollIndicator.classList.add('show');
            } else {
                scrollIndicator.classList.remove('show');
            }
        }

        document.getElementById('scroll-indicator').addEventListener('click', function() {
            window.scrollTo({
                top: window.pageYOffset + window.innerHeight - 100,
                behavior: 'smooth'
            });
        });

        window.addEventListener('scroll', checkScrollIndicator);
        window.addEventListener('resize', checkScrollIndicator);

        // ===== AUTO REFRESH AFTER SUCCESS =====
        function scheduleAutoRefresh() {
            if (autoRefreshTimeout) {
                clearTimeout(autoRefreshTimeout);
            }
            autoRefreshTimeout = setTimeout(() => {
                window.location.reload();
            }, 120000);
        }

        // ===== CONSULTOR DROPDOWN: filtrado por la sede elegida =====
        function renderConsultores() {
            const select = document.getElementById('consultor_id');
            const sedeId = document.getElementById('sede_id').value;
            const valorPrevio = select.value;

            select.innerHTML = '';
            const optDefault = document.createElement('option');
            optDefault.value = '';
            optDefault.textContent = 'Selecciona un consultor...';
            select.appendChild(optDefault);

            const filtrados = sedeId ?
                consultores.filter(c => (c.sede_ids || []).includes(parseInt(sedeId))) :
                consultores;

            filtrados.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                select.appendChild(opt);
            });

            const optNoSabe = document.createElement('option');
            optNoSabe.value = 'no_sabe';
            optNoSabe.textContent = 'No sabe / No tiene consultor';
            select.appendChild(optNoSabe);

            if ([...select.options].some(o => o.value === valorPrevio)) {
                select.value = valorPrevio;
            }
        }

        // ===== RAMA CONSULTOR: mostrar/ocultar según Sí/No y selección =====
        // "Productos" queda oculto hasta responder el filtro: con "No" aparece
        // de inmediato; con "Sí" aparece igual, debajo del bloque de consultor.
        function actualizarRamaConsultor() {
            const grupoSelect = document.getElementById('consultor-select-group');
            const preguntaRating = document.getElementById('consultor-rating-question');
            const productosQuestion = document.getElementById('productos-question');

            if (tieneConsultorValue !== '1') {
                grupoSelect.style.display = 'none';
                preguntaRating.style.display = 'none';
                document.getElementById('consultor_id').value = '';
                document.getElementById('consultor_rating').value = '';
                selectedRatings[3] = null;
                document.querySelectorAll('.rating-btn[data-question="3"]').forEach(b => b.classList.remove(
                    'active'));
                productosQuestion.style.display = tieneConsultorValue === '0' ? 'block' : 'none';
                return;
            }

            grupoSelect.style.display = 'block';
            productosQuestion.style.display = 'block';

            const consultorSeleccionado = document.getElementById('consultor_id').value;
            const esConsultorEspecifico = consultorSeleccionado && consultorSeleccionado !== 'no_sabe';
            preguntaRating.style.display = esConsultorEspecifico ? 'block' : 'none';

            if (!esConsultorEspecifico) {
                document.getElementById('consultor_rating').value = '';
                selectedRatings[3] = null;
                document.querySelectorAll('.rating-btn[data-question="3"]').forEach(b => b.classList.remove(
                    'active'));
            }
        }

        // ===== LOAD SURVEY DATA =====
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch(`/api/encuesta/${token}`);
                const data = await response.json();

                if (data.success) {
                    document.getElementById('evaluado-name').textContent = data.data.user.name;

                    const tipoTexto = {
                        consultor: 'Consultor',
                        sede: `Sede - ${data.data.user.location || ''}`,
                        trimax: 'TRIMAX - General',
                    };
                    document.getElementById('evaluado-type').textContent = tipoTexto[data.data.user.role] ||
                        '';

                    // Sede: siempre visible, precargada según el link cuando aplica
                    const sedeSelect = document.getElementById('sede_id');
                    (data.data.sedes || []).forEach(sede => {
                        const opt = document.createElement('option');
                        opt.value = sede.id;
                        opt.textContent = sede.name;
                        sedeSelect.appendChild(opt);
                    });
                    if (data.data.sede_preseleccionada_id) {
                        sedeSelect.value = String(data.data.sede_preseleccionada_id);
                    }
                    sedeSelect.addEventListener('change', () => {
                        formStarted = true;
                        renderConsultores();
                    });

                    consultores = data.data.consultores || [];
                    renderConsultores();

                    document.getElementById('loading').classList.remove('show');
                    document.getElementById('survey-container').style.display = 'block';

                    setTimeout(checkScrollIndicator, 500);
                } else {
                    Notify.error('Encuesta no encontrada o inactiva');
                }
            } catch (error) {
                Notify.error('Error al cargar la encuesta');
            }
        });

        // ===== RATING BUTTONS (preguntas 1, 2, 3, 4) =====
        document.querySelectorAll('.rating-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                formStarted = true;
                const question = this.dataset.question;

                document.querySelectorAll(`.rating-btn[data-question="${question}"]`).forEach(b => {
                    b.classList.remove('active');
                });
                this.classList.add('active');

                selectedRatings[question] = this.dataset.value;

                const fieldByQuestion = {
                    1: 'experience_rating',
                    2: 'sede_rating',
                    3: 'consultor_rating',
                    4: 'productos_rating',
                };
                document.getElementById(fieldByQuestion[question]).value = this.dataset.value;
            });
        });

        // ===== FILTRO SÍ/NO =====
        document.getElementById('tiene-consultor-si').addEventListener('click', function() {
            formStarted = true;
            tieneConsultorValue = '1';
            document.getElementById('tiene_consultor').value = '1';
            document.getElementById('tiene-consultor-si').classList.add('active');
            document.getElementById('tiene-consultor-no').classList.remove('active');
            actualizarRamaConsultor();
        });

        document.getElementById('tiene-consultor-no').addEventListener('click', function() {
            formStarted = true;
            tieneConsultorValue = '0';
            document.getElementById('tiene_consultor').value = '0';
            document.getElementById('tiene-consultor-no').classList.add('active');
            document.getElementById('tiene-consultor-si').classList.remove('active');
            actualizarRamaConsultor();
        });

        document.getElementById('consultor_id').addEventListener('change', function() {
            formStarted = true;
            actualizarRamaConsultor();
        });

        // ===== FORM INPUT TRACKING =====
        document.getElementById('client_name').addEventListener('input', function() {
            if (this.value.trim().length > 0) {
                formStarted = true;
            }
        });

        document.getElementById('comments').addEventListener('input', function() {
            if (this.value.trim().length > 0) {
                formStarted = true;
            }
        });

        // ===== FORM SUBMIT =====
        document.getElementById('survey-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!document.getElementById('client_name').value.trim()) {
                Notify.warning('Ingresa la razón social');
                return;
            }
            if (!selectedRatings[1] || !selectedRatings[2]) {
                Notify.warning('Por favor completa las calificaciones de arriba');
                return;
            }
            if (tieneConsultorValue === null) {
                Notify.warning('Indica si eres atendido por un consultor comercial');
                return;
            }

            const consultorSelect = document.getElementById('consultor_id');
            const consultorValue = consultorSelect.value;
            const esConsultorEspecifico = tieneConsultorValue === '1' && consultorValue && consultorValue !==
                'no_sabe';

            if (tieneConsultorValue === '1' && !consultorValue) {
                Notify.warning('Selecciona un consultor o marca "No sabe / No tiene consultor"');
                return;
            }
            if (esConsultorEspecifico && !selectedRatings[3]) {
                Notify.warning('Falta calificar la atención del consultor');
                return;
            }
            if (!selectedRatings[4]) {
                Notify.warning('Falta calificar los productos Trimax');
                return;
            }

            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            const formData = {
                client_name: document.getElementById('client_name').value.trim(),
                experience_rating: parseInt(selectedRatings[1]),
                sede_rating: parseInt(selectedRatings[2]),
                tiene_consultor: tieneConsultorValue === '1',
                productos_rating: parseInt(selectedRatings[4]),
                comments: document.getElementById('comments').value,
            };

            const sedeValue = document.getElementById('sede_id').value;
            if (sedeValue) {
                formData.sede_id = parseInt(sedeValue);
            }

            if (tieneConsultorValue === '1') {
                if (consultorValue === 'no_sabe') {
                    formData.consultor_desconocido = true;
                } else {
                    formData.consultor_id = parseInt(consultorValue);
                    formData.consultor_rating = parseInt(selectedRatings[3]);
                }
            }

            try {
                const response = await fetch(`/api/encuesta/${token}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    formStarted = false;
                    document.getElementById('survey-container').style.display = 'none';
                    document.getElementById('success-container').classList.add('show');
                    document.getElementById('scroll-indicator').classList.remove('show');

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    scheduleAutoRefresh();
                } else {
                    Notify.error(data.message || 'Error al enviar la encuesta');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'ENVIAR';
                }
            } catch (error) {
                Notify.error('Error de conexión. Intenta nuevamente.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'ENVIAR';
            }
        });

        // ===== PREVENT ACCIDENTAL EXIT =====
        window.addEventListener('beforeunload', (e) => {
            if (formStarted && document.getElementById('survey-container').style.display !== 'none') {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>

</html>
