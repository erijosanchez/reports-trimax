{{-- resources/views/firma/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Mi Firma Digital')

@section('content')
    <div class="content-wrapper">
        <div class="row justify-content-center">
            <div class="col-lg-7">

                <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom">
                    <h3 class="mb-0">
                        <i class="mdi mdi-draw"></i> Mi Firma Digital
                    </h3>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="mdi mdi-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Firma actual --}}
                @if ($usuario->tieneFirmaRegistrada())
                    <div class="shadow-sm mb-4 card">
                        <div class="card-body">
                            <h6 class="mb-3 text-success">
                                <i class="mdi mdi-check-circle"></i> Firma registrada
                            </h6>
                            <div class="border rounded p-3 bg-light text-center" style="min-height:90px;">
                                <img src="{{ $usuario->firma_imagen }}" alt="Mi firma" style="max-height:80px; max-width:300px;">
                            </div>
                            <form method="POST" action="{{ route('firma.destroy') }}" class="mt-3 d-inline"
                                onsubmit="return confirm('¿Eliminar tu firma registrada?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="mdi mdi-delete"></i> Eliminar firma
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Tabs: Dibujar / Subir imagen --}}
                <div class="shadow-sm card">
                    <div class="card-body">
                        <h6 class="mb-3 text-primary">
                            <i class="mdi mdi-pencil"></i>
                            {{ $usuario->tieneFirmaRegistrada() ? 'Reemplazar firma' : 'Registrar firma' }}
                        </h6>

                        <ul class="nav nav-tabs mb-3" id="firmaTabs">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabDibujar">
                                    <i class="mdi mdi-draw"></i> Dibujar
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSubir">
                                    <i class="mdi mdi-upload"></i> Subir imagen
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">

                            {{-- TAB 1: Dibujar --}}
                            <div class="tab-pane fade show active" id="tabDibujar">
                                <p class="text-muted small mb-3">
                                    Usa el mouse o toca la pantalla para firmar.
                                </p>
                                <div class="border rounded mb-3" style="background:#fff; cursor:crosshair;">
                                    <canvas id="firmaCanvas" width="620" height="160"
                                        style="width:100%; display:block; touch-action:none;"></canvas>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <button type="button" id="btnLimpiar" class="btn btn-outline-secondary btn-sm">
                                        <i class="mdi mdi-eraser"></i> Limpiar
                                    </button>
                                    <div class="ms-auto d-flex align-items-center gap-2">
                                        <small class="text-muted">Color:</small>
                                        <input type="color" id="colorPicker" value="#000000" style="width:32px; height:28px; padding:1px; border-radius:4px;">
                                        <small class="text-muted">Grosor:</small>
                                        <input type="range" id="grosor" min="1" max="6" value="2" style="width:70px;">
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('firma.store') }}" id="formDibujar">
                                    @csrf
                                    <input type="hidden" name="firma_data" id="firmaDataDibujar">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="mdi mdi-content-save"></i> Guardar firma dibujada
                                    </button>
                                </form>
                            </div>

                            {{-- TAB 2: Subir imagen --}}
                            <div class="tab-pane fade" id="tabSubir">
                                <p class="text-muted small mb-3">
                                    Sube tu firma escaneada en formato <strong>PNG, JPG o GIF</strong> con fondo blanco o transparente.
                                    Tamaño máximo: 2 MB.
                                </p>

                                <div id="dropZone" class="border rounded p-4 text-center mb-3"
                                    style="border-style:dashed!important; cursor:pointer; background:#fafafa; transition: background .2s;">
                                    <i class="mdi mdi-cloud-upload mdi-36px text-muted d-block mb-2"></i>
                                    <p class="mb-1 text-muted">Arrastra tu imagen aquí o haz clic para seleccionar</p>
                                    <small class="text-muted">PNG, JPG, GIF — máx. 2 MB</small>
                                    <input type="file" id="firmaFile" accept="image/png,image/jpeg,image/gif"
                                        style="position:absolute;opacity:0;inset:0;cursor:pointer;">
                                </div>

                                {{-- Preview --}}
                                <div id="previewZone" class="d-none border rounded p-3 bg-light text-center mb-3">
                                    <img id="previewImg" src="" alt="preview" style="max-height:90px; max-width:100%;">
                                    <div class="mt-2">
                                        <button type="button" id="btnCambiarImg" class="btn btn-sm btn-outline-secondary">
                                            <i class="mdi mdi-refresh"></i> Cambiar imagen
                                        </button>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('firma.store') }}" id="formSubir">
                                    @csrf
                                    <input type="hidden" name="firma_data" id="firmaDataSubir">
                                    <button type="submit" class="btn btn-primary w-100" id="btnGuardarImg" disabled>
                                        <i class="mdi mdi-content-save"></i> Guardar firma subida
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="mt-3 text-muted small">
                    <i class="mdi mdi-shield-lock-outline"></i>
                    Tu firma se almacena de forma segura y solo se usa en documentos que tú autoricéis.
                </div>

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .shadow-sm { box-shadow: 0 2px 15px rgba(0,0,0,.08) !important; border:none!important; border-radius:10px!important; }
        #dropZone:hover, #dropZone.dragover { background: #e8f4ff !important; border-color: #4e73df !important; }
        #firmaCanvas { border-radius: 0 0 6px 6px; }
    </style>
