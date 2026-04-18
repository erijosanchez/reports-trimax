<?php $p = $prefix ?? ''; ?>
<div class="mb-3">
    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
    <input type="text" name="nombre" id="<?php echo $p; ?>nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
</div>
<div class="mb-3">
    <label class="form-label fw-semibold">Sede <span class="text-danger">*</span></label>
    <select name="sede" id="<?php echo $p; ?>sede" class="form-select" required>
        <option value="">Seleccionar sede</option>
        <?php $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>
<div class="mb-3">
    <label class="form-label fw-semibold">Teléfono</label>
    <input type="text" name="telefono" id="<?php echo $p; ?>telefono" class="form-control" placeholder="Ej: 987654321">
</div>
<div class="mb-3">
    <label class="form-label fw-semibold">ID Dispositivo Traccar</label>
    <input type="number" name="traccar_device_id" id="<?php echo $p; ?>traccar_device_id" class="form-control"
           placeholder="Ver tabla de dispositivos Traccar">
    <div class="form-text">Corresponde al <code>id</code> del dispositivo en Traccar.</div>
</div>
<div class="mb-3">
    <label class="form-label fw-semibold">Estado</label>
    <select name="estado" id="<?php echo $p; ?>estado" class="form-select">
        <option value="activo">Activo</option>
        <option value="inactivo">Inactivo</option>
    </select>
</div>
<?php /**PATH /var/www/resources/views/tracking/_form-motorizado.blade.php ENDPATH**/ ?>