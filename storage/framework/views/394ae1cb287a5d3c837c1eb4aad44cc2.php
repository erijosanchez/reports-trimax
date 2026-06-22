
<script>
    (function () {
        const GPS_ENDPOINT = "<?php echo route('location.store-gps'); ?>";
        const PERM_ENDPOINT = "<?php echo route('location.report-permission'); ?>";
        const TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!TOKEN) return;

        const INTERVAL = <?php echo max(60, (int) config('security.session.track_interval', 300)); ?> * 1000;
        const MIN_GAP = 55 * 1000;
        let lastSent = 0;

        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': TOKEN,
            'Accept': 'application/json',
        };

        // Reporta el estado del permiso solo cuando cambia (evita spam de logs).
        function reportPermission(status) {
            if (localStorage.getItem('geo_perm_reported') === status) return;
            localStorage.setItem('geo_perm_reported', status);
            fetch(PERM_ENDPOINT, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ status: status }),
                keepalive: true,
            }).catch(function () {});
        }

        // Aviso persistente (no bloqueante) cuando la ubicación está desactivada.
        function toggleBanner(show) {
            let el = document.getElementById('geoPermBanner');
            if (show) {
                if (el || sessionStorage.getItem('geo_banner_hidden') === '1') return;
                el = document.createElement('div');
                el.id = 'geoPermBanner';
                el.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:2000;background:#fff3cd;' +
                    'color:#664d03;border-bottom:1px solid #ffe69c;padding:.6rem 1rem;font-size:.9rem;' +
                    'display:flex;align-items:center;gap:.5rem;box-shadow:0 2px 6px rgba(0,0,0,.08);';
                el.innerHTML =
                    '<i class="mdi mdi-map-marker-alert" style="font-size:1.2rem;"></i>' +
                    '<span style="flex:1;">Tu <strong>ubicación está desactivada</strong>. ' +
                    'Actívala en el candado 🔒 de la barra de direcciones del navegador para registrar tu ubicación.</span>' +
                    '<button type="button" id="geoBannerClose" style="background:none;border:none;font-size:1.1rem;cursor:pointer;color:#664d03;">&times;</button>';
                document.body.appendChild(el);
                document.getElementById('geoBannerClose').addEventListener('click', function () {
                    sessionStorage.setItem('geo_banner_hidden', '1');
                    el.remove();
                });
            } else if (el) {
                el.remove();
            }
        }

        function sendPosition(position) {
            const now = Date.now();
            if (now - lastSent < MIN_GAP) return;
            lastSent = now;
            fetch(GPS_ENDPOINT, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                }),
                keepalive: true,
            }).catch(function () {});
        }

        function onGranted(position) {
            reportPermission('granted');
            toggleBanner(false);
            if (position) sendPosition(position);
        }

        function onError(err) {
            // code 1 = PERMISSION_DENIED; 2 = POSITION_UNAVAILABLE; 3 = TIMEOUT
            if (err && err.code === 1) {
                reportPermission('denied');
                toggleBanner(true);
            }
        }

        function capture() {
            if (!('geolocation' in navigator)) {
                reportPermission('unavailable');
                return;
            }
            navigator.geolocation.getCurrentPosition(onGranted, onError, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 60000,
            });
        }

        // Si está disponible la Permissions API, reaccionamos a cambios en vivo.
        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then(function (perm) {
                const apply = function () {
                    if (perm.state === 'denied') {
                        reportPermission('denied');
                        toggleBanner(true);
                    } else {
                        // 'granted' o 'prompt' → intentamos capturar.
                        toggleBanner(false);
                        capture();
                    }
                };
                apply();
                perm.onchange = apply;
            }).catch(capture);
        } else {
            capture();
        }

        setInterval(function () {
            if (document.visibilityState === 'visible') capture();
        }, INTERVAL);
    })();
</script>
<?php /**PATH /var/www/resources/views/includes/gps-tracker.blade.php ENDPATH**/ ?>