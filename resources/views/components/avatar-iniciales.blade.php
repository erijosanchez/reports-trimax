{{--
    Avatar de iniciales generado localmente (ARQUITECTURA.md/FRONTEND.md, F2).
    Reemplaza a ui-avatars.com: antes cada avatar mandaba el nombre completo
    del usuario/cliente a un servicio externo en la query string. No requiere
    red ni JS.
--}}
@props(['nombre', 'background' => '6366f1', 'color' => 'fff', 'size' => 36])
@php
    $iniciales = collect(preg_split('/\s+/', trim((string) $nombre)))
        ->filter()
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
    $iniciales = $iniciales !== '' ? $iniciales : '?';
    $bg = '#'.ltrim($background, '#');
    $fg = '#'.ltrim($color, '#');
    $fontSize = max(10, (int) round($size / 2.5));
@endphp
<span {{ $attributes->merge(['class' => 'avatar-iniciales']) }}
      style="display:inline-flex;align-items:center;justify-content:center;width:{{ $size }}px;height:{{ $size }}px;background-color:{{ $bg }};color:{{ $fg }};font-weight:600;font-size:{{ $fontSize }}px;line-height:1;flex-shrink:0;"
      title="{{ $nombre }}">{{ $iniciales }}</span>
