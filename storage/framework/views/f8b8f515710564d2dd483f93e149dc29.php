<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  @page {
    size: A4 portrait;
    margin: 8.5mm 7.5mm 5mm 5mm;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 8pt; color: #000; width: 100%; }

  table { border-collapse: collapse; width: 100%; }
  td, th { font-size: 8pt; vertical-align: middle; }

  /* Separación entre secciones */
  .mt { margin-top: 5pt; }

  /* ── HEADERS ── */
  .sh  { background-color: #33CCCC; font-weight: bold; font-size: 8pt; padding: 3pt 5pt; }
  .shg { background-color: #E7E6E6; font-weight: bold; font-size: 8pt; padding: 3pt 5pt; }
  .shb { background-color: #BEF0F0; font-weight: bold; font-size: 8pt; padding: 3pt 5pt; text-align: center; }
  .lbl { font-weight: bold; }

  /* Bordes */
  .b  { border: 1pt solid #000; }
  .bt { border-top: 1pt solid #000; }
  .bb { border-bottom: 1pt solid #000; }

  /* Celda normal */
  .c  { border: 1pt solid #000; padding: 4pt 5pt; }

  /* Checkbox */
  .chk { border: 1pt solid #000; padding: 0pt 1.5pt; font-size: 7pt; }

  /* Firmas */
  .firma-esp  { height: 55pt; }
  .firma-line { border-top: 1pt solid #000; width: 90pt; margin: 3pt auto 2pt; }
  .firma-lbl  { font-size: 7.5pt; font-weight: bold; text-align: center; }
  .firma-nom  { font-size: 7pt; color: #333; text-align: center; }
  .firma-fec  { font-size: 6.5pt; color: #555; text-align: center; }

  /* Nota */
  .nota { margin-top: 8pt; font-size: 7pt; color: #444; }
</style>
</head>
<body>


<table class="b mt">
  <tr style="height:15pt;">
    <td rowspan="2" style="width:25.4%; text-align:center; border:1pt solid #000; padding:3pt 5pt;">
      <?php if($logoBase64): ?>
        <img src="<?php echo $logoBase64; ?>" alt="TRIMAX" style="max-width:90pt; max-height:28pt;">
      <?php else: ?>
        <span style="font-size:11pt; font-weight:bold;">TRIMAX</span>
      <?php endif; ?>
    </td>
    <td rowspan="2" style="width:48.3%; text-align:center; border:1pt solid #000; padding:3pt 5pt;">
      <div style="font-size:7pt; font-weight:bold;">FORMATO</div>
      <div style="font-size:7pt; font-weight:bold;">REQUERIMIENTO DE PERSONAL</div>
    </td>
    <td style="width:9.2%; font-weight:bold; border:1pt solid #000; padding:3pt 5pt;">Código:</td>
    <td style="width:17.1%; border:1pt solid #000; padding:3pt 5pt;">RH-PR-03-FO-01</td>
  </tr>
  <tr style="height:27pt;">
    <td style="font-weight:bold; border:1pt solid #000; padding:3pt 5pt;">Versión:</td>
    <td style="border:1pt solid #000; padding:3pt 5pt;">01</td>
  </tr>
</table>


<table class="mt">
  <tr>
    <td style="width:60%; border-bottom:1pt solid #000; padding:3pt 1pt;">
      <b>Nombre / Cargo del Solicitante:</b>
      <?php echo $requerimiento->solicitante->name; ?><?php if($requerimiento->solicitante->cargo): ?> — <?php echo $requerimiento->solicitante->cargo; ?><?php endif; ?>
    </td>
    <td style="width:40%; border-bottom:1pt solid #000; padding:3pt 1pt; text-align:right;">
      <b>Fecha de Solicitud:</b> <?php echo $requerimiento->fecha_solicitud->format('d/m/Y'); ?>

    </td>
  </tr>
</table>


<table class="b mt">
  <tr><td colspan="4" class="sh">1. Puesto</td></tr>
  <tr>
    <td class="c lbl" style="width:18%;">Título del puesto:</td>
    <td class="c" style="width:32%;"><?php echo $requerimiento->puesto; ?></td>
    <td class="c lbl" style="width:8%;">Área:</td>
    <td class="c" style="width:42%;"><?php echo $requerimiento->gerencia; ?></td>
  </tr>
  <tr>
    <td class="c lbl">Reporta a :</td>
    <td class="c" colspan="3"><?php echo $requerimiento->jefe_directo; ?></td>
  </tr>
  <tr>
    <td class="c lbl">Supervisa a :</td>
    <td class="c" colspan="3"><?php echo $requerimiento->supervisa_a ?? ''; ?></td>
  </tr>
  <tr>
    <td class="c lbl">N° de vacantes :</td>
    <td class="c"><?php echo $requerimiento->num_vacantes ?? 1; ?></td>
    <td class="c lbl">Maneja inf. confidencial</td>
    <td class="c">
      <span class="chk"><?php echo !$requerimiento->info_confidencial ? 'X':'&nbsp;'; ?></span> No &nbsp;
      <span class="chk"><?php echo $requerimiento->info_confidencial  ? 'X':'&nbsp;'; ?></span> Sí
    </td>
  </tr>
</table>


<table class="b mt">
  <tr>
    
    <td style="width:47.9%; vertical-align:top; padding:0; border-right:1pt solid #000;">
      <table>
        <tr><td colspan="2" class="sh" style="border-bottom:1pt solid #000;">2. Información adicional respecto al puesto</td></tr>
        <tr>
          <td class="c" style="width:50%;">
            <span class="chk"><?php echo $requerimiento->tipo_vacante==='vacante' ? 'X':'&nbsp;'; ?></span> Vacante
          </td>
          <td class="c" style="width:50%;">
            <span class="chk"><?php echo $requerimiento->permanencia==='temporal' ? 'X':'&nbsp;'; ?></span> Temporal
          </td>
        </tr>
        <tr>
          <td class="c">
            <span class="chk"><?php echo $requerimiento->tipo_vacante==='reemplazo' ? 'X':'&nbsp;'; ?></span> Reemplazo
          </td>
          <td class="c">
            <span class="chk"><?php echo $requerimiento->permanencia==='permanente' ? 'X':'&nbsp;'; ?></span> Permanente
          </td>
        </tr>
        <tr>
          <td class="c">
            <span class="chk"><?php echo $requerimiento->tipo_vacante==='posicion_nueva' ? 'X':'&nbsp;'; ?></span> Posición nueva
          </td>
          <td class="c">
            <span class="chk"><?php echo $requerimiento->disponibilidad_viaje ? 'X':'&nbsp;'; ?></span> Disp. para viajar
          </td>
        </tr>
        <tr>
          <td class="c">
            <span class="chk"><?php echo $requerimiento->jornada==='tiempo_parcial' ? 'X':'&nbsp;'; ?></span> Tiempo Parcial
          </td>
          <td class="c">
            <span class="chk"><?php echo $requerimiento->jornada==='tiempo_completo' ? 'X':'&nbsp;'; ?></span> Tiempo Completo
          </td>
        </tr>
        <tr>
          <td class="c" colspan="2"><b>Motivo</b> &nbsp; <?php echo $requerimiento->motivo ?? ''; ?></td>
        </tr>
      </table>
    </td>

    
    <td style="width:52.1%; vertical-align:top; padding:0;">
      <table>
        <tr><td colspan="2" class="shg" style="border-bottom:1pt solid #000;">3. No llenar - Espacio para que lo llene Recursos Humanos</td></tr>
        <tr>
          <td class="c lbl" style="width:55%;">Fecha estimada de contratación</td>
          <td class="c"><?php echo $requerimiento->fecha_estimada_contratacion ? $requerimiento->fecha_estimada_contratacion->format('d/m/Y') : ''; ?></td>
        </tr>
        <tr>
          <td class="c lbl">Tipo de contrato</td>
          <td class="c"><?php echo $requerimiento->tipo_contrato ?? ''; ?></td>
        </tr>
        <tr>
          <td class="c lbl">Duración</td>
          <td class="c"><?php echo $requerimiento->duracion_contrato ?? ''; ?></td>
        </tr>
        <tr>
          <td class="c lbl">Remuneración prevista &nbsp; S/.</td>
          <td class="c"><?php echo $requerimiento->remuneracion_prevista ? number_format($requerimiento->remuneracion_prevista, 2) : ''; ?></td>
        </tr>
        <tr>
          <td class="c lbl">Horario de Trabajo</td>
          <td class="c"><?php echo $requerimiento->horario_trabajo ?? ''; ?></td>
        </tr>
        <tr>
          <td class="c lbl">Beneficios</td>
          <td class="c"><?php echo $requerimiento->beneficios ?? ''; ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>


<table class="b mt">
  <tr><td colspan="2" class="sh">4. Candidatos Internos o Externos a considerar para el puesto</td></tr>
  <tr>
    <th class="shb" style="width:72.7%;">Nombre Completo</th>
    <th class="shb" style="width:27.3%;">Teléfono de Contacto</th>
  </tr>
  <?php $cands = $requerimiento->candidatos ?? []; ?>
  <?php $__currentLoopData = ['a','b','c']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <tr>
    <td class="c"><?php echo $l; ?>. <?php echo $cands[$i]['nombre'] ?? ''; ?></td>
    <td class="c"><?php echo $cands[$i]['telefono'] ?? ''; ?></td>
  </tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table>


<table class="b mt">
  <tr><td class="sh">5. Herramientas que el puesto requiere</td></tr>
  <?php $herrs = $requerimiento->herramientas ?? []; ?>
  <?php $__currentLoopData = ['a','b','c']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <tr><td class="c"><?php echo $l; ?>. <?php echo $herrs[$i] ?? ''; ?></td></tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table>


<table class="b mt">
  <tr><td colspan="3" class="sh">6. Aprobaciones</td></tr>
  <tr>
    <td style="width:32.3%; text-align:center; border:1pt solid #000; padding:4pt 3pt;">
      <div class="firma-esp">
        <?php if($requerimiento->firma_solicitante_data): ?>
          <img src="<?php echo $requerimiento->firma_solicitante_data; ?>" alt="firma" style="max-height:50pt; max-width:100pt;">
        <?php endif; ?>
      </div>
      <div class="firma-line"></div>
      <div class="firma-lbl">Responsable de Área Solicitante</div>
      <?php if($requerimiento->firma_solicitante_nombre): ?><div class="firma-nom"><?php echo $requerimiento->firma_solicitante_nombre; ?></div><?php endif; ?>
      <?php if($requerimiento->firma_solicitante_at): ?><div class="firma-fec"><?php echo $requerimiento->firma_solicitante_at->format('d/m/Y H:i'); ?></div><?php endif; ?>
    </td>
    <td style="width:30.0%; text-align:center; border:1pt solid #000; padding:4pt 3pt;">
      <div class="firma-esp">
        <?php if($requerimiento->firma_rrhh_data): ?>
          <img src="<?php echo $requerimiento->firma_rrhh_data; ?>" alt="firma" style="max-height:50pt; max-width:100pt;">
        <?php endif; ?>
      </div>
      <div class="firma-line"></div>
      <div class="firma-lbl">Responsable de Recursos Humanos</div>
      <?php if($requerimiento->firma_rrhh_nombre): ?><div class="firma-nom"><?php echo $requerimiento->firma_rrhh_nombre; ?></div><?php endif; ?>
      <?php if($requerimiento->firma_rrhh_at): ?><div class="firma-fec"><?php echo $requerimiento->firma_rrhh_at->format('d/m/Y H:i'); ?></div><?php endif; ?>
    </td>
    <td style="width:37.7%; text-align:center; border:1pt solid #000; padding:4pt 3pt;">
      <div class="firma-esp">
        <?php if($requerimiento->firma_gerente_data): ?>
          <img src="<?php echo $requerimiento->firma_gerente_data; ?>" alt="firma" style="max-height:50pt; max-width:100pt;">
        <?php endif; ?>
      </div>
      <div class="firma-line"></div>
      <div class="firma-lbl">Gerente General</div>
      <?php if($requerimiento->firma_gerente_nombre): ?><div class="firma-nom"><?php echo $requerimiento->firma_gerente_nombre; ?></div><?php endif; ?>
      <?php if($requerimiento->firma_gerente_at): ?><div class="firma-fec"><?php echo $requerimiento->firma_gerente_at->format('d/m/Y H:i'); ?></div><?php endif; ?>
    </td>
  </tr>
</table>


<div class="nota">
  <b>NOTA:</b><br>
  La versión impresa de este documento es una copia NO CONTROLADA.
  Verificar antes de utilizar el documento que esta sea la última versión.
</div>

</body>
</html>
<?php /**PATH /var/www/resources/views/rrhh/requerimientos/pdf.blade.php ENDPATH**/ ?>