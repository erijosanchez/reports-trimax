console.log('🌍 GPS Tracking iniciado');

document.addEventListener("DOMContentLoaded", function () {

    if (!navigator.geolocation) {
        console.log('❌ Geolocalización no soportada');
        return;
    }

    console.log('✅ Geolocalización soportada');

    // Esperar 2 segundos
    setTimeout(function () {
        console.log('📍 Solicitando GPS...');

        navigator.geolocation.getCurrentPosition(
            // ✅ ÉXITO
            function (position) {
                console.log('✅ GPS obtenido:', position.coords);

                const gpsData = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };

                console.log('📤 Enviando al servidor...');

                fetch('/gps-location', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(gpsData)
                })
                .then(response => {
                    console.log('📥 Respuesta:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('✅ Servidor:', data);

                    if (data.success) {
                        // ✅ Círculo verde de éxito
                        mostrarCirculo('success');
                    } else {
                        // ⚠️ Círculo amarillo de advertencia
                        mostrarCirculo('warning');
                    }
                })
                .catch(error => {
                    console.error('❌ Error:', error);
                    // ❌ Círculo rojo de error
                    mostrarCirculo('error');
                });
            },

            // ❌ ERROR GPS
            function (error) {
                console.log('❌ Error GPS:', error.code);
                // 🔵 Círculo azul - GPS no disponible
                mostrarCirculo('info');
            },

            // ⚙️ OPCIONES
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    }, 2000);
});

/**
 * Mostrar círculo de estado discreto
 */
function mostrarCirculo(tipo) {
    const colores = {
        'success': '#28a745',  // Verde
        'warning': '#ffc107',  // Amarillo
        'error': '#dc3545',    // Rojo
        'info': '#17a2b8'      // Azul
    };

    const iconos = {
        'success': '',
        'warning': '',
        'error': '',
        'info': ''
    };

    // Crear círculo
    const circulo = document.createElement('div');
    circulo.innerHTML = iconos[tipo];
    circulo.style.cssText = `
        position: fixed;
        bottom: 10px;
        right: 20px;
        width: 20px;
        height: 20px;
        background: ${colores[tipo]};
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 99999;
        animation: slideInBounce 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        cursor: default;
    `;

    document.body.appendChild(circulo);

    // Auto-remover después de 2.5 segundos
    setTimeout(() => {
        circulo.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => circulo.remove(), 300);
    }, 2500);
}

// CSS para animaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInBounce {
        0% {
            transform: translateX(100px) scale(0.5);
            opacity: 0;
        }
        50% {
            transform: translateX(-10px) scale(1.1);
        }
        100% {
            transform: translateX(0) scale(1);
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.8);
        }
    }
`;
document.head.appendChild(style);


