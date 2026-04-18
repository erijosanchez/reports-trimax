@php $p = $prefix ?? ''; @endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
        <input type="text" name="cliente_nombre" id="{{ $p }}cliente_nombre"
               class="form-control" placeholder="Nombre completo" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Teléfono</label>
        <input type="text" name="cliente_telefono" id="{{ $p }}cliente_telefono"
               class="form-control" placeholder="Ej: 987654321">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Referencia / N° Orden</label>
        <input type="text" name="referencia" id="{{ $p }}referencia"
               class="form-control" placeholder="Ej: ORD-001">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Sede <span class="text-danger">*</span></label>
        <select name="sede" id="{{ $p }}sede" class="form-select" required>
            <option value="">Seleccionar sede</option>
            @foreach($sedes as $s)
                <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Dirección <span class="text-danger">*</span></label>
        <input type="text" name="direccion" id="{{ $p }}direccion"
               class="form-control" placeholder="Dirección completa de entrega" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Latitud
            <span class="text-muted fw-normal small">(opcional)</span>
        </label>
        <input type="number" name="latitud" id="{{ $p }}latitud"
               class="form-control" step="0.0000001" placeholder="Ej: -14.0677">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Longitud
            <span class="text-muted fw-normal small">(opcional)</span>
        </label>
        <input type="number" name="longitud" id="{{ $p }}longitud"
               class="form-control" step="0.0000001" placeholder="Ej: -75.7286">
        <div class="form-text">
            <a href="https://maps.google.com" target="_blank" class="small">
                <i class="mdi mdi-map-search"></i> Buscar coordenadas en Google Maps
            </a>
        </div>
    </div>
    @if($p === 'edit-')
    <div class="col-md-6">
        <label class="form-label fw-semibold">Estado</label>
        <select name="estado" id="{{ $p }}estado" class="form-select">
            <option value="pendiente">Pendiente</option>
            <option value="en_ruta">En ruta</option>
            <option value="entregado">Entregado</option>
            <option value="fallido">Fallido</option>
        </select>
    </div>
    @endif
    <div class="col-12">
        <label class="form-label fw-semibold">Notas</label>
        <textarea name="notas" id="{{ $p }}notas" class="form-control" rows="2"
                  placeholder="Referencias del lugar, instrucciones, etc."></textarea>
    </div>
</div>
