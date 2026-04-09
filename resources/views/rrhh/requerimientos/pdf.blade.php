<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 portrait;
        }
        * { margin: 0; padding: 10px; padding-right: 30px; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 8.5pt;
            color: #000;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td, th {
            border: 1pt solid #000;
            vertical-align: middle;
            padding: 3pt 5pt;
            word-wrap: break-word;
        }

        .sh  { background-color: #33CCCC; font-weight: bold; }
        .shg { background-color: #E7E6E6; font-weight: bold; }
        .shb { background-color: #BEF0F0; font-weight: bold; text-align: center; }
        .lbl { font-weight: bold; }

        .mt { margin-top: 6pt; }
        .text-center { text-align: center; }

        .chk {
            border: 1pt solid #000;
            padding: 0 2pt;
            font-size: 7.5pt;
        }

        .firma-box {
            height: 65pt;
            vertical-align: bottom;
            text-align: center;
            padding-bottom: 4pt;
        }
        .firma-img-wrap {
            height: 50pt;
            display: block;
            text-align: center;
        }
        .firma-line { border-top: 1pt solid #000; width: 80%; margin: 0 auto; }
        .firma-txt  { font-size: 7.5pt; font-weight: bold; margin-top: -5pt; }
        .firma-nom  { font-size: 7pt; color: #333; margin-top: -10px}
        .firma-fec  { font-size: 6.5pt; color: #555; margin-top: -10px; }

        .nota { margin-top: 10pt; font-size: 7pt; color: #444; }
    </style>
</head>
<body>

{{-- ══ CABECERA ══ --}}
<table>
    <tr>
        <td rowspan="2" style="width:25%;" class="text-center">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="TRIMAX" style="max-width:90pt; max-height:28pt;">
            @else
                <b style="font-size:11pt;">TRIMAX</b>
            @endif
        </td>
        <td rowspan="2" style="width:45%;" class="text-center">
            <div class="lbl">FORMATO</div>
            <div class="lbl">REQUERIMIENTO DE PERSONAL</div>
        </td>
        <td style="width:10%;" class="lbl">Código:</td>
        <td style="width:20%;">RH-PR-03-FO-01</td>
    </tr>
    <tr>
        <td class="lbl">Versión:</td>
        <td>01</td>
    </tr>
</table>

{{-- ══ SOLICITANTE / FECHA ══ --}}
<table class="mt" style="border:none;">
    <tr>
        <td style="border:none; border-bottom:1pt solid #000;">
            <span class="lbl">Nombre / Cargo del Solicitante:</span>
            {{ $requerimiento->solicitante->name }}@if($requerimiento->solicitante->cargo) — {{ $requerimiento->solicitante->cargo }}@endif
        </td>
        <td style="border:none; border-bottom:1pt solid #000; width:32%; text-align:right;">
            <span class="lbl">Fecha de Solicitud:</span> {{ $requerimiento->fecha_solicitud->format('d/m/Y') }}
        </td>
    </tr>
</table>

{{-- ══ SECCIÓN 1: PUESTO ══ --}}
<table class="mt">
    <tr><td colspan="4" class="sh">1. Puesto</td></tr>
    <tr>
        <td class="lbl" style="width:18%;">Título del puesto:</td>
        <td style="width:32%;">{{ $requerimiento->puesto }}</td>
        <td class="lbl" style="width:10%;">Área:</td>
        <td style="width:40%;">{{ $requerimiento->gerencia }}</td>
    </tr>
    <tr>
        <td class="lbl">Reporta a:</td>
        <td colspan="3">{{ $requerimiento->jefe_directo }}</td>
    </tr>
    <tr>
        <td class="lbl">Supervisa a:</td>
        <td colspan="3">{{ $requerimiento->supervisa_a ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">N° de vacantes:</td>
        <td>{{ $requerimiento->num_vacantes ?? 1 }}</td>
        <td class="lbl" colspan="2">
            Maneja información confidencial: &nbsp;
            <span class="chk">{{ !$requerimiento->info_confidencial ? 'X' : '&nbsp;' }}</span> No &nbsp;
            <span class="chk">{{ $requerimiento->info_confidencial  ? 'X' : '&nbsp;' }}</span> Sí
        </td>
    </tr>
</table>

{{-- ══ SECCIONES 2 + 3 ══ --}}
<table class="mt">
    <tr style="padding:0 !important; margin:0 !important;">
        {{-- SECCIÓN 2 --}}
        <td style="width:50%; padding:0 !important; margin:0 !important; vertical-align:top;">
            <table style="border:none; padding:0 !important; margin:0 !important;">
                <tr><td colspan="2" class="sh" style="border-top:none; border-left:none;">2. Información adicional respecto al puesto</td></tr>
                <tr>
                    <td style="border-left:none;"><span class="chk">{{ $requerimiento->tipo_vacante==='vacante'        ? 'X':'&nbsp;' }}</span> Vacante</td>
                    <td style="border-right:none;"><span class="chk">{{ $requerimiento->permanencia==='temporal'       ? 'X':'&nbsp;' }}</span> Temporal</td>
                </tr>
                <tr>
                    <td style="border-left:none;"><span class="chk">{{ $requerimiento->tipo_vacante==='reemplazo'      ? 'X':'&nbsp;' }}</span> Reemplazo</td>
                    <td style="border-right:none;"><span class="chk">{{ $requerimiento->permanencia==='permanente'     ? 'X':'&nbsp;' }}</span> Permanente</td>
                </tr>
                <tr>
                    <td style="border-left:none;"><span class="chk">{{ $requerimiento->tipo_vacante==='posicion_nueva' ? 'X':'&nbsp;' }}</span> Posición nueva</td>
                    <td style="border-right:none;"><span class="chk">{{ $requerimiento->disponibilidad_viaje           ? 'X':'&nbsp;' }}</span> Disponibilidad para viajar</td>
                </tr>
                <tr>
                    <td style="border-left:none;"><span class="chk">{{ $requerimiento->jornada==='tiempo_parcial'     ? 'X':'&nbsp;' }}</span> Tiempo Parcial</td>
                    <td style="border-right:none;"><span class="chk">{{ $requerimiento->jornada==='tiempo_completo'   ? 'X':'&nbsp;' }}</span> Tiempo Completo</td>
                </tr>
                <tr>
                    <td colspan="2" style="border-left:none; border-right:none; border-bottom:none; height:30pt; vertical-align:top;">
                        <span class="lbl">Motivo:</span> {{ $requerimiento->motivo ?? '' }}
                    </td>
                </tr>
            </table>
        </td>

        {{-- SECCIÓN 3 --}}
        <td style="width:50%; padding:0; vertical-align:top;">
            <table style="border:none; padding:0 !important; margin:0 !important;">
                <tr><td colspan="2" class="shg" style="border-top:none; border-right:none;">3. No llenar - Espacio para Recursos Humanos</td></tr>
                <tr>
                    <td class="lbl" style="width:58%; border-right:none;">Fecha estimada de contratación:</td>
                    <td style="border-right:none;">{{ $requerimiento->fecha_estimada_contratacion ? $requerimiento->fecha_estimada_contratacion->format('d/m/Y') : '' }}</td>
                </tr>
                <tr>
                    <td class="lbl" style="border-right:none;">Tipo de contrato:</td>
                    <td style="border-right:none;">{{ $requerimiento->tipo_contrato ?? '' }}</td>
                </tr>
                <tr>
                    <td class="lbl" style="border-right:none;">Duración:</td>
                    <td style="border-right:none;">{{ $requerimiento->duracion_contrato ?? '' }}</td>
                </tr>
                <tr>
                    <td class="lbl" style="border-right:none;">Remuneración prevista S/.</td>
                    <td style="border-right:none;">{{ $requerimiento->remuneracion_prevista ? number_format($requerimiento->remuneracion_prevista, 2) : '' }}</td>
                </tr>
                <tr>
                    <td class="lbl" style="border-right:none;">Horario de Trabajo:</td>
                    <td style="border-right:none;">{{ $requerimiento->horario_trabajo ?? '' }}</td>
                </tr>
                <tr>
                    <td class="lbl" style="border-right:none; border-bottom:none;">Beneficios:</td>
                    <td style="border-right:none; border-bottom:none;">{{ $requerimiento->beneficios ?? '' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ══ SECCIÓN 4: CANDIDATOS ══ --}}
@php $cands = $requerimiento->candidatos ?? []; @endphp
<table class="mt">
    <tr><td colspan="2" class="sh">4. Candidatos Internos o Externos a considerar para el puesto</td></tr>
    <tr>
        <td class="shb" style="width:75%;">Nombre Completo</td>
        <td class="shb" style="width:25%;">Teléfono de Contacto</td>
    </tr>
    @foreach(['a','b','c'] as $i => $l)
    <tr>
        <td>{{ $l }}. {{ $cands[$i]['nombre'] ?? '' }}</td>
        <td>{{ $cands[$i]['telefono'] ?? '' }}</td>
    </tr>
    @endforeach
</table>

{{-- ══ SECCIÓN 5: HERRAMIENTAS ══ --}}
@php $herrs = $requerimiento->herramientas ?? []; @endphp
<table class="mt">
    <tr><td class="sh">5. Herramientas que el puesto requiere</td></tr>
    @foreach(['a','b','c'] as $i => $l)
    <tr><td style="height:15pt;">{{ $l }}. {{ $herrs[$i] ?? '' }}</td></tr>
    @endforeach
</table>

{{-- ══ SECCIÓN 6: FIRMAS ══ --}}
<table class="mt">
    <tr><td colspan="3" class="sh">6. Aprobaciones</td></tr>
    <tr>
        {{-- Solicitante --}}
        <td class="firma-box">
            @if($requerimiento->firma_solicitante_data)
                <div class="firma-img-wrap">
                    <img src="{{ $requerimiento->firma_solicitante_data }}" style="max-height:46pt; max-width:120pt;">
                </div>
            @else
                <div style="height:50pt;"></div>
            @endif
            <div class="firma-line"></div>
            <div class="firma-txt">Responsable de Área Solicitante</div>
            @if($requerimiento->firma_solicitante_nombre)
                <div class="firma-nom">{{ $requerimiento->firma_solicitante_nombre }}</div>
            @endif
            @if($requerimiento->firma_solicitante_at)
                <div class="firma-fec">{{ $requerimiento->firma_solicitante_at->format('d/m/Y H:i') }}</div>
            @endif
        </td>

        {{-- RRHH --}}
        <td class="firma-box">
            @if($requerimiento->firma_rrhh_data)
                <div class="firma-img-wrap">
                    <img src="{{ $requerimiento->firma_rrhh_data }}" style="max-height:46pt; max-width:120pt;">
                </div>
            @else
                <div style="height:50pt;"></div>
            @endif
            <div class="firma-line"></div>
            <div class="firma-txt">Responsable de Recursos Humanos</div>
            @if($requerimiento->firma_rrhh_nombre)
                <div class="firma-nom">{{ $requerimiento->firma_rrhh_nombre }}</div>
            @endif
            @if($requerimiento->firma_rrhh_at)
                <div class="firma-fec">{{ $requerimiento->firma_rrhh_at->format('d/m/Y H:i') }}</div>
            @endif
        </td>

        {{-- Gerente --}}
        <td class="firma-box">
            @if($requerimiento->firma_gerente_data)
                <div class="firma-img-wrap">
                    <img src="{{ $requerimiento->firma_gerente_data }}" style="max-height:46pt; max-width:120pt;">
                </div>
            @else
                <div style="height:50pt;"></div>
            @endif
            <div class="firma-line"></div>
            <div class="firma-txt">Gerente General</div>
            @if($requerimiento->firma_gerente_nombre)
                <div class="firma-nom">{{ $requerimiento->firma_gerente_nombre }}</div>
            @endif
            @if($requerimiento->firma_gerente_at)
                <div class="firma-fec">{{ $requerimiento->firma_gerente_at->format('d/m/Y H:i') }}</div>
            @endif
        </td>
    </tr>
</table>

{{-- ══ NOTA ══ --}}
<div class="nota">
    <span class="lbl">NOTA:</span><br>
    &nbsp; &nbsp; &nbsp;La versión impresa de este documento es una copia NO CONTROLADA. Verificar antes de utilizar el documento que esta sea la última versión.
</div>

</body>
</html>
