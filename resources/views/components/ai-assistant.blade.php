
<script src="https://cdn.tailwindcss.com"></script>

<div id="ai-assistant" class="right-5 bottom-5 z-[9999] fixed" style="font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;">

    {{-- Botón flotante --}}
    <button id="ai-toggle"
        class="group relative bg-gradient-to-br from-blue-600 hover:from-blue-700 to-indigo-700 hover:to-indigo-800 shadow-blue-500/25 shadow-lg hover:shadow-blue-500/30 hover:shadow-xl p-3.5 rounded-2xl text-white hover:scale-105 active:scale-95 transition-all duration-300">
        <svg id="ai-icon-chat" class="w-6 h-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <svg id="ai-icon-close" class="hidden w-6 h-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    {{-- Panel del chat --}}
    <div id="ai-chat-panel" class="hidden right-0 bottom-16 absolute flex flex-col bg-white shadow-2xl shadow-black/15 border border-gray-100 rounded-2xl w-[400px] h-[580px] overflow-hidden">

        {{-- Header --}}
        <div class="flex flex-shrink-0 justify-between items-center bg-gradient-to-r from-blue-600 to-indigo-700 px-4 py-3 text-white">
            <div class="flex items-center gap-3">
                <div class="flex justify-center items-center bg-white/20 backdrop-blur-sm rounded-lg w-8 h-8 font-bold text-sm">T</div>
                <div>
                    <h3 class="font-semibold text-sm leading-tight">Asistente Trimax</h3>
                    <div class="flex items-center gap-1.5">
                        <span id="ai-status-dot" class="bg-green-400 rounded-full w-1.5 h-1.5"></span>
                        <span id="ai-status-text" class="text-[11px] text-blue-100">En línea</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button id="ai-clear" title="Limpiar conversación" class="hover:bg-white/15 p-1.5 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                <button id="ai-close" class="hover:bg-white/15 p-1.5 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mensajes --}}
        <div id="ai-messages" class="flex-1 space-y-3 bg-gradient-to-b from-gray-50 to-white p-4 overflow-y-auto scroll-smooth">
            <div class="ai-msg assistant">
                <div class="bg-white shadow-sm p-3.5 border border-gray-100 rounded-2xl rounded-tl-md max-w-[85%]">
                    <p class="text-[13px] text-gray-800 leading-relaxed">¡Hola <strong>{{ auth()->user()->name ?? 'Usuario' }}</strong>! 👋 Soy tu asistente del CRM Trimax.</p>
                    <p class="mt-2 text-[13px] text-gray-600">¿En qué puedo ayudarte?</p>
                </div>
            </div>
            <div id="ai-suggestions" class="flex flex-wrap gap-2 px-1">
                <button onclick="aiQuickAsk('¿Cuál es el resumen general del sistema?')" class="bg-blue-50 hover:bg-blue-100 px-3 py-1.5 border border-blue-200 rounded-full text-[11px] text-blue-700 transition-colors">📊 Resumen general</button>
                <button onclick="aiQuickAsk('¿Cómo van las ventas de este mes?')" class="bg-blue-50 hover:bg-blue-100 px-3 py-1.5 border border-blue-200 rounded-full text-[11px] text-blue-700 transition-colors">💰 Ventas del mes</button>
                <button onclick="aiQuickAsk('¿Cuáles son las mejores sedes en ventas?')" class="bg-blue-50 hover:bg-blue-100 px-3 py-1.5 border border-blue-200 rounded-full text-[11px] text-blue-700 transition-colors">🏆 Top sedes</button>
                <button onclick="aiQuickAsk('¿Cuántos descuentos están pendientes?')" class="bg-blue-50 hover:bg-blue-100 px-3 py-1.5 border border-blue-200 rounded-full text-[11px] text-blue-700 transition-colors">🏷️ Descuentos</button>
                <button onclick="aiQuickAsk('¿Cuántos acuerdos comerciales están vigentes?')" class="bg-blue-50 hover:bg-blue-100 px-3 py-1.5 border border-blue-200 rounded-full text-[11px] text-blue-700 transition-colors">📋 Acuerdos</button>
            </div>
        </div>

        {{-- Input --}}
        <div class="flex-shrink-0 bg-white p-3 border-gray-100 border-t">
            <div class="flex items-end gap-2">
                <textarea id="ai-input" rows="1" placeholder="Escribe tu pregunta..."
                    class="flex-1 px-3.5 py-2.5 border border-gray-200 focus:border-blue-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/40 max-h-[80px] text-[13px] placeholder:text-gray-400 transition-all resize-none"></textarea>
                <button id="ai-send" class="flex-shrink-0 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 p-2.5 rounded-xl text-white active:scale-95 transition-all disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#ai-chat-panel { animation: aiSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes aiSlideUp { from { opacity: 0; transform: translateY(12px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