@endpush

@push('scripts')
<script>
(function () {
    /* ── CANVAS: Dibujar ── */
    const canvas  = document.getElementById('firmaCanvas');
    const ctx     = canvas.getContext('2d');
    const grosor  = document.getElementById('grosor');
    const picker  = document.getElementById('colorPicker');
    let drawing   = false, lastX = 0, lastY = 0;

    function ratio() { return canvas.width / canvas.offsetWidth; }
    function pos(e) {
        const r = canvas.getBoundingClientRect();
        const s = e.touches ? e.touches[0] : e;
        return [(s.clientX - r.left) * ratio(), (s.clientY - r.top) * ratio()];
    }

    ctx.lineJoin = 'round';
    ctx.lineCap  = 'round';

    canvas.addEventListener('mousedown',  e => { drawing = true; [lastX, lastY] = pos(e); });
    canvas.addEventListener('mousemove',  e => {
        if (!drawing) return;
        ctx.strokeStyle = picker.value;
        ctx.lineWidth   = grosor.value;
        ctx.beginPath(); ctx.moveTo(lastX, lastY);
        [lastX, lastY] = pos(e);
        ctx.lineTo(lastX, lastY); ctx.stroke();
    });
    canvas.addEventListener('mouseup',    () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);
    canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; [lastX, lastY] = pos(e); }, { passive:false });
    canvas.addEventListener('touchmove',  e => {
        e.preventDefault();
        if (!drawing) return;
        ctx.strokeStyle = picker.value;
        ctx.lineWidth   = grosor.value;
        ctx.beginPath(); ctx.moveTo(lastX, lastY);
        [lastX, lastY] = pos(e);
        ctx.lineTo(lastX, lastY); ctx.stroke();
    }, { passive:false });
    canvas.addEventListener('touchend', () => drawing = false);

    document.getElementById('btnLimpiar').addEventListener('click', () => ctx.clearRect(0, 0, canvas.width, canvas.height));

    document.getElementById('formDibujar').addEventListener('submit', function (e) {
        const blank = document.createElement('canvas');
        blank.width = canvas.width; blank.height = canvas.height;
        if (canvas.toDataURL() === blank.toDataURL()) {
            e.preventDefault();
            alert('Por favor dibuja tu firma antes de guardar.');
            return;
        }
        document.getElementById('firmaDataDibujar').value = canvas.toDataURL('image/png');
    });

    /* ── UPLOAD: Subir imagen ── */
    const dropZone   = document.getElementById('dropZone');
    const fileInput  = document.getElementById('firmaFile');
    const previewZ   = document.getElementById('previewZone');
    const previewImg = document.getElementById('previewImg');
    const btnGuardar = document.getElementById('btnGuardarImg');
    const btnCambiar = document.getElementById('btnCambiarImg');
    const firmaDataSubir = document.getElementById('firmaDataSubir');

    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault(); dropZone.classList.remove('dragover');
        if (e.dataTransfer.files[0]) processFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', () => { if (fileInput.files[0]) processFile(fileInput.files[0]); });
    btnCambiar.addEventListener('click', () => {
        previewZ.classList.add('d-none');
        dropZone.classList.remove('d-none');
        fileInput.value = '';
        firmaDataSubir.value = '';
        btnGuardar.disabled = true;
    });

    function processFile(file) {
        if (!file.type.match(/^image\/(png|jpeg|gif)$/)) {
            alert('Solo se aceptan PNG, JPG o GIF.'); return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('La imagen supera 2 MB.'); return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            firmaDataSubir.value = e.target.result;
            dropZone.classList.add('d-none');
            previewZ.classList.remove('d-none');
            btnGuardar.disabled = false;
        };
        reader.readAsDataURL(file);
    }

    document.getElementById('formSubir').addEventListener('submit', function (e) {
        if (!firmaDataSubir.value) {
            e.preventDefault(); alert('Selecciona una imagen primero.'); return;
        }
    });
})();
</script>
@endpush
