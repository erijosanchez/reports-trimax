<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mis entregas — {{ $ruta->fecha->format('d/m/Y') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.2.96/css/materialdesignicons.min.css">
    <style>
        body { background: #f0f2f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

        .header-top {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: #fff; padding: 16px 20px;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
        }

        .progress-bar-wrap { height: 6px; background: rgba(255,255,255,.3); border-radius: 3px; }
        .progress-bar-fill { height: 6px; background: #4cff91; border-radius: 3px; transition: width .4s; }

        .parada-card {
            border-radius: 14px; overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            transition: transform .1s;
        }
        .parada-card:active { transform: scale(.98); }

        .parada-numero {
            width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; flex-shrink: 0; color: #fff;
        }

        .btn-entregar {
            border-radius: 50px; font-weight: 700; font-size: 15px;
            padding: 12px 0; width: 100%;
            box-shadow: 0 3px 10px rgba(40,167,69,.35);
        }
        .btn-fallido {
            border-radius: 50px; font-weight: 600; font-size: 14px;
            padding: 10px 0; width: 100%;
        }

        .estado-badge {
            font-size: 13px; padding: 5px 12px; border-radius: 20px; font-weight: 600;
        }

        .card-completado { border-left: 5px solid #28a745 !important; opacity: .75; }
        .card-fallido    { border-left: 5px solid #dc3545 !important; opacity: .75; }
        .card-pendiente  { border-left: 5px solid #dee2e6; }
        .card-en_camino  { border-left: 5px solid #ffc107; }

        .direccion-link { color: #1a73e8; text-decoration: none; font-size: 13px; }
        .direccion-link:hover { text-decoration: underline; }

        .spinner-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.45); z-index: 9999;
            align-items: center; justify-content: center;
        }
        .spinner-overlay.active { display: flex; }

        .resumen-final {
            display: none; background: #fff; border-radius: 16px;
            padding: 28px 20px; text-align: center; margin: 20px 0;
        }
        .resumen-final.show { display: block; }
    </style>
</head>
<body>

{{-- Header sticky --}}
<div class="header-top">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <div style="font-size:13px;opacity:.8">Trimax — Ruta de entregas</div>
            <div class="fw-bold" style="font-size:17px">{{ $ruta->motorizado->nombre }}</div>
            <div style="font-size:12px;opacity:.8">
                {{ $ruta->fecha->format('d/m/Y') }} · {{ $ruta->motorizado->sede }}
            </div>
        </div>
        <div class="text-end">
            <div id="badge-ruta" class="badge bg-warning text-dark" style="font-size:12px">
                {{ ucfirst(str_replace('_',' ', $ruta->estado)) }}
            </div>
            <div style="font-size:12px;opacity:.8;margin-top:4px">
                <span id="cont-completadas">{{ $ruta->paradas->where('estado','completado')->count() }}</span>
                /{{ $ruta->paradas->count() }} entregas
            </div>
        </div>
    </div>
    <div class="progress-bar-wrap">
        @php
            $pct = $ruta->paradas->count()
                ? round($ruta->paradas->where('estado','completado')->count() / $ruta->paradas->count() * 100)
                : 0;
        @endphp
        <div class="progress-bar-fill" id="barra-progreso" style="width:{{ $pct }}%"></div>
    </div>
</div>

<div class="container-fluid px-3 py-3" style="max-width:600px;margin:0 auto">

    {{-- Resumen cuando se completa todo --}}
    <div class="resumen-final {{ $pct === 100 ? 'show' : '' }}" id="resumen-final">
        <i class="mdi mdi-check-circle text-success" style="font-size:56px"></i>
        <h4 class="fw-bold mt-2 mb-1">¡Ruta completada!</h4>
        <p class="text-muted">Todas las entregas han sido registradas.</p>
    </div>

    @forelse($ruta->paradas as $parada)
    @php
        $orden  = $parada->orden;
        $colores = ['completado'=>'#28a745','fallido'=>'#dc3545','en_camino'=>'#ffc107','pendiente'=>'#1a73e8'];
        $color   = $colores[$parada->estado] ?? '#1a73e8';
        $mapsUrl = $orden->latitud && $orden->longitud
            ? "https://maps.google.com/?q={$orden->latitud},{$orden->longitud}"
            : "https://maps.google.com/?q=" . urlencode($orden->direccion);
    @endphp

    <div class="card parada-card border-0 mb-3 card-{{ $parada->estado }}"
         id="card-{{ $parada->id }}" data-estado="{{ $parada->estado }}">
        <div class="card-body p-3">

            {{-- Número + info --}}
            <div class="d-flex gap-3 align-items-start mb-3">
                <div class="parada-numero" style="background:{{ $color }}" id="num-{{ $parada->id }}">
                    {{ $parada->orden_secuencia }}
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold" style="font-size:16px">{{ $orden->cliente_nombre }}</div>
                    @if($orden->cliente_telefono)
                    <a href="tel:{{ $orden->cliente_telefono }}" class="text-muted small d-block">
                        <i class="mdi mdi-phone me-1"></i>{{ $orden->cliente_telefono }}
                    </a>
                    @endif
                    @if($orden->referencia)
                    <div class="text-muted small"><i class="mdi mdi-tag me-1"></i>{{ $orden->referencia }}</div>
                    @endif
                </div>
                <div id="badge-estado-{{ $parada->id }}">
                    @if($parada->estado === 'completado')
                        <span class="estado-badge bg-success text-white">
                            <i class="mdi mdi-check"></i> Entregado
                        </span>
                    @elseif($parada->estado === 'fallido')
                        <span class="estado-badge bg-danger text-white">
                            <i class="mdi mdi-close"></i> Fallido
                        </span>
                    @else
                        <span class="estado-badge bg-light text-secondary border">Pendiente</span>
                    @endif
                </div>
            </div>

            {{-- Dirección con link a Maps --}}
            <a href="{{ $mapsUrl }}" target="_blank" class="direccion-link d-flex align-items-start gap-1 mb-3">
                <i class="mdi mdi-map-marker mt-1" style="flex-shrink:0"></i>
                <span>{{ $orden->direccion }}</span>
            </a>

            {{-- Hora entrega si ya está --}}
            @if($parada->hora_salida)
            <div class="text-muted small mb-2">
                <i class="mdi mdi-clock-check me-1"></i>
                Entregado a las {{ $parada->hora_salida->format('H:i') }}
            </div>
            @endif

            {{-- Botones de acción (ocultos si ya tiene estado final) --}}
            <div id="acciones-{{ $parada->id }}"
                 class="{{ in_array($parada->estado, ['completado','fallido']) ? 'd-none' : '' }}">

                {{-- Campo de notas (colapsable) --}}
                <div class="mb-2" id="notas-wrap-{{ $parada->id }}" style="display:none">
                    <textarea id="notas-{{ $parada->id }}" class="form-control form-control-sm" rows="2"
                              placeholder="Motivo / observación…"></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-success btn-entregar"
                            onclick="marcar({{ $parada->id }}, 'completado', '{{ $token }}')">
                        <i class="mdi mdi-check-bold me-1"></i>Entregado
                    </button>
                    <button class="btn btn-outline-danger btn-fallido"
                            style="width:auto;padding:12px 14px"
                            onclick="toggleNotas({{ $parada->id }})">
                        <i class="mdi mdi-dots-vertical"></i>
                    </button>
                </div>

                <div id="btn-fallido-wrap-{{ $parada->id }}" style="display:none" class="mt-2">
                    <button class="btn btn-outline-danger btn-fallido"
                            onclick="marcar({{ $parada->id }}, 'fallido', '{{ $token }}')">
                        <i class="mdi mdi-close-circle me-1"></i>No entregado / Nadie en casa
                    </button>
                </div>
            </div>

        </div>
    </div>
    @empty
        <div class="text-center text-muted py-5">
            <i class="mdi mdi-package-variant mdi-48px d-block mb-2"></i>
            Sin paradas para esta ruta
        </div>
    @endforelse

</div>

{{-- Spinner de carga --}}
<div class="spinner-overlay" id="spinner">
    <div class="text-center text-white">
        <div class="spinner-border mb-2" style="width:2.5rem;height:2.5rem"></div>
        <div class="fw-semibold">Guardando…</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const COLORES = { completado: '#28a745', fallido: '#dc3545', en_camino: '#ffc107', pendiente: '#1a73e8' };
const totalParadas = {{ $ruta->paradas->count() }};
let completadas = {{ $ruta->paradas->where('estado','completado')->count() }};

function toggleNotas(paradaId) {
    const wrap    = document.getElementById(`notas-wrap-${paradaId}`);
    const btnFall = document.getElementById(`btn-fallido-wrap-${paradaId}`);
    const show    = wrap.style.display === 'none';
    wrap.style.display    = show ? 'block' : 'none';
    btnFall.style.display = show ? 'block' : 'none';
}

async function marcar(paradaId, estado, token) {
    const spinner = document.getElementById('spinner');
    spinner.classList.add('active');

    const notas = document.getElementById(`notas-${paradaId}`)?.value || '';
    const csrf  = document.querySelector('meta[name="csrf-token"]').content;

    try {
        const res = await fetch(`/entrega/${token}/parada/${paradaId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ estado, notas }),
        });

        if (!res.ok) throw new Error();
        const data = await res.json();

        // Actualizar card visualmente
        const card   = document.getElementById(`card-${paradaId}`);
        const num    = document.getElementById(`num-${paradaId}`);
        const badge  = document.getElementById(`badge-estado-${paradaId}`);
        const acciones = document.getElementById(`acciones-${paradaId}`);

        card.className  = `card parada-card border-0 mb-3 card-${estado}`;
        num.style.background = COLORES[estado];
        acciones.classList.add('d-none');

        if (estado === 'completado') {
            completadas++;
            badge.innerHTML = `<span class="estado-badge bg-success text-white"><i class="mdi mdi-check"></i> Entregado</span>`;
        } else {
            badge.innerHTML = `<span class="estado-badge bg-danger text-white"><i class="mdi mdi-close"></i> Fallido</span>`;
        }

        // Actualizar barra de progreso
        const pct = Math.round(completadas / totalParadas * 100);
        document.getElementById('barra-progreso').style.width = pct + '%';
        document.getElementById('cont-completadas').textContent = completadas;

        // Badge ruta
        if (data.ruta_estado === 'completado') {
            document.getElementById('badge-ruta').className = 'badge bg-success';
            document.getElementById('badge-ruta').textContent = 'Completada';
            document.getElementById('resumen-final').classList.add('show');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else if (data.ruta_estado === 'en_ruta') {
            document.getElementById('badge-ruta').className = 'badge bg-warning text-dark';
            document.getElementById('badge-ruta').textContent = 'En ruta';
        }

        // Vibrar si disponible (feedback táctil)
        if (navigator.vibrate) navigator.vibrate(estado === 'completado' ? [50, 30, 50] : [100]);

    } catch {
        alert('Error al guardar. Verifica tu conexión.');
    } finally {
        spinner.classList.remove('active');
    }
}
</script>
</body>
</html>
