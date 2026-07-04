@props([
    'title' => null,
])
@php
    $variant = auth()->user()->isAdmin() ? 'agency' : 'portal';
@endphp

@include('layouts.app.shell', [
    'title' => $title,
    'variant' => $variant,
    'content' => $slot,
])
