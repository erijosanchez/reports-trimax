<?php
// app/Http/Controllers/AssistantAIController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AssistantAIController extends Controller
{
    // Configuración de la API FREE LLM
    private $apiUrl = 'https://apifreellm.com/api/v1/chat';
    private $apiKey = 'apf_j1s2ql9cd5h2koe1jds3mz8m';

    public function chat(Request $request)
    {
        $message = $request->input('message');
        $history = $request->input('history', []);

        // PASO 1: Intentar respuesta local inteligente PRIMERO (más rápido)
        $localResponse = $this->getSmartLocalResponse($message);
        if ($localResponse) {
            return response()->json([
                'success' => true,
                'message' => $localResponse,
                'source' => 'local'
            ]);
        }

        // PASO 2: Verificar cache (evitar llamadas repetidas)
        $cacheKey = 'assistant_' . md5($message);
        if (Cache::has($cacheKey)) {
            return response()->json([
                'success' => true,
                'message' => Cache::get($cacheKey),
                'source' => 'cache'
            ]);
        }

        // PASO 3: Llamar a la API de IA
        try {
            $aiResponse = $this->callFreeLLMAPI($message, $history);

            if ($aiResponse) {
                // Guardar en cache por 30 minutos
                Cache::put($cacheKey, $aiResponse, 1800);

                return response()->json([
                    'success' => true,
                    'message' => $aiResponse,
                    'source' => 'ai'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FreeLLM API Error: ' . $e->getMessage());
        }

        // PASO 4: Fallback inteligente si la API falla
        $fallback = $this->getIntelligentFallback($message);

        return response()->json([
            'success' => true,
            'message' => $fallback,
            'source' => 'fallback'
        ]);
    }

    private function callFreeLLMAPI($userMessage, $history)
    {
        // Construir el prompt con contexto del sistema + historial
        $systemContext = $this->getSystemPrompt();

        // Agregar historial reciente (últimos 4 mensajes)
        $conversationContext = "";
        $recentHistory = array_slice($history, -4);
        foreach ($recentHistory as $msg) {
            $role = $msg['role'] === 'user' ? 'Usuario' : 'Asistente';
            $conversationContext .= "{$role}: {$msg['content']}\n";
        }

        // Construir mensaje completo
        $fullMessage = $systemContext . "\n\n";
        if ($conversationContext) {
            $fullMessage .= "Historial de conversación:\n{$conversationContext}\n";
        }
        $fullMessage .= "Usuario: {$userMessage}\nAsistente:";

        // Llamar a la API (respeta el rate limit de 5 segundos)
        $response = Http::timeout(15)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ])
            ->post($this->apiUrl, [
                'message' => $fullMessage,
                'model' => 'apifreellm' // opcional
            ]);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['success']) && $data['success'] === true) {
                return $data['response'];
            }
        }

        // Si la respuesta no es exitosa, loguear
        if ($response->status() === 429) {
            Log::warning('Rate limit alcanzado. Esperar 5 segundos.');
        } else {
            Log::error('FreeLLM API Error: ' . $response->body());
        }

        return null;
    }

    private function getSystemPrompt()
    {
        return "Eres el Asistente Virtual de Trimax, un sistema de gestión de ventas y óptica.

            PERSONALIDAD:
            - Amigable, cercano y con sentido del humor
            - Natural, como un compañero de trabajo
            - Cuando no sabes algo: 'Uy, esa no me la sé 🤔'
            - Usas emojis con moderación
            - Profesional pero no robótico

            CAPACIDADES:
            1. Gestión de usuarios (crear, editar, eliminar)
            2. Códigos QR para encuestas
            3. Asignar sedes a consultores
            4. Ver encuestas y estadísticas
            5. Consultas de ventas, clientes y productos
            6. Links de encuesta (copiar, regenerar)

            INSTRUCCIONES:
            - Respuestas claras y concisas
            - Listas numeradas para pasos
            - Si no sabes, sé honesto
            - Tono amigable y profesional";
    }

    private function getSmartLocalResponse($message)
    {
        $message = strtolower(trim($message));

        $patterns = [
            // Crear usuario
            '/crear|nuevo|agregar.*usuario/i' => "**👤 Crear nuevo usuario**\n\nTe explico paso a paso:\n\n**1️⃣** Ve al menú 'Gestión de Usuarios Marketing'\n**2️⃣** Clic en botón verde 'Nuevo Usuario'\n**3️⃣** Completa:\n  • Nombre\n  • Tipo (Consultor/Sede)\n  • Ubicación (si es sede)\n**4️⃣** Guardar y listo! 🎉\n\n💡 El link se genera automáticamente",

            // QR
            '/\bqr\b|codigo.*qr|generar.*qr/i' => "**📱 Generar código QR**\n\n**1️⃣** Busca al usuario en la tabla\n**2️⃣** Clic en botón gris con ícono QR\n**3️⃣** Se abre modal con el QR\n**4️⃣** Descarga con el botón\n\n💡 El QR contiene el link de encuesta",

            // Asignar sedes
            '/asign|vincular|asociar.*sede/i' => "**🏢 Asignar sedes**\n\n**1️⃣** Busca al consultor\n**2️⃣** Botón azul con ícono tienda 🏪\n**3️⃣** Selecciona sedes\n**4️⃣** Guardar asignación ✅\n\n💡 Puedes asignar múltiples sedes",

            // Ver encuestas
            '/ver.*encuesta|estadistica|metricas/i' => "**📊 Ver encuestas**\n\n**1️⃣** Clic en 'Ver Detalles' (ícono ojo 👁️)\n**2️⃣** Verás dashboard con estadísticas\n**3️⃣** Scroll para ver lista completa\n**4️⃣** Gráficos de tendencia 30 días\n\n💡 Puedes exportar los datos",

            // Regenerar link
            '/regener|renovar|nuevo.*link/i' => "**🔄 Regenerar link**\n\n⚠️ **Invalidará el link anterior**\n\n**1️⃣** Tres puntos (···)\n**2️⃣** 'Regenerar Link'\n**3️⃣** Confirmar\n**4️⃣** Nuevo link generado\n\n🚨 El link viejo deja de funcionar AL INSTANTE",

            // Activar/Desactivar
            '/activ|desactiv|encender|apagar|habilit/i' => "**⚡ Activar/Desactivar**\n\n**1️⃣** Botón de power ⚡\n**2️⃣** Estado cambia:\n  • 🟢 Verde = Activo\n  • 🔴 Rojo = Inactivo\n\n💡 Los inactivos NO reciben encuestas nuevas",

            // Copiar link
            '/copi.*link|portapapel|clipboard/i' => "**📋 Copiar link**\n\n**1️⃣** Columna 'Link de Encuesta'\n**2️⃣** Botón azul de copiar 📋\n**3️⃣** Link copiado automáticamente\n**4️⃣** Botón verde 2 seg ✅\n\n💡 Comparte por WhatsApp, email, etc.",

            // Eliminar
            '/elimin|borrar|quitar|remover/i' => "**🗑️ Eliminar usuario**\n\n⚠️ **NO SE PUEDE DESHACER**\n\n**1️⃣** Tres puntos (···)\n**2️⃣** 'Eliminar' (opción roja)\n**3️⃣** Confirmar\n\n🚨 Se eliminan TODAS las encuestas",

            // Buscar
            '/busc|filtr|encontr/i' => "**🔍 Buscar usuarios**\n\n**1️⃣** 'Mostrar' junto a 'Búsqueda y Filtros'\n**2️⃣** Buscar por: Nombre, Email, Ubicación\n**3️⃣** Filtrar por: Tipo, Estado\n**4️⃣** Clic en 'Buscar'\n\n💡 Combina filtros para precisión",

            // Vista previa
            '/vista.*previa|preview/i' => "**👁️ Vista previa**\n\n**1️⃣** Tres puntos (···)\n**2️⃣** 'Vista Previa'\n**3️⃣** Se abre en nueva pestaña\n\n💡 Verifica antes de compartir",

            // Ventas
            '/venta|factura|documento/i' => "**💰 Consultas de ventas**\n\nPuedo mostrarte:\n📊 Ventas del mes\n👥 Top clientes\n📦 Productos más vendidos\n🏢 Por sede/zona\n💵 Facturación total\n\n¿Qué necesitas?",

            // Saludo
            '/^(hola|hey|hi|buenos|buenas|que tal|ola)/i' => "**👋 ¡Hola!**\n\n¡Qué tal! Soy Trimax AI 🤖\n\n**Puedo ayudarte:**\n\n📋 Gestión de usuarios\n📱 Códigos QR\n🏢 Asignar sedes\n📊 Ver encuestas\n💰 Consultas de ventas\n\n¿En qué te ayudo? 😊",

            // Ayuda
            '/ayuda|help|que.*hacer|que.*sabes/i' => "**❓ Mis capacidades**\n\n**📋 GESTIÓN:**\n• Crear/editar usuarios\n• Generar QR\n• Asignar sedes\n• Links encuesta\n\n**📊 CONSULTAS:**\n• Ventas y facturación\n• Clientes\n• Productos\n• Estadísticas\n\n**🔍 BÚSQUEDAS:**\n• Filtrar usuarios\n• Buscar datos\n\n¿Con qué te ayudo? 🚀"
        ];

        foreach ($patterns as $pattern => $response) {
            if (preg_match($pattern, $message)) {
                return $response;
            }
        }

        return null;
    }

    private function getIntelligentFallback($message)
    {
        $message = strtolower($message);

        // Fallbacks específicos
        if (strpos($message, 'crear') !== false || strpos($message, 'nuevo') !== false) {
            return "Para **crear algo nuevo**, puedo ayudarte con:\n\n• 👤 Crear usuarios\n• 📱 Generar códigos QR\n• 🔗 Crear links de encuesta\n\n¿Qué necesitas crear?";
        }

        if (strpos($message, 'ver') !== false || strpos($message, 'mostrar') !== false) {
            return "Para **ver información**, puedo mostrarte:\n\n• 📊 Encuestas y estadísticas\n• 💰 Ventas y facturación\n• 👥 Datos de clientes\n• 📦 Productos\n\n¿Qué quieres consultar?";
        }

        if (strpos($message, 'como') !== false || strpos($message, 'cómo') !== false) {
            return "Te puedo explicar **cómo hacer**:\n\n• Crear usuarios\n• Generar QR\n• Asignar sedes\n• Ver encuestas\n• Copiar links\n• Filtrar datos\n\n¿Qué proceso te explico?";
        }

        // Fallback genérico con personalidad
        return "Mmm... 🤔 Esa pregunta me la pone difícil, todavía estoy aprendiendo sobre eso jajaja.\n\nPero soy pro en:\n• 👤 Gestión de usuarios\n• 📱 Códigos QR\n• 📊 Encuestas\n• 💰 Consultas de ventas\n\n¿Te ayudo con algo de esto? 😊";
    }
}
