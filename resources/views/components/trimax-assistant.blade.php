{{-- resources/views/components/trimax-assistant.blade.php --}}
<div id="trimaxAssistant">
    <!-- Botón flotante -->
    <button id="assistantToggle" class="assistant-toggle" type="button">
        <i class="mdi mdi-help-circle assistant-icon"></i>
        <i class="mdi mdi-close assistant-close-icon"></i>
        <span class="assistant-badge" id="assistantBadge">¿Necesitas ayuda?</span>
    </button>

    <!-- Ventana del asistente -->
    <div id="assistantWindow" class="assistant-window">
        <!-- Header -->
        <div class="assistant-header">
            <div class="d-flex align-items-center">
                <div class="assistant-avatar">
                    <i class="mdi mdi-robot"></i>
                </div>
                <div>
                    <h6 class="mb-0">Asistente Trimax</h6>
                    <small class="text-white-50">Estoy aquí para ayudarte 😊</small>
                </div>
            </div>
            <button class="btn-close-assistant" type="button">
                <i class="mdi mdi-close"></i>
            </button>
        </div>

        <!-- Mensajes de bienvenida y accesos rápidos -->
        <div id="assistantContent" class="assistant-content">
            <!-- Mensaje de bienvenida -->
            <div class="welcome-message">
                <div class="bot-message">
                    <div class="message-avatar">
                        <i class="mdi mdi-robot"></i>
                    </div>
                    <div class="message-content">
                        <p>¡Hola! 👋 Soy tu asistente virtual de Trimax.</p>
                        <p>Estoy aquí para ayudarte con cualquier duda del sistema. ¿En qué puedo ayudarte?</p>
                    </div>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div class="quick-actions">
                <h6 class="quick-actions-title">Preguntas frecuentes:</h6>
                <div class="quick-action-buttons">
                    <button class="quick-action-btn" type="button">
                        <i class="mdi mdi-account-plus"></i> Crear usuario
                    </button>
                    <button class="quick-action-btn" type="button">
                        <i class="mdi mdi-qrcode"></i> Generar QR
                    </button>
                    <button class="quick-action-btn" type="button">
                        <i class="mdi mdi-office-building"></i> Asignar sedes
                    </button>
                    <button class="quick-action-btn" type="button">
                        <i class="mdi mdi-file-document"></i> Ver encuestas
                    </button>
                    <button class="quick-action-btn" type="button">
                        <i class="mdi mdi-refresh"></i> Regenerar link
                    </button>
                    <button class="quick-action-btn" type="button">
                        <i class="mdi mdi-power"></i> Activar/Desactivar
                    </button>
                </div>
            </div>

            <!-- Área de chat -->
            <div id="chatMessages" class="chat-messages"></div>
        </div>

        <!-- Input de búsqueda -->
        <div class="assistant-input">
            <input type="text" id="userQuestion" class="form-control" placeholder="Escribe tu pregunta aquí...">
            <button class="btn-send" type="button">
                <i class="mdi mdi-send"></i>
            </button>
        </div>
    </div>
</div>

