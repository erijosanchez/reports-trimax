{{-- AI Assistant - Bootstrap version --}}

<div id="ai-assistant" style="position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 9999; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;">

    {{-- Botón flotante --}}
    <button id="ai-toggle"
        style="background: linear-gradient(135deg, #2563eb, #4338ca); border: none; border-radius: 1rem; padding: 0.75rem; color: white; box-shadow: 0 8px 25px rgba(37,99,235,0.35); transition: all 0.3s; cursor: pointer;"
        onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 10px 30px rgba(37,99,235,0.45)';"
        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 8px 25px rgba(37,99,235,0.35)';"
        onmousedown="this.style.transform='scale(0.95)';"
        onmouseup="this.style.transform='scale(1.05)';">
        <svg id="ai-icon-chat" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <svg id="ai-icon-close" class="d-none" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    {{-- Panel del chat --}}
    <div id="ai-chat-panel" class="d-none" style="position: absolute; right: 0; bottom: 4rem; width: 400px; height: 580px; background: white; border-radius: 1.25rem; box-shadow: 0 20px 60px rgba(0,0,0,0.15); border: 1px solid #f1f5f9; display: flex; flex-direction: column; overflow: hidden; animation: aiSlideUp 0.25s cubic-bezier(0.16,1,0.3,1);">

        {{-- Header --}}
        <div style="background: linear-gradient(90deg, #2563eb, #4338ca); padding: 0.75rem 1rem; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; color: white;">
            <div class="d-flex align-items-center gap-2">
                <div style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); border-radius: 0.5rem; width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.875rem;">T</div>
                <div>
                    <div style="font-weight: 600; font-size: 0.875rem; line-height: 1.2;">Asistente Trimax</div>
                    <div class="d-flex align-items-center gap-1">
                        <span id="ai-status-dot" style="width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block;"></span>
                        <span id="ai-status-text" style="font-size: 11px; color: #bfdbfe;">En línea</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button id="ai-clear" title="Limpiar conversación"
                    style="background: transparent; border: none; color: white; padding: 0.375rem; border-radius: 0.5rem; cursor: pointer; line-height: 0; transition: background 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='transparent'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                <button id="ai-close"
                    style="background: transparent; border: none; color: white; padding: 0.375rem; border-radius: 0.5rem; cursor: pointer; line-height: 0; transition: background 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.15)'" onmouseout="this.style.background='transparent'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mensajes --}}
        <div id="ai-messages" style="flex: 1; overflow-y: auto; padding: 1rem; background: linear-gradient(to bottom, #f9fafb, #ffffff); display: flex; flex-direction: column; gap: 0.75rem;">
            <div class="ai-msg assistant">
                <div style="background: white; border: 1px solid #f3f4f6; border-radius: 1rem 1rem 1rem 0.25rem; padding: 0.875rem; max-width: 85%; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                    <p style="font-size: 13px; color: #1f2937; line-height: 1.6; margin: 0;">¡Hola <strong>{{ auth()->user()->name ?? 'Usuario' }}</strong>! 👋 Soy tu asistente del CRM Trimax.</p>
                    <p style="font-size: 13px; color: #4b5563; margin: 0.5rem 0 0;">¿En qué puedo ayudarte?</p>
                </div>
            </div>
            <div id="ai-suggestions" style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0 0.25rem;">
                <button onclick="aiQuickAsk('¿Cuál es el resumen general del sistema?')" class="ai-chip">📊 Resumen general</button>
                <button onclick="aiQuickAsk('¿Cómo van las ventas de este mes?')" class="ai-chip">💰 Ventas del mes</button>
                <button onclick="aiQuickAsk('¿Cuáles son las mejores sedes en ventas?')" class="ai-chip">🏆 Top sedes</button>
                <button onclick="aiQuickAsk('¿Cuántos descuentos están pendientes?')" class="ai-chip">🏷️ Descuentos</button>
                <button onclick="aiQuickAsk('¿Cuántos acuerdos comerciales están vigentes?')" class="ai-chip">📋 Acuerdos</button>
            </div>
        </div>

        {{-- Input --}}
        <div style="flex-shrink: 0; background: white; padding: 0.75rem; border-top: 1px solid #f3f4f6;">
            <div class="d-flex align-items-end gap-2">
                <textarea id="ai-input" rows="1" placeholder="Escribe tu pregunta..."
                    style="flex: 1; padding: 0.625rem 0.875rem; border: 1px solid #e5e7eb; border-radius: 0.75rem; font-size: 13px; resize: none; max-height: 80px; outline: none; transition: border-color 0.2s, box-shadow 0.2s; color: #374151; font-family: inherit;"
                    onfocus="this.style.borderColor='#60a5fa'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.2)'"
                    onblur="this.style.borderColor='#e5e7eb'; this.style.boxShadow='none'"></textarea>
                <button id="ai-send"
                    style="flex-shrink: 0; background: #2563eb; border: none; border-radius: 0.75rem; padding: 0.625rem; color: white; cursor: pointer; line-height: 0; transition: all 0.2s;"
                    onmouseover="this.style.background='#1d4ed8'"
                    onmouseout="this.style.background='#2563eb'"
                    onmousedown="this.style.transform='scale(0.95)'"
                    onmouseup="this.style.transform='scale(1)'">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes aiSlideUp {
    from { opacity: 0; transform: translateY(12px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.ai-msg.user     { display: flex; justify-content: flex-end; }
.ai-msg.assistant{ display: flex; justify-content: flex-start; }
.ai-msg .msg-content p       { margin-bottom: 0.4em; }
.ai-msg .msg-content p:last-child { margin-bottom: 0; }
.ai-msg .msg-content strong  { font-weight: 600; }
.ai-msg .msg-content ul,
.ai-msg .msg-content ol      { padding-left: 1.2em; margin: 0.3em 0; }
.ai-msg .msg-content li      { margin-bottom: 0.15em; }
.ai-msg .msg-content table   { width: 100%; border-collapse: collapse; margin: 0.4em 0; font-size: 0.88em; }
.ai-msg .msg-content th,
.ai-msg .msg-content td      { border: 1px solid #e5e7eb; padding: 3px 6px; text-align: left; }
.ai-msg .msg-content th      { background: #f3f4f6; font-weight: 600; }

.ai-chip {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    padding: 0.25rem 0.75rem;
    font-size: 11px;
    color: #1d4ed8;
    cursor: pointer;
    transition: background 0.2s;
    font-family: inherit;
}
.ai-chip:hover { background: #dbeafe; }

.ai-fb-btn {
    background: transparent;
    border: none;
    font-size: 11px;
    color: #9ca3af;
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.ai-fb-btn:hover { background: #f0fdf4; color: #16a34a; }
.ai-fb-btn.negative:hover { background: #fef2f2; color: #ef4444; }

.typing-dot {
    width: 8px; height: 8px;
    background: #60a5fa;
    border-radius: 50%;
    animation: typeBounce 1.4s ease-in-out infinite;
    display: inline-block;
}
.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes typeBounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30%            { transform: translateY(-4px); opacity: 1; }
}

#ai-messages::-webkit-scrollbar       { width: 4px; }
#ai-messages::-webkit-scrollbar-track  { background: transparent; }
#ai-messages::-webkit-scrollbar-thumb  { background: #d1d5db; border-radius: 4px; }

#ai-send:disabled { background: #d1d5db !important; cursor: not-allowed; }

@media (max-width: 576px) {
    #ai-chat-panel {
        width: calc(100vw - 24px) !important;
        height: calc(100vh - 100px) !important;
        right: -8px !important;
        bottom: 56px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle    = document.getElementById('ai-toggle');
    const panel     = document.getElementById('ai-chat-panel');
    const close     = document.getElementById('ai-close');
    const clear     = document.getElementById('ai-clear');
    const input     = document.getElementById('ai-input');
    const send      = document.getElementById('ai-send');
    const messages  = document.getElementById('ai-messages');
    const statusDot = document.getElementById('ai-status-dot');
    const statusText= document.getElementById('ai-status-text');
    const iconChat  = document.getElementById('ai-icon-chat');
    const iconClose = document.getElementById('ai-icon-close');

    let sessionId = localStorage.getItem('ai_session_id') || Date.now().toString();
    localStorage.setItem('ai_session_id', sessionId);
    let isOpen = false;
    let isSending = false;

    // Toggle
    toggle.addEventListener('click', () => {
        isOpen = !isOpen;
        panel.classList.toggle('d-none', !isOpen);
        if (isOpen) { panel.style.display = 'flex'; }
        iconChat.classList.toggle('d-none', isOpen);
        iconClose.classList.toggle('d-none', !isOpen);
        if (isOpen) input.focus();
    });

    close.addEventListener('click', () => {
        isOpen = false;
        panel.classList.add('d-none');
        panel.style.display = '';
        iconChat.classList.remove('d-none');
        iconClose.classList.add('d-none');
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
            bubble.style.cssText = 'background:#2563eb; color:white; border-radius:1rem 1rem 0.25rem 1rem; padding:0.75rem; max-width:85%; box-shadow:0 1px 3px rgba(0,0,0,0.1);';
            bubble.innerHTML = `<p style="font-size:13px;line-height:1.6;margin:0;">${escHtml(text)}</p>`;
        } else {
            bubble.style.cssText = 'background:white; border:1px solid #f3f4f6; border-radius:1rem 1rem 1rem 0.25rem; padding:0.875rem; max-width:85%; box-shadow:0 1px 3px rgba(0,0,0,0.06);';
            bubble.innerHTML = `<div style="font-size:13px;color:#1f2937;line-height:1.6;" class="msg-content">${renderMd(text)}</div>`;

            if (toolsUsed.length > 0) {
                bubble.insertAdjacentHTML('beforeend', `<div class="mt-2"><span style="display:inline-flex;align-items:center;gap:4px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:999px;padding:2px 10px;font-size:10px;color:#065f46;">✓ Datos verificados</span></div>`);
            }

            if (interactionId) {
                bubble.insertAdjacentHTML('beforeend', `
                    <div style="display:flex;gap:0.5rem;margin-top:0.625rem;padding-top:0.5rem;border-top:1px solid #f9fafb;">
                        <button onclick="aiFeedback(${interactionId}, true, this)" class="ai-fb-btn">👍 Útil</button>
                        <button onclick="aiFeedback(${interactionId}, false, this)" class="ai-fb-btn negative">👎 Mejorar</button>
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
                <div style="background:white;border:1px solid #f3f4f6;border-radius:1rem 1rem 1rem 0.25rem;padding:0.875rem;max-width:85%;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                    <p style="font-size:13px;color:#1f2937;margin:0;">¡Hola <strong>${name}</strong>! 👋 Conversación limpia.</p>
                    <p style="font-size:13px;color:#4b5563;margin:0.25rem 0 0;">¿En qué puedo ayudarte?</p>
                </div>
            </div>
            <div id="ai-suggestions" style="display:flex;flex-wrap:wrap;gap:0.5rem;padding:0 0.25rem;">
                <button onclick="aiQuickAsk('¿Cuál es el resumen general?')" class="ai-chip">📊 Resumen</button>
                <button onclick="aiQuickAsk('¿Cómo van las ventas?')" class="ai-chip">💰 Ventas</button>
                <button onclick="aiQuickAsk('Top sedes en ventas')" class="ai-chip">🏆 Top sedes</button>
            </div>
        `);
    }

    function showTyping() {
        const id = 'typ-' + Date.now();
        messages.insertAdjacentHTML('beforeend', `
            <div id="${id}" class="ai-msg assistant">
                <div style="background:white;border:1px solid #f3f4f6;border-radius:1rem 1rem 1rem 0.25rem;padding:0.875rem;max-width:85%;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                    <div style="display:flex;align-items:center;gap:6px;height:20px;">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            </div>
        `);
        messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });
        return id;
    }

    function hideTyping(id) { document.getElementById(id)?.remove(); }

    function setStatus(s) {
        const map = {
            online:   { bg: '#4ade80', pulse: false, text: 'En línea' },
            thinking: { bg: '#facc15', pulse: true,  text: 'Consultando datos...' },
            error:    { bg: '#f87171', pulse: false,  text: 'Error' }
        };
        const cfg = map[s] || map.online;
        statusDot.style.background = cfg.bg;
        statusDot.style.animation = cfg.pulse ? 'pulse 1s infinite' : 'none';
        statusText.textContent = cfg.text;
    }

    function getModule() {
        const p = window.location.pathname;
        if (p.includes('descuento'))               return 'descuentos';
        if (p.includes('convenio') || p.includes('acuerdo')) return 'convenios';
        if (p.includes('orden'))                   return 'ordenes';
        if (p.includes('dashboard'))               return 'dashboard';
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
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`(.*?)`/g, '<code>$1</code>')
            .replace(/^[-•]\s+(.+)/gm, '<li>$1</li>')
            .replace(/^\d+\.\s+(.+)/gm, '<li>$1</li>')
            .replace(/((?:<li>.*<\/li>\s*)+)/g, '<ul>$1</ul>')
            .replace(/^### (.+)/gm, '<strong style="font-size:1em;">$1</strong>')
            .replace(/^## (.+)/gm,  '<strong style="font-size:1.05em;">$1</strong>')
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>')
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
        parent.querySelectorAll('.ai-fb-btn').forEach(b => { b.disabled = true; b.style.opacity = '0.4'; b.style.cursor = 'not-allowed'; });
        btn.textContent = helpful ? '✓ ¡Gracias!' : '✓ Registrado';
        btn.style.opacity = '1';
    } catch (e) { console.error(e); }
}
</script>