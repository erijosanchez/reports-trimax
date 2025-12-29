<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta de satisfacción - TRIMAX</title>
    <link rel="stylesheet" href="{{ asset('assets/css/survey.css') }}">
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
                <!-- Pregunta 1: Experiencia general -->
                <div class="question-container">
                    <div class="header-question">Encuesta de satisfacción - TRIMAX</div>
                    <div class="question">
                        <div class="question-text">¿Cómo calificarías tu experiencia en TRIMAX?</div>
                        <div class="ratings">
                            <div class="rating-btn muy-feliz" data-question="1" data-value="4">
                                <span class="emoji">😊</span>
                                <div class="rating-label">Muy<br>Feliz</div>
                            </div>
                            <div class="rating-btn feliz" data-question="1" data-value="3">
                                <span class="emoji">🙂</span>
                                <div class="rating-label">Feliz</div>
                            </div>
                            <div class="rating-btn insatisfecho" data-question="1" data-value="2">
                                <span class="emoji">😐</span>
                                <div class="rating-label">Insatisfecho</div>
                            </div>
                            <div class="rating-btn muy-insatisfecho" data-question="1" data-value="1">
                                <span class="emoji">😞</span>
                                <div class="rating-label">Muy<br>Insatisfecho</div>
                            </div>
                        </div>
                        <input type="hidden" name="experience_rating" id="experience_rating" required>
                    </div>
                </div>
                <!-- Pregunta 2: Atención (cambia según tipo) -->
                <div class="question-container">
                    <div class="header-question">Encuesta de satisfacción - TRIMAX</div>
                    <div class="question">
                        <div class="question-text" id="service-quality-question">
                            ¿Cómo evaluarías la atención y el soporte de tu Consultor Trimax?
                        </div>
                        <div class="ratings">
                            <div class="rating-btn muy-feliz" data-question="2" data-value="4">
                                <span class="emoji">😊</span>
                                <div class="rating-label">Muy<br>Feliz</div>
                            </div>
                            <div class="rating-btn feliz" data-question="2" data-value="3">
                                <span class="emoji">🙂</span>
                                <div class="rating-label">Feliz</div>
                            </div>
                            <div class="rating-btn insatisfecho" data-question="2" data-value="2">
                                <span class="emoji">😐</span>
                                <div class="rating-label">Insatisfecho</div>
                            </div>
                            <div class="rating-btn muy-insatisfecho" data-question="2" data-value="1">
                                <span class="emoji">😞</span>
                                <div class="rating-label">Muy<br>Insatisfecho</div>
                            </div>
                        </div>
                        <input type="hidden" name="service_quality_rating" id="service_quality_rating" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tu nombre (opcional)</label>
                    <input type="text" name="client_name" id="client_name" placeholder="Escribe tu nombre aquí...">
                </div>

                <div class="form-group">
                    <label>Cuéntanos brevemente qué podríamos mejorar o qué te gustó más de nuestra atención.</label>
                    <textarea name="comments" id="comments" placeholder="Tu opinión es anónima y nos ayuda a mejorar..."></textarea>
                </div>

                <button type="submit" class="submit-btn" id="submit-btn">ENVIAR</button>

                <div class="trimax-logo">
                    <svg viewBox="0 0 200 50" xmlns="http://www.w3.org/2000/svg">
                        <text x="10" y="35" font-family="Arial, sans-serif" font-size="32" font-weight="900"
                            fill="#1565C0">TRIMAX</text>
                    </svg>
                </div>
            </form>
        </div>

        <div class="success-container" id="success-container">
            <div class="success-emoji">✅</div>
            <h2>¡Gracias por tu opinión!</h2>
            <p>Tu encuesta ha sido enviada exitosamente.</p>
            <div class="trimax-logo">
                <svg viewBox="0 0 200 50" xmlns="http://www.w3.org/2000/svg">
                    <text x="10" y="35" font-family="Arial, sans-serif" font-size="32" font-weight="900"
                        fill="#4CAF50">TRIMAX</text>
                </svg>
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

    <script>
        const token = '{{ $token }}';
        let selectedRatings = {
            experience: null,
            service_quality: null
        };
        let userRole = null;
        let formStarted = false;
        let autoRefreshTimeout = null;

        // ===== SCROLL INDICATOR LOGIC =====
        function checkScrollIndicator() {
            const scrollIndicator = document.getElementById('scroll-indicator');
            const surveyContainer = document.getElementById('survey-container');

            // Solo mostrar si el survey está visible
            if (surveyContainer.style.display === 'none') {
                scrollIndicator.classList.remove('show');
                return;
            }

            // Verificar si hay contenido fuera del viewport
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            // Mostrar si hay más contenido abajo y no estamos al final
            const hasMoreContent = documentHeight > windowHeight + 100; // 100px de margen
            const isAtBottom = (windowHeight + scrollTop) >= documentHeight - 50; // 50px de margen

            if (hasMoreContent && !isAtBottom) {
                scrollIndicator.classList.add('show');
            } else {
                scrollIndicator.classList.remove('show');
            }
        }

        // Smooth scroll al hacer click en la flecha
        document.getElementById('scroll-indicator').addEventListener('click', function() {
            window.scrollTo({
                top: window.pageYOffset + window.innerHeight - 100,
                behavior: 'smooth'
            });
        });

        // Check scroll indicator on scroll and resize
        window.addEventListener('scroll', checkScrollIndicator);
        window.addEventListener('resize', checkScrollIndicator);

        // ===== AUTO REFRESH AFTER SUCCESS =====
        function scheduleAutoRefresh() {
            // Limpiar cualquier timeout previo
            if (autoRefreshTimeout) {
                clearTimeout(autoRefreshTimeout);
            }

            // Programar refresh después de 2 minutos (120000 ms)
            autoRefreshTimeout = setTimeout(() => {
                console.log('Auto-refreshing after 2 minutes...');
                window.location.reload();
            }, 120000); // 2 minutos
        }

        // ===== LOAD SURVEY DATA =====
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                const response = await fetch(`/api/encuesta/${token}`);
                const data = await response.json();

                if (data.success) {
                    userRole = data.data.user.role;

                    document.getElementById('evaluado-name').textContent = data.data.user.name;
                    document.getElementById('evaluado-type').textContent =
                        userRole === 'consultor' ? 'Consultor' :
                        `Sede - ${data.data.user.location || ''}`;

                    const serviceQualityQuestion = document.getElementById('service-quality-question');
                    if (userRole === 'consultor') {
                        serviceQualityQuestion.textContent =
                            '¿Cómo evaluarías la atención y el soporte de tu Consultor Trimax?';
                    } else {
                        serviceQualityQuestion.textContent =
                            '¿Cómo evaluarías la atención y el soporte de tu Sede Trimax?';
                    }

                    document.getElementById('loading').classList.remove('show');
                    document.getElementById('survey-container').style.display = 'block';

                    // Check scroll indicator después de cargar
                    setTimeout(checkScrollIndicator, 500);
                } else {
                    alert('Encuesta no encontrada o inactiva');
                }
            } catch (error) {
                alert('Error al cargar la encuesta');
            }
        });

        // ===== RATING BUTTONS =====
        document.querySelectorAll('.rating-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                formStarted = true;
                const question = this.dataset.question;

                document.querySelectorAll(`.rating-btn[data-question="${question}"]`).forEach(b => {
                    b.classList.remove('active');
                });

                this.classList.add('active');

                if (question === '1') {
                    selectedRatings.experience = this.dataset.value;
                    document.getElementById('experience_rating').value = selectedRatings.experience;
                } else if (question === '2') {
                    selectedRatings.service_quality = this.dataset.value;
                    document.getElementById('service_quality_rating').value = selectedRatings
                        .service_quality;
                }
            });
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

            if (!selectedRatings.experience || !selectedRatings.service_quality) {
                alert('Por favor selecciona una calificación para ambas preguntas');
                return;
            }

            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            const formData = {
                experience_rating: parseInt(selectedRatings.experience),
                service_quality_rating: parseInt(selectedRatings.service_quality),
                client_name: document.getElementById('client_name').value,
                comments: document.getElementById('comments').value
            };

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

                    // Ocultar scroll indicator cuando se muestra success
                    document.getElementById('scroll-indicator').classList.remove('show');

                    // Scroll to top suavemente
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                    // Programar auto-refresh después de 2 minutos
                    scheduleAutoRefresh();
                } else {
                    alert(data.message || 'Error al enviar la encuesta');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'ENVIAR';
                }
            } catch (error) {
                alert('Error de conexión. Intenta nuevamente.');
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