<style>
    /* Botón flotante */
    .assistant-toggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg,#2e4bce 0%, #102581 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        cursor: pointer;
        z-index: 9999;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .assistant-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
    }

    .assistant-icon,
    .assistant-close-icon {
        color: white;
        font-size: 28px;
        transition: all 0.3s ease;
    }

    .assistant-close-icon {
        display: none;
    }

    .assistant-toggle.active .assistant-icon {
        display: none;
    }

    .assistant-toggle.active .assistant-close-icon {
        display: block;
    }

    .assistant-badge {
        position: absolute;
        bottom: 60px;
        right: 0;
        background: white;
        color: #6366f1;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        white-space: nowrap;
        opacity: 0;
        animation: fadeInOut 4s ease-in-out infinite;
    }

    @keyframes fadeInOut {

        0%,
        100% {
            opacity: 0;
            transform: translateX(10px);
        }

        50% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Ventana del asistente */
    .assistant-window {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 400px;
        height: 600px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        display: none;
        flex-direction: column;
        z-index: 9998;
        overflow: hidden;
    }

    .assistant-window.active {
        display: flex;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Header */
    .assistant-header {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .assistant-avatar {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 24px;
    }

    .btn-close-assistant {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-close-assistant:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Contenido */
    .assistant-content {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
    }

    .welcome-message {
        margin-bottom: 20px;
    }

    .bot-message {
        display: flex;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .message-avatar {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .message-content {
        background: white;
        padding: 12px 16px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        max-width: 80%;
    }

    .message-content p {
        margin: 0;
        margin-bottom: 8px;
        line-height: 1.5;
    }

    .message-content p:last-child {
        margin-bottom: 0;
    }

    .user-message {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 16px;
    }

    .user-message .message-content {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
    }

    /* Acciones rápidas */
    .quick-actions {
        margin-bottom: 20px;
    }

    .quick-actions-title {
        font-size: 13px;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 12px;
    }

    .quick-action-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .quick-action-btn {
        background: white;
        border: 1px solid #e5e7eb;
        padding: 12px;
        border-radius: 8px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
        color: #374151;
    }

    .quick-action-btn:hover {
        background: #f3f4f6;
        border-color: #6366f1;
        color: #6366f1;
        transform: translateY(-2px);
    }

    .quick-action-btn i {
        font-size: 16px;
    }

    /* Chat messages */
    .chat-messages {
        margin-top: 20px;
    }

    /* Input */
    .assistant-input {
        padding: 16px;
        background: white;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 8px;
    }

    .assistant-input input {
        flex: 1;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 10px 16px;
        font-size: 14px;
    }

    .assistant-input input:focus {
        outline: none;
        border-color: #6366f1;
    }

    .btn-send {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        color: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-send:hover {
        transform: scale(1.1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .assistant-window {
            width: calc(100vw - 40px);
            right: 20px;
            left: 20px;
            height: 500px;
        }

        .quick-action-buttons {
            grid-template-columns: 1fr;
        }
    }

    /* Scrollbar personalizado */
    .assistant-content::-webkit-scrollbar {
        width: 6px;
    }

    .assistant-content::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .assistant-content::-webkit-scrollbar-thumb {
        background: #6366f1;
        border-radius: 3px;
    }

    .assistant-content::-webkit-scrollbar-thumb:hover {
        background: #4f46e5;
    }

    /* Animación de escritura */
    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 12px 16px;
        background: white;
        border-radius: 12px;
        width: fit-content;
    }

    .typing-dot {
        width: 8px;
        height: 8px;
        background: #6366f1;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }

    .typing-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {

        0%,
        60%,
        100% {
            transform: translateY(0);
        }

        30% {
            transform: translateY(-10px);
        }
    }
</style>

<script>
    // Esperar a que el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🤖 Asistente Trimax cargado');

        // Base de conocimiento del sistema
        const knowledgeBase = {
            "¿cómo creo un nuevo usuario?": {
                title: "Crear nuevo usuario",
                steps: [
                    "Ve al menú 'Gestión de Usuarios Marketing'",
                    "Haz clic en el botón verde 'Nuevo Usuario' en la esquina superior derecha",
                    "Completa el formulario con: Nombre, Tipo (Consultor o Sede) y Ubicación (si es sede)",
                    "Haz clic en 'Guardar'",
                    "¡Listo! Se generará automáticamente el link de encuesta único 🎉"
                ],
                tip: "💡 Tip: El link de encuesta se genera automáticamente y es único para cada usuario."
            },
            "¿cómo genero un código qr?": {
                title: "Generar código QR",
                steps: [
                    "En la tabla de usuarios, busca al usuario para el que quieres generar el QR",
                    "Haz clic en el botón gris con el ícono de QR",
                    "Se abrirá un modal mostrando el código QR",
                    "Puedes descargarlo haciendo clic en 'Descargar Código QR'"
                ],
                tip: "💡 Tip: El código QR contiene el link directo a la encuesta del usuario."
            },
            "¿cómo asigno sedes a un consultor?": {
                title: "Asignar sedes a consultor",
                steps: [
                    "En la tabla de usuarios, busca al consultor",
                    "Haz clic en el botón azul con ícono de tienda",
                    "Selecciona las sedes que quieres asignar (puedes seleccionar múltiples)",
                    "Haz clic en 'Guardar asignación'",
                    "Las sedes quedarán vinculadas al consultor"
                ],
                tip: "💡 Tip: Un consultor puede tener múltiples sedes asignadas, y una sede puede tener varios consultores."
            },
            "¿cómo veo las encuestas?": {
                title: "Ver encuestas",
                steps: [
                    "Haz clic en el botón 'Ver Detalles' (ícono de ojo) del usuario",
                    "En la página de detalles verás todas las estadísticas",
                    "Scroll hacia abajo para ver la lista de encuestas recientes",
                    "Puedes ver: calificación, comentarios y fecha de cada encuesta"
                ],
                tip: "💡 Tip: También puedes ver gráficos de tendencia de los últimos 30 días."
            },
            "¿cómo regenero el link de encuesta?": {
                title: "Regenerar link de encuesta",
                steps: [
                    "En la tabla de usuarios, busca el usuario",
                    "Haz clic en el botón con tres puntos (···) para abrir el menú",
                    "Selecciona 'Regenerar Link'",
                    "Confirma la acción (el link anterior dejará de funcionar)",
                    "Se generará un nuevo link único"
                ],
                tip: "⚠️ Importante: El link anterior dejará de funcionar inmediatamente."
            },
            "¿cómo activo o desactivo un usuario?": {
                title: "Activar/Desactivar usuario",
                steps: [
                    "En la tabla de usuarios, busca el usuario",
                    "Haz clic en el botón de encendido (ícono de power)",
                    "Si está activo se desactivará (rojo), si está inactivo se activará (verde)",
                    "Los usuarios inactivos no pueden recibir nuevas encuestas"
                ],
                tip: "💡 Tip: Desactivar un usuario no elimina sus encuestas previas, solo evita que reciba nuevas."
            },
            "¿cómo copio el link de encuesta?": {
                title: "Copiar link de encuesta",
                steps: [
                    "En la tabla de usuarios, en la columna 'Link de Encuesta'",
                    "Haz clic en el botón azul con ícono de copiar",
                    "El link se copiará automáticamente al portapapeles",
                    "El botón se pondrá verde por 2 segundos confirmando la acción"
                ],
                tip: "💡 Tip: Puedes compartir este link por WhatsApp, email o cualquier medio digital."
            },
            "¿cómo elimino un usuario?": {
                title: "Eliminar usuario",
                steps: [
                    "En la tabla de usuarios, haz clic en el botón con tres puntos (···)",
                    "Selecciona la opción 'Eliminar' (última opción en rojo)",
                    "Confirma la eliminación",
                    "⚠️ Esta acción NO se puede deshacer"
                ],
                tip: "⚠️ Advertencia: Al eliminar un usuario también se eliminan todas sus encuestas asociadas."
            },
            "¿cómo busco usuarios?": {
                title: "Buscar y filtrar usuarios",
                steps: [
                    "En la parte superior de la tabla, haz clic en 'Mostrar' junto a 'Búsqueda y Filtros'",
                    "Puedes buscar por: Nombre, email o ubicación",
                    "Filtrar por: Tipo (Consultor/Sede) o Estado (Activo/Inactivo)",
                    "Haz clic en 'Buscar' para aplicar los filtros"
                ],
                tip: "💡 Tip: Puedes combinar múltiples filtros para búsquedas más específicas."
            },
            "¿cómo veo la vista previa de la encuesta?": {
                title: "Vista previa de encuesta",
                steps: [
                    "En la tabla de usuarios, haz clic en el botón con tres puntos (···)",
                    "Selecciona 'Vista Previa'",
                    "Se abrirá una nueva pestaña mostrando cómo se ve la encuesta para ese usuario"
                ],
                tip: "💡 Tip: Útil para verificar cómo se verá la encuesta antes de compartirla."
            },
            "default": {
                title: "¿Necesitas más ayuda?",
                steps: [
                    "Intenta reformular tu pregunta de manera más específica",
                    "Usa las preguntas frecuentes arriba como guía",
                    "Busca palabras clave como: crear, asignar, ver, eliminar, etc."
                ],
                tip: "💡 Tip: Si no encuentras lo que buscas, contacta al administrador del sistema."
            }
        };

        // Referencias a elementos
        const assistantToggle = document.getElementById('assistantToggle');
        const assistantWindow = document.getElementById('assistantWindow');
        const userQuestion = document.getElementById('userQuestion');
        const btnSend = document.querySelector('.btn-send');
        const btnCloseAssistant = document.querySelector('.btn-close-assistant');
        const quickActionBtns = document.querySelectorAll('.quick-action-btn');

        // Toggle del asistente (función global)
        window.toggleAssistant = function() {
            console.log('Toggle asistente');
            assistantToggle.classList.toggle('active');
            assistantWindow.classList.toggle('active');

            if (assistantWindow.classList.contains('active')) {
                userQuestion.focus();
            }
        }

        // Event listeners
        assistantToggle.addEventListener('click', window.toggleAssistant);
        btnCloseAssistant.addEventListener('click', window.toggleAssistant);

        // Manejar tecla Enter
        userQuestion.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                sendQuestion();
            }
        });

        // Botón enviar
        btnSend.addEventListener('click', sendQuestion);

        // Botones de acceso rápido
        quickActionBtns.forEach((btn, index) => {
            const questions = [
                '¿Cómo creo un nuevo usuario?',
                '¿Cómo genero un código QR?',
                '¿Cómo asigno sedes a un consultor?',
                '¿Cómo veo las encuestas?',
                '¿Cómo regenero el link de encuesta?',
                '¿Cómo activo o desactivo un usuario?'
            ];

            btn.addEventListener('click', function() {
                window.askQuestion(questions[index]);
            });
        });

        // Enviar pregunta
        function sendQuestion() {
            const question = userQuestion.value.trim();

            if (!question) return;

            addUserMessage(question);
            userQuestion.value = '';

            setTimeout(() => {
                const answer = findAnswer(question);
                addBotResponse(answer);
            }, 500);
        }

        // Preguntar desde botón rápido (función global)
        window.askQuestion = function(question) {
            userQuestion.value = question;
            sendQuestion();
        }

        // Agregar mensaje del usuario
        function addUserMessage(message) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'user-message';
            messageDiv.innerHTML = `
            <div class="message-content">
                <p>${message}</p>
            </div>
        `;
            chatMessages.appendChild(messageDiv);
            scrollToBottom();
        }

        // Agregar respuesta del bot
        function addBotResponse(answer) {
            const chatMessages = document.getElementById('chatMessages');

            // Mostrar indicador de escritura
            const typingDiv = document.createElement('div');
            typingDiv.className = 'bot-message';
            typingDiv.innerHTML = `
            <div class="message-avatar">
                <i class="mdi mdi-robot"></i>
            </div>
            <div class="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        `;
            chatMessages.appendChild(typingDiv);
            scrollToBottom();

            // Después de 1 segundo, mostrar respuesta
            setTimeout(() => {
                typingDiv.remove();

                const messageDiv = document.createElement('div');
                messageDiv.className = 'bot-message';

                let stepsHTML = '';
                if (answer.steps) {
                    stepsHTML = '<ol style="margin: 8px 0; padding-left: 20px;">';
                    answer.steps.forEach(step => {
                        stepsHTML += `<li style="margin-bottom: 6px;">${step}</li>`;
                    });
                    stepsHTML += '</ol>';
                }

                messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="mdi mdi-robot"></i>
                </div>
                <div class="message-content">
                    <p><strong>${answer.title}</strong></p>
                    ${stepsHTML}
                    ${answer.tip ? `<p style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #e5e7eb;"><small>${answer.tip}</small></p>` : ''}
                </div>
            `;
                chatMessages.appendChild(messageDiv);
                scrollToBottom();
            }, 1000);
        }

        // Buscar respuesta en la base de conocimiento
        function findAnswer(question) {
            const normalizedQuestion = question.toLowerCase();

            // Buscar coincidencia exacta
            for (const [key, value] of Object.entries(knowledgeBase)) {
                const cleanKey = key.toLowerCase().replace(/¿|\?/g, '');
                if (normalizedQuestion.includes(cleanKey)) {
                    return value;
                }
            }

            // Buscar por palabras clave
            if (normalizedQuestion.includes('crear') || normalizedQuestion.includes('nuevo')) {
                return knowledgeBase["¿cómo creo un nuevo usuario?"];
            }
            if (normalizedQuestion.includes('qr') || normalizedQuestion.includes('código')) {
                return knowledgeBase["¿cómo genero un código qr?"];
            }
            if (normalizedQuestion.includes('asign') || normalizedQuestion.includes('sede')) {
                return knowledgeBase["¿cómo asigno sedes a un consultor?"];
            }
            if (normalizedQuestion.includes('encuesta') || normalizedQuestion.includes('ver')) {
                return knowledgeBase["¿cómo veo las encuestas?"];
            }
            if (normalizedQuestion.includes('regener') || normalizedQuestion.includes('link')) {
                return knowledgeBase["¿cómo regenero el link de encuesta?"];
            }
            if (normalizedQuestion.includes('activ') || normalizedQuestion.includes('desactiv')) {
                return knowledgeBase["¿cómo activo o desactivo un usuario?"];
            }
            if (normalizedQuestion.includes('copi') || normalizedQuestion.includes('portapapeles')) {
                return knowledgeBase["¿cómo copio el link de encuesta?"];
            }
            if (normalizedQuestion.includes('elimin') || normalizedQuestion.includes('borrar')) {
                return knowledgeBase["¿cómo elimino un usuario?"];
            }
            if (normalizedQuestion.includes('busc') || normalizedQuestion.includes('filtr')) {
                return knowledgeBase["¿cómo busco usuarios?"];
            }
            if (normalizedQuestion.includes('vista') || normalizedQuestion.includes('preview')) {
                return knowledgeBase["¿cómo veo la vista previa de la encuesta?"];
            }

            return knowledgeBase.default;
        }

        // Scroll al final del chat
        function scrollToBottom() {
            const content = document.getElementById('assistantContent');
            content.scrollTop = content.scrollHeight;
        }
    });
</script>
