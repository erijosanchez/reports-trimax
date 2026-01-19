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
                    <h6 class="mb-0">Asistente Trimax IA</h6>
                    <small class="text-white-50">Con inteligencia artificial 🧠</small>
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
                        <p>¡Hola! 👋 Soy tu asistente con IA de Trimax.</p>
                        <p>Puedo ayudarte con:</p>
                        <ul style="margin: 8px 0; padding-left: 20px; line-height: 1.8;">
                            <li>📊 Consultas de ventas y facturación</li>
                            <li>👥 Información de clientes</li>
                            <li>🏢 Datos por sedes y zonas</li>
                            <li>👓 Productos y marcas de lentes</li>
                            <li>💰 Reportes y estadísticas</li>
                            <li>❓ Ayuda con el sistema</li>
                        </ul>
                        <p><small>Si no sé algo, te lo digo sin pena jajaja 🤷‍♂️</small></p>
                    </div>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div class="quick-actions">
                <h6 class="quick-actions-title">Consultas frecuentes:</h6>
                <div class="quick-action-buttons">
                    <button class="quick-action-btn" data-question="¿Cuáles son las ventas del mes actual?">
                        <i class="mdi mdi-chart-line"></i> Ventas del mes
                    </button>
                    <button class="quick-action-btn" data-question="Muéstrame los principales clientes">
                        <i class="mdi mdi-account-group"></i> Top clientes
                    </button>
                    <button class="quick-action-btn" data-question="¿Qué productos se venden más?">
                        <i class="mdi mdi-package-variant"></i> Productos top
                    </button>
                    <button class="quick-action-btn" data-question="¿Cómo están las ventas por sede?">
                        <i class="mdi mdi-office-building"></i> Ventas x Sede
                    </button>
                    <button class="quick-action-btn" data-question="¿Cuántas facturas hay este año?">
                        <i class="mdi mdi-file-document"></i> Facturación
                    </button>
                    <button class="quick-action-btn" data-question="¿Qué puedes hacer?">
                        <i class="mdi mdi-help-circle"></i> ¿Qué sabes hacer?
                    </button>
                </div>
            </div>

            <!-- Área de chat -->
            <div id="chatMessages" class="chat-messages"></div>
        </div>

        <!-- Input de búsqueda -->
        <div class="assistant-input">
            <input type="text" id="userQuestion" class="form-control"
                placeholder="Pregúntame sobre ventas, clientes, productos...">
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
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        cursor: pointer;
        z-index: 9999;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .assistant-toggle:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    }

    .assistant-icon,
    .assistant-close-icon {
        color: white;
        font-size: 30px;
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
        bottom: 70px;
        right: 0;
        background: white;
        color: #667eea;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        white-space: nowrap;
        opacity: 0;
        animation: fadeInOut 5s ease-in-out infinite;
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
        width: 420px;
        height: 650px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        z-index: 9998;
        overflow: hidden;
    }

    .assistant-window.active {
        display: flex;
        animation: slideUp 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.9);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Header */
    .assistant-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .assistant-avatar {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.25);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 26px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    .btn-close-assistant {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-close-assistant:hover {
        background: rgba(255, 255, 255, 0.35);
        transform: rotate(90deg);
    }

    /* Contenido */
    .assistant-content {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
    }

    .welcome-message {
        margin-bottom: 20px;
    }

    .bot-message {
        display: flex;
        align-items: flex-start;
        margin-bottom: 16px;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message-avatar {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .message-content {
        background: white;
        padding: 14px 18px;
        border-radius: 15px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        max-width: 85%;
        position: relative;
    }

    .message-content p {
        margin: 0;
        margin-bottom: 8px;
        line-height: 1.6;
    }

    .message-content p:last-child {
        margin-bottom: 0;
    }

    .message-content ul,
    .message-content ol {
        margin: 8px 0;
        padding-left: 20px;
    }

    .message-content li {
        margin-bottom: 6px;
        line-height: 1.5;
    }

    .user-message {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 16px;
        animation: fadeIn 0.3s ease;
    }

    .user-message .message-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    /* Acciones rápidas */
    .quick-actions {
        margin-bottom: 20px;
        background: white;
        padding: 15px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .quick-actions-title {
        font-size: 13px;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .quick-action-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .quick-action-btn {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px solid #e9ecef;
        padding: 12px 10px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 6px;
        color: #495057;
    }

    .quick-action-btn:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .quick-action-btn i {
        font-size: 18px;
    }

    /* Chat messages */
    .chat-messages {
        margin-top: 20px;
    }

    /* Input */
    .assistant-input {
        padding: 18px;
        background: white;
        border-top: 2px solid #e9ecef;
        display: flex;
        gap: 10px;
    }

    .assistant-input input {
        flex: 1;
        border: 2px solid #e9ecef;
        border-radius: 25px;
        padding: 12px 18px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .assistant-input input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-send {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-send:hover {
        transform: scale(1.1) rotate(15deg);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-send:active {
        transform: scale(0.95);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .assistant-window {
            width: calc(100vw - 40px);
            right: 20px;
            left: 20px;
            height: 550px;
        }

        .quick-action-buttons {
            grid-template-columns: 1fr;
        }
    }

    /* Scrollbar personalizado */
    .assistant-content::-webkit-scrollbar {
        width: 8px;
    }

    .assistant-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .assistant-content::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }

    .assistant-content::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }

    /* Animación de escritura */
    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 14px 18px;
        background: white;
        border-radius: 15px;
        width: fit-content;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .typing-dot {
        width: 10px;
        height: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            transform: translateY(-12px);
        }
    }

    /* Efecto de error */
    .error-message {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 12px;
        margin-top: 10px;
        border-radius: 8px;
    }

    .error-message p {
        color: #856404;
        margin: 0;
        font-size: 13px;
    }

    /* Tabla de datos en respuestas */
    .data-table {
        width: 100%;
        margin: 10px 0;
        border-collapse: collapse;
        font-size: 12px;
    }

    .data-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px;
        text-align: left;
        font-weight: 600;
    }

    .data-table td {
        padding: 8px;
        border-bottom: 1px solid #e9ecef;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    /* Stats cards */
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 10px;
        margin: 10px 0;
        text-align: center;
    }

    .stats-card .stat-value {
        font-size: 24px;
        font-weight: bold;
        margin: 5px 0;
    }

    .stats-card .stat-label {
        font-size: 12px;
        opacity: 0.9;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🤖 Asistente Trimax IA cargado');

        // Base URL de tu API Laravel
        const TRIMAX_API = '/api/assistant';
        const AI_API = '/api/assistant/chat'; // Proxy de IA en Laravel

        // Base de conocimiento del sistema mejorada
        const systemKnowledge = `
Eres el Asistente Virtual de Trimax, un sistema de gestión de ventas y óptica.

TU PERSONALIDAD:
- Eres amigable, cercano y con sentido del humor
- Hablas de manera natural, como un compañero de trabajo
- Cuando no sabes algo, lo admites sin pena: "Uy, esa no me la sé 🤔"
- Usas emojis con moderación para ser más expresivo
- Eres profesional pero no robótico

DATOS QUE MANEJAS:
La base de datos contiene información de ventas con estos campos:
- Fecha, Tipo de Documento, Número de Documento
- RUC/DNI del cliente, Razón Social, Tipo de Cliente
- Motorizado, Sede, Zona
- Código de Producto, Descripción, Importe, IGV
- Cantidad, Año, Mes
- Detalles del producto: Tallado, Marca, Diseño, Material
- Tipo Fotocromático, Color, Tipo de Artículo
- Información de notas de crédito

CAPACIDADES:
1. Consultar ventas por período (mes, año)
2. Obtener información de clientes específicos
3. Ver productos más vendidos
4. Analizar ventas por sede/zona
5. Consultar facturas y documentos
6. Ver marcas y tipos de productos
7. Generar estadísticas básicas

INSTRUCCIONES:
- Si te piden datos específicos, menciona que vas a consultar la base de datos
- Formatea números con comas: S/ 1,234.56
- Usa tablas HTML para mostrar datos cuando sea apropiado
- Si la consulta es muy compleja, explica que necesitas más detalles
- Mantén respuestas concisas pero completas

AYUDA DEL SISTEMA:
También puedes ayudar con el uso del sistema de gestión de usuarios:
- Crear usuarios, generar QR, asignar sedes
- Ver encuestas, regenerar links
- Activar/desactivar usuarios, copiar links
- Buscar y filtrar información
`;

        // Historial de conversación
        let conversationHistory = [];

        // Referencias a elementos
        const assistantToggle = document.getElementById('assistantToggle');
        const assistantWindow = document.getElementById('assistantWindow');
        const userQuestion = document.getElementById('userQuestion');
        const btnSend = document.querySelector('.btn-send');
        const btnCloseAssistant = document.querySelector('.btn-close-assistant');
        const quickActionBtns = document.querySelectorAll('.quick-action-btn');

        // Toggle del asistente
        window.toggleAssistant = function() {
            assistantToggle.classList.toggle('active');
            assistantWindow.classList.toggle('active');
            if (assistantWindow.classList.contains('active')) {
                userQuestion.focus();
            }
        };

        // Event listeners
        assistantToggle.addEventListener('click', window.toggleAssistant);
        btnCloseAssistant.addEventListener('click', window.toggleAssistant);

        userQuestion.addEventListener('keypress', function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendQuestion();
            }
        });

        btnSend.addEventListener('click', sendQuestion);

        // Botones de acceso rápido
        quickActionBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const question = this.getAttribute('data-question');
                userQuestion.value = question;
                sendQuestion();
            });
        });

        // Función principal para enviar pregunta
        async function sendQuestion() {
            const question = userQuestion.value.trim();
            if (!question) return;

            // Deshabilitar input mientras procesa
            userQuestion.disabled = true;
            btnSend.disabled = true;

            // Agregar mensaje del usuario
            addUserMessage(question);
            userQuestion.value = '';

            // Agregar indicador de escritura
            const typingId = addTypingIndicator();

            try {
                // Primero: Verificar si necesita datos de la BD
                const needsData = await checkIfNeedsData(question);

                let contextData = null;
                if (needsData) {
                    // Consultar la API de Laravel para obtener datos
                    contextData = await fetchTrimaxData(question);
                }

                // Llamar a la IA con o sin contexto de datos
                const response = await callAI(question, contextData);

                // Remover indicador y mostrar respuesta
                removeTypingIndicator(typingId);
                addBotMessage(response);

                // Actualizar historial
                conversationHistory.push({
                    role: 'user',
                    content: question
                }, {
                    role: 'assistant',
                    content: response
                });

                // Limitar historial a últimas 10 interacciones
                if (conversationHistory.length > 20) {
                    conversationHistory = conversationHistory.slice(-20);
                }

            } catch (error) {
                console.error('Error:', error);
                removeTypingIndicator(typingId);
                addErrorMessage(error.message);
            } finally {
                userQuestion.disabled = false;
                btnSend.disabled = false;
                userQuestion.focus();
            }
        }

        // Verificar si la pregunta necesita datos de la BD
        async function checkIfNeedsData(question) {
            const dataKeywords = [
                'ventas', 'cliente', 'producto', 'factura', 'sede', 'zona',
                'documento', 'importe', 'mes', 'año', 'marca', 'cuánto',
                'cuántos', 'total', 'suma', 'precio', 'venta', 'compra',
                'top', 'mejor', 'peor', 'estadística', 'reporte', 'mostrar',
                'dame', 'busca', 'encuentra'
            ];

            const lowerQuestion = question.toLowerCase();
            return dataKeywords.some(keyword => lowerQuestion.includes(keyword));
        }

        // Consultar datos a la API de Laravel
        async function fetchTrimaxData(question) {
            try {
                const response = await fetch(TRIMAX_API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        query: question
                    })
                });

                if (!response.ok) {
                    throw new Error('Error al consultar datos');
                }

                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error fetching data:', error);
                return null;
            }
        }

        // Llamar a la API de IA a través del proxy de Laravel
        async function callAI(userMessage, contextData = null) {
            let enhancedMessage = userMessage;

            // Si hay datos del contexto, agregar a la pregunta
            if (contextData && contextData.data) {
                enhancedMessage += `\n\nCONTEXTO DE DATOS:\n${JSON.stringify(contextData.data, null, 2)}`;
            }

            const response = await fetch(AI_API, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: enhancedMessage,
                    history: conversationHistory
                })
            });

            if (!response.ok) {
                throw new Error(`Error ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error desconocido');
            }

            return data.message;
        }

        // Agregar mensaje del usuario
        function addUserMessage(message) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'user-message';
            messageDiv.innerHTML = `
            <div class="message-content">
                <p>${escapeHtml(message)}</p>
            </div>
        `;
            chatMessages.appendChild(messageDiv);
            scrollToBottom();
        }

        // Agregar mensaje del bot
        function addBotMessage(message) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'bot-message';

            // Formatear mensaje (permite HTML para tablas y formato)
            const formattedMessage = formatBotMessage(message);

            messageDiv.innerHTML = `
            <div class="message-avatar">
                <i class="mdi mdi-robot"></i>
            </div>
            <div class="message-content">
                ${formattedMessage}
            </div>
        `;
            chatMessages.appendChild(messageDiv);
            scrollToBottom();
        }

        // Formatear mensaje del bot
        function formatBotMessage(message) {
            // No escapar HTML si contiene tablas
            if (message.includes('<table') || message.includes('<div')) {
                return message;
            }

            let formatted = escapeHtml(message);

            // Convertir saltos de línea
            formatted = formatted.replace(/\n\n/g, '</p><p>');
            formatted = formatted.replace(/\n/g, '<br>');

            // Convertir ** en negrita
            formatted = formatted.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            // Detectar listas
            const lines = formatted.split('<br>');
            let inList = false;
            let result = '';

            for (let line of lines) {
                if (/^\d+\./.test(line.trim())) {
                    if (!inList) {
                        result += '<ol style="margin: 8px 0; padding-left: 20px;">';
                        inList = true;
                    }
                    result += `<li>${line.replace(/^\d+\.\s*/, '')}</li>`;
                } else if (/^[-•]/.test(line.trim())) {
                    if (!inList) {
                        result += '<ul style="margin: 8px 0; padding-left: 20px;">';
                        inList = true;
                    }
                    result += `<li>${line.replace(/^[-•]\s*/, '')}</li>`;
                } else {
                    if (inList) {
                        result += inList === 'ol' ? '</ol>' : '</ul>';
                        inList = false;
                    }
                    result += line + '<br>';
                }
            }

            if (inList) {
                result += '</ol>';
            }

            return `<p>${result}</p>`;
        }

        // Agregar indicador de escritura
        function addTypingIndicator() {
            const chatMessages = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            const id = 'typing-' + Date.now();
            typingDiv.id = id;
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
            return id;
        }

        // Remover indicador de escritura
        function removeTypingIndicator(id) {
            const indicator = document.getElementById(id);
            if (indicator) {
                indicator.remove();
            }
        }

        // Mensaje de error
        function addErrorMessage(errorMsg = '') {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'bot-message';
            messageDiv.innerHTML = `
            <div class="message-avatar">
                <i class="mdi mdi-robot"></i>
            </div>
            <div class="message-content">
                <p>¡Oops! 😅 Parece que tuve un problemita técnico.</p>
                <p>Intenta preguntarme de nuevo en un momento, ¿vale?</p>
                <div class="error-message">
                    <p><strong>💡 Tip:</strong> ${errorMsg || 'Si el problema persiste, intenta con las preguntas frecuentes de arriba.'}</p>
                </div>
            </div>
        `;
            chatMessages.appendChild(messageDiv);
            scrollToBottom();
        }

        // Scroll al final
        function scrollToBottom() {
            const content = document.getElementById('assistantContent');
            setTimeout(() => {
                content.scrollTop = content.scrollHeight;
            }, 100);
        }

        // Escapar HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        console.log('✅ Asistente IA listo para usar');
    });
</script>
