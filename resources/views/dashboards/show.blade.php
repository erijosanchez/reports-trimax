@extends('layouts.app')

@section('title', $dashboard->title)

@section('content')
<h1>{{ $dashboard->title }}</h1>
<p>{{ $dashboard->description }}</p>
<a href="{{ route('home') }}">← Volver</a>
<hr>
<iframe 
    src="{{ $dashboard->embed_url }}" 
    width="100%" 
    height="600" 
    frameborder="0" 
    allowFullScreen="true">
</iframe>
@endsection