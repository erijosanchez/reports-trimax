
<script src="https://cdn.tailwindcss.com"></script>

<div id="ai-assistant" class="fixed bottom-4 right-4 z-50">
    <!-- Botón flotante -->
    <button id="ai-toggle"
        class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-full p-4 shadow-2xl transition-all duration-300 hover:scale-110">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
            </path>
        </svg>
    </button>

    <!-- Panel del chat -->
    <div id="ai-chat-panel" class="hidden bg-white rounded-2xl shadow-2xl w-96 h-[600px] flex flex-col overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                    <div class="absolute inset-0 w-3 h-3 bg-green-400 rounded-full animate-ping"></div>
                </div>
                <div>
                    <h3 class="font-semibold">Asistente Trimax</h3>
                    <p class="text-xs text-blue-100">Powered by Groq AI</p>
                </div>
            </div>
            <button id="ai-close" class="hover:bg-blue-800 rounded-lg p-1 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Messages -->
        <div id="ai-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
            <div class="ai-message assistant">
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-800">¡Hola! 👋 Soy tu asistente de Trimax. Puedo ayudarte con:</p>
                    <ul class="text-xs text-gray-600 mt-2 space-y-1 ml-4">
                        <li>• Descuentos especiales</li>
                        <li>• Convenios comerciales</li>
                        <li>• Consulta de órdenes</li>
                        <li>• Métricas y reportes</li>
                    </ul>
                    <p class="text-sm text-gray-800 mt-3">¿En qué puedo ayudarte?</p>
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="border-t p-4 bg-white">
            <div class="flex gap-2">
                <input type="text" id="ai-input" placeholder="Escribe tu pregunta..."
                    class="flex-1 border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button id="ai-send"
                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-6 py-2 font-medium transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                <span>🧠 Aprendiendo de cada conversación</span>
                <span id="ai-status" class="text-green-600">● En línea</span>
            </div>
        </div>
    </div>
</div>

<style>
    #ai-chat-panel {
        animation: slideUp 0.3s ease-out;
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

    .ai-message.user {
        display: flex;
        justify-content: flex-end;
    }

    .ai-message.assistant {
        display: flex;
        justify-content: flex-start;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('ai-toggle');
        const panel = document.getElementById('ai-chat-panel');
        const close = document.getElementById('ai-close');
        const input = document.getElementById('ai-input');
        const send = document.getElementById('ai-send');
        const messages = document.getElementById('ai-messages');
        const status = document.getElementById('ai-status');

        let sessionId = Date.now().toString();

        toggle.addEventListener('click', () => {
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) {
                input.focus();
            }
        });

        close.addEventListener('click', () => {
            panel.classList.add('hidden');
        });

        send.addEventListener('click', sendMessage);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        async function sendMessage() {
            const question = input.value.trim();
            if (!question) return;

            // Mostrar pregunta del usuario
            addMessage(question, 'user');
            input.value = '';

            // Mostrar typing indicator
            const typingId = addTypingIndicator();
            status.textContent = '● Pensando...';
            status.className = 'text-yellow-600';

            try {
                const response = await fetch('/api/ai/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        question: question,
                        context: {
                            module: getCurrentModule(),
                            page: window.location.pathname,
                            sessionId: sessionId
                        }
                    })
                });

                if (!response.ok) {
                    throw new Error('Error en la respuesta');
                }

                const data = await response.json();

                // Remover typing indicator
                removeTypingIndicator(typingId);
                status.textContent = '● En línea';
                status.className = 'text-green-600';

                // Mostrar respuesta
                addMessage(data.answer, 'assistant', data.interaction_id, data.sources);

            } catch (error) {
                console.error('Error:', error);
                removeTypingIndicator(typingId);
                status.textContent = '● Error';
                status.className = 'text-red-600';
                addMessage('Lo siento, ocurrió un error. Por favor intenta de nuevo.', 'assistant');

                setTimeout(() => {
                    status.textContent = '● En línea';
                    status.className = 'text-green-600';
                }, 3000);
            }
        }

        function addMessage(text, type, interactionId = null, source = null) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `ai-message ${type}`;

            const bubble = document.createElement('div');
            bubble.className = type === 'user' ?
                'bg-blue-600 text-white rounded-2xl p-3 max-w-[80%] shadow-md' :
                'bg-white rounded-2xl p-4 max-w-[80%] shadow-sm border border-gray-100';

            bubble.innerHTML = `<p class="text-sm whitespace-pre-wrap">${text}</p>`;

            // Badge de fuente
            if (source && type === 'assistant') {
                const badge = document.createElement('div');
                badge.className = 'mt-2 inline-block';
                badge.innerHTML = source === 'learned_knowledge' ?
                    '<span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">🧠 Conocimiento aprendido</span>' :
                    '<span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">🤖 IA generada</span>';
                bubble.appendChild(badge);
            }

            // Agregar botones de feedback
            if (type === 'assistant' && interactionId) {
                const feedbackDiv = document.createElement('div');
                feedbackDiv.className = 'mt-3 pt-3 border-t border-gray-100 flex gap-3 text-xs';
                feedbackDiv.innerHTML = `
                <button onclick="giveFeedback(${interactionId}, true, this)" 
                        class="feedback-btn text-gray-600 hover:text-green-600 transition-colors flex items-center gap-1">
                    <span>👍</span> Útil
                </button>
                <button onclick="giveFeedback(${interactionId}, false, this)" 
                        class="feedback-btn text-gray-600 hover:text-red-600 transition-colors flex items-center gap-1">
                    <span>👎</span> No útil
                </button>
            `;
                bubble.appendChild(feedbackDiv);
            }

            messageDiv.appendChild(bubble);
            messages.appendChild(messageDiv);
            messages.scrollTop = messages.scrollHeight;
        }

        function addTypingIndicator() {
            const id = 'typing-' + Date.now();
            const div = document.createElement('div');
            div.id = id;
            div.className = 'ai-message assistant';
            div.innerHTML = `
            <div class="bg-white rounded-2xl p-4 max-w-[80%] shadow-sm border border-gray-100">
                <div class="flex gap-1">
                    <div class="w-2 h-2 bg-blue-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
            </div>
        `;
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
            return id;
        }

        function removeTypingIndicator(id) {
            document.getElementById(id)?.remove();
        }

        function getCurrentModule() {
            const path = window.location.pathname;
            if (path.includes('descuento')) return 'descuentos';
            if (path.includes('convenio')) return 'convenios';
            if (path.includes('orden')) return 'ordenes';
            if (path.includes('dashboard')) return 'dashboard';
            return 'general';
        }
    });

    async function giveFeedback(interactionId, wasHelpful, button) {
        try {
            const response = await fetch('/api/ai/feedback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    interaction_id: interactionId,
                    was_helpful: wasHelpful
                })
            });

            if (response.ok) {
                // Deshabilitar todos los botones de feedback de este mensaje
                const parent = button.parentElement;
                parent.querySelectorAll('.feedback-btn').forEach(btn => {
                    btn.disabled = true;
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                });

                button.innerHTML = wasHelpful ?
                    '<span>✓</span> ¡Gracias!' :
                    '<span>✓</span> Registrado';
            }
        } catch (error) {
            console.error('Error enviando feedback:', error);
        }
    }
</script>
