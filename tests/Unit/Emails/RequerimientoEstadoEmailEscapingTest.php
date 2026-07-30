<?php

namespace Tests\Unit\Emails;

use Carbon\Carbon;
use Tests\TestCase;

class RequerimientoEstadoEmailEscapingTest extends TestCase
{
    private function renderTipoEtapa(string $extra): string
    {
        $requerimiento = (object) [
            'codigo'               => 'REQ-001',
            'estado'                => 'En Proceso',
            'puesto'                => 'Vendedor',
            'sede'                  => 'Lima',
            'solicitante'           => (object) ['name' => 'Ana Solicitante'],
            'responsableRh'         => null,
            'responsable_rh_externo'=> null,
            'fecha_solicitud'       => Carbon::now(),
        ];

        return view('emails.rrhh.requerimiento_estado', [
            'requerimiento'  => $requerimiento,
            'notifiable'     => (object) ['name' => 'Responsable RH'],
            'url'            => 'https://example.test/requerimientos/1',
            'tipo'           => 'etapa',
            'estadoAnterior' => null,
            'estadoNuevo'    => null,
            'extra'          => $extra,
        ])->render();
    }

    public function test_etapa_extra_is_escaped_and_cannot_inject_html(): void
    {
        $payload = '<script>alert(1)</script> <a href="https://evil.test">click</a>';

        $html = $this->renderTipoEtapa($payload);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<a href="https://evil.test">', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_etapa_plain_text_still_renders(): void
    {
        $html = $this->renderTipoEtapa('Se agendó entrevista para el 30/07.');

        $this->assertStringContainsString('Se agendó entrevista para el 30/07.', $html);
    }
}
