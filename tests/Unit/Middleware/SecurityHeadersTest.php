<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SecurityHeadersTest extends TestCase
{
    public function test_adds_the_three_security_headers_to_the_response(): void
    {
        $middleware = new SecurityHeaders();
        $request    = Request::create('/login', 'GET');

        $response = $middleware->handle($request, fn () => new Response('ok'));

        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
    }

    public function test_does_not_break_binary_file_responses(): void
    {
        $middleware = new SecurityHeaders();
        $request    = Request::create('/vouchers/1/archivo/0', 'GET');

        $file = tempnam(sys_get_temp_dir(), 'sec-headers-test');
        file_put_contents($file, 'contenido');

        // BinaryFileResponse (usada por response()->file(...) en los controladores
        // de vouchers/desbloqueo) no soporta ->header() encadenado; el middleware
        // debe usar ->headers->add() para no romper esas descargas.
        $response = $middleware->handle($request, fn () => new BinaryFileResponse($file));

        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));

        @unlink($file);
    }
}