.ai-msg.user { display: flex; justify-content: flex-end; }
.ai-msg.assistant { display: flex; justify-content: flex-start; }
.ai-msg .msg-content p { margin-bottom: 0.4em; }
.ai-msg .msg-content p:last-child { margin-bottom: 0; }
.ai-msg .msg-content strong { font-weight: 600; }
.ai-msg .msg-content ul, .ai-msg .msg-content ol { padding-left: 1.2em; margin: 0.3em 0; }
.ai-msg .msg-content li { margin-bottom: 0.15em; }
.ai-msg .msg-content table { width: 100%; border-collapse: collapse; margin: 0.4em 0; font-size: 0.88em; }
.ai-msg .msg-content th, .ai-msg .msg-content td { border: 1px solid #e5e7eb; padding: 3px 6px; text-align: left; }
.ai-msg .msg-content th { background: #f3f4f6; font-weight: 600; }
.typing-dot { animation: typeBounce 1.4s ease-in-out infinite; }
.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes typeBounce { 0%, 60%, 100% { transform: translateY(0); opacity: 0.4; } 30% { transform: translateY(-4px); opacity: 1; } }
#ai-messages::-webkit-scrollbar { width: 4px; }
#ai-messages::-webkit-scrollbar-track { background: transparent; }
#ai-messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
@media (max-width: 640px) { #ai-chat-panel { width: calc(100vw - 24px) !important; height: calc(100vh - 100px) !important; right: -8px; bottom: 56px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('ai-toggle');
    const panel = document.getElementById('ai-chat-panel');
    const close = document.getElementById('ai-close');
    const clear = document.getElementById('ai-clear');
    const input = document.getElementById('ai-input');
    const send = document.getElementById('ai-send');
    const messages = document.getElementById('ai-messages');
    const statusDot = document.getElementById('ai-status-dot');
    const statusText = document.getElementById('ai-status-text');
    const iconChat = document.getElementById('ai-icon-chat');
    const iconClose = document.getElementById('ai-icon-close');

    let sessionId = localStorage.getItem('ai_session_id') || Date.now().toString();
    localStorage.setItem('ai_session_id', sessionId);
    let isOpen = false;
    let isSending = false;

    // Toggle
    toggle.addEventListener('click', () => {
        isOpen = !isOpen;
        panel.classList.toggle('hidden', !isOpen);
        iconChat.classList.toggle('hidden', isOpen);
        iconClose.classList.toggle('hidden', !isOpen);
        if (isOpen) input.focus();
    });

    close.addEventListener('click', () => {
        isOpen = false;
        panel.classList.add('hidden');
        iconChat.classList.remove('hidden');
        iconClose.classList.add('hidden');
    });

    // Limpiar
    clear.addEventListener('click', async () => {
        if (!confirm('¿Limpiar toda la conversación?')) return;
        try {
            await fetch('/ai/clear-history', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ session_id: sessionId })
            });
        } catch (e) {}
        sessionId = Date.now().toString();
        localStorage.setItem('ai_session_id', sessionId);
        messages.innerHTML = '';
        addWelcome();
    });

    // Enviar
    send.addEventListener('click', sendMessage);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });

    // Auto-resize textarea
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 80) + 'px';
    });

    window.aiSendMessage = sendMessage;

    async function sendMessage() {
        const question = input.value.trim();
        if (!question || isSending) return;
        isSending = true;
        send.disabled = true;

        // Ocultar sugerencias
        document.getElementById('ai-suggestions')?.remove();

        addMsg(question, 'user');
        input.value = '';
        input.style.height = 'auto';

        const typingId = showTyping();
        setStatus('thinking');

        try {
            const res = await fetch('/ai/ask', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({
                    question,
                    context: { module: getModule(), page: window.location.pathname, sessionId }
                })
            });
            hideTyping(typingId);
            if (!res.ok) throw new Error('Error');
            const data = await res.json();
            setStatus('online');
            addMsg(data.answer, 'assistant', data.interaction_id, data.tools_used || []);
        } catch (err) {
            console.error(err);
            hideTyping(typingId);
            setStatus('error');
            addMsg('Lo siento, ocurrió un error. Intenta de nuevo.', 'assistant');
            setTimeout(() => setStatus('online'), 3000);
        }
        isSending = false;
        send.disabled = false;
        input.focus();
    }

    function addMsg(text, type, interactionId = null, toolsUsed = []) {
        const div = document.createElement('div');
        div.className = `ai-msg ${type}`;
        const bubble = document.createElement('div');

        if (type === 'user') {
            bubble.className = 'bg-blue-600 text-white rounded-2xl rounded-tr-md p-3 max-w-[85%] shadow-sm';
            bubble.innerHTML = `<p class="text-[13px] leading-relaxed">${escHtml(text)}</p>`;
        } else {
            bubble.className = 'bg-white rounded-2xl rounded-tl-md p-3.5 max-w-[85%] shadow-sm border border-gray-100';
            bubble.innerHTML = `<div class="text-[13px] text-gray-800 leading-relaxed msg-content">${renderMd(text)}</div>`;

            if (toolsUsed.length > 0) {
                bubble.insertAdjacentHTML('beforeend', `<div class="mt-2"><span class="inline-flex items-center gap-1 bg-emerald-50 px-2 py-0.5 border border-emerald-200 rounded-full text-[10px] text-emerald-700">✓ Datos verificados</span></div>`);
            }

            if (interactionId) {
                bubble.insertAdjacentHTML('beforeend', `
                    <div class="flex gap-2 mt-2.5 pt-2 border-gray-50 border-t">
                        <button onclick="aiFeedback(${interactionId}, true, this)" class="flex items-center gap-1 hover:bg-emerald-50 px-2 py-1 rounded-lg text-[11px] text-gray-400 hover:text-emerald-600 transition-colors ai-fb">👍 Útil</button>
                        <button onclick="aiFeedback(${interactionId}, false, this)" class="flex items-center gap-1 hover:bg-red-50 px-2 py-1 rounded-lg text-[11px] text-gray-400 hover:text-red-500 transition-colors ai-fb">👎 Mejorar</button>
                    </div>
                `);
            }
        }

        div.appendChild(bubble);
        messages.appendChild(div);
        messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });
    }

    function addWelcome() {
        const name = @json(auth()->user()->name ?? 'Usuario');
        messages.insertAdjacentHTML('beforeend', `
            <div class="ai-msg assistant">
                <div class="bg-white shadow-sm p-3.5 border border-gray-100 rounded-2xl rounded-tl-md max-w-[85%]">
                    <p class="text-[13px] text-gray-800">¡Hola <strong>${name}</strong>! 👋 Conversación limpia.</p>
                    <p class="mt-1 text-[13px] text-gray-600">¿En qué puedo ayudarte?</p>
                </div>
            </div>
            <div id="ai-suggestions" class="flex flex-wrap gap-2 px-1">
                <button onclick="aiQuickAsk('¿Cuál es el resumen general?')" class="bg-blue-50 hover:bg-blue-100 px-3 py-1.5 border border-blue-200 rounded-full text-[11px] text-blue-700 transition-colors">📊 Resumen</button>
                <button onclick="aiQuickAsk('¿Cómo van las ventas?')" class="bg-blue-50 hover:bg-blue-100 px-3 py-1.5 border border-blue-200 rounded-full text-[11px] text-blue-700 transition-colors">💰 Ventas</button>
                <button onclick="aiQuickAsk('Top sedes en ventas')" class="bg-blue-50 hover:bg-blue-100 px-3 py-1.5 border border-blue-200 rounded-full text-[11px] text-blue-700 transition-colors">🏆 Top sedes</button>
            </div>
        `);
    }

    function showTyping() {
        const id = 'typ-' + Date.now();
        messages.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="ai-msg assistant">
                <div class="bg-white shadow-sm p-3.5 border border-gray-100 rounded-2xl rounded-tl-md max-w-[85%]">
                    <div class="flex items-center gap-1.5 h-5">
                        <div class="bg-blue-400 rounded-full w-2 h-2 typing-dot"></div>
                        <div class="bg-blue-400 rounded-full w-2 h-2 typing-dot"></div>
                        <div class="bg-blue-400 rounded-full w-2 h-2 typing-dot"></div>
                    </div>
                </div>
            </div>
        `);
        messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });
        return id;
    }

    function hideTyping(id) { document.getElementById(id)?.remove(); }

    function setStatus(s) {
        const m = { online: ['bg-green-400', 'En línea'], thinking: ['bg-yellow-400 animate-pulse', 'Consultando datos...'], error: ['bg-red-400', 'Error'] };
        const [dot, txt] = m[s] || m.online;
        statusDot.className = `w-1.5 h-1.5 rounded-full ${dot}`;
        statusText.textContent = txt;
    }

    function getModule() {
        const p = window.location.pathname;
        if (p.includes('descuento')) return 'descuentos';
        if (p.includes('convenio') || p.includes('acuerdo')) return 'convenios';
        if (p.includes('orden')) return 'ordenes';
        if (p.includes('dashboard')) return 'dashboard';
        if (p.includes('encuesta') || p.includes('survey')) return 'encuestas';
        return 'general';
    }

    function escHtml(t) {
        const d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    }

    function renderMd(text) {
        if (!text) return '';
        return text
            // Bold
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            // Italic
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            // Inline code
            .replace(/`(.*?)`/g, '<code>$1</code>')
            // Unordered lists
            .replace(/^[-•]\s+(.+)/gm, '<li>$1</li>')
            // Ordered lists
            .replace(/^\d+\.\s+(.+)/gm, '<li>$1</li>')
            // Wrap consecutive li tags in ul
            .replace(/((?:<li>.*<\/li>\s*)+)/g, '<ul>$1</ul>')
            // Headers
            .replace(/^### (.+)/gm, '<strong style="font-size:1em;">$1</strong>')
            .replace(/^## (.+)/gm, '<strong style="font-size:1.05em;">$1</strong>')
            // Paragraphs
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>')
            // Wrap in paragraph
            .replace(/^(?!<)/, '<p>')
            .replace(/(?!>)$/, '</p>');
    }
});

// Funciones globales
function aiQuickAsk(question) {
    document.getElementById('ai-input').value = question;
    window.aiSendMessage?.();
}

async function aiFeedback(id, helpful, btn) {
    try {
        await fetch('/ai/feedback', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ interaction_id: id, was_helpful: helpful })
        });
        const parent = btn.parentElement;
        parent.querySelectorAll('.ai-fb').forEach(b => { b.disabled = true; b.classList.add('opacity-40', 'cursor-not-allowed'); });
        btn.innerHTML = helpful ? '✓ ¡Gracias!' : '✓ Registrado';
        btn.classList.remove('opacity-40');
    } catch (e) { console.error(e); }
}
</script>
