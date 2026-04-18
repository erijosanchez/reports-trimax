@php $p = $prefix ?? ''; @endphp
<div class="mb-3">
    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
    <input type="text" name="nombre" id="{{ $p }}nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
</div>
<div class="mb-3">
    <label class="form-label fw-semibold">Sede <span class="text-danger">*</span></label>
    <select name="sede" id="{{ $p }}sede" class="form-select" required>
        <option value="">Seleccionar sede</option>
        @foreach($sedes as $s)
            <option value="{{ $s }}">{{ $s }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label fw-semibold">Teléfono</label>
    <input type="text" name="telefono" id="{{ $p }}telefono" class="form-control" placeholder="Ej: 987654321">
</div>
<div class="mb-3">
    <label class="form-label fw-semibold">ID Dispositivo Traccar</label>
    <input type="number" name="traccar_device_id" id="{{ $p }}traccar_device_id" class="form-control"
           placeholder="Ver tabla de dispositivos Traccar">
    <div class="form-text">Corresponde al <code>id</code> del dispositivo en Traccar.</div>
</div>
<div class="mb-3">
    <label class="form-label fw-semibold">Estado</label>
    <select name="estado" id="{{ $p }}estado" class="form-select">
        <option value="activo">Activo</option>
        <option value="inactivo">Inactivo</option>
    </select>
</div>
