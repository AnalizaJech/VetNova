<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="vetnova">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'VetNova — Acceso' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- Toast para mensajes de error/éxito --}}
    <x-toast />

    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        {{-- Logo --}}
        <div class="flex items-center gap-3 mb-8">
            <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-primary/10">
                <x-icon name="o-heart" class="w-7 h-7 text-primary" />
            </div>
            <div>
                <h1 class="text-2xl font-bold font-heading tracking-tight text-base-content">VetNova</h1>
                <p class="text-sm text-base-content/60">Sistema de Gestión Veterinaria</p>
            </div>
        </div>

        {{-- Contenido del formulario de login/registro --}}
        <div class="w-full max-w-md">
            <x-card class="shadow-xl">
                {{ $slot }}
            </x-card>
        </div>

        {{-- Footer --}}
        <p class="mt-8 text-xs text-base-content/40">
            &copy; {{ date('Y') }} VetNova · Hecho en Perú 🇵🇪
        </p>
    </div>

</body>
</html>
