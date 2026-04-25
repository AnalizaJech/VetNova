<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="VetNova — Sistema de Gestión Veterinaria">

    <title>{{ $title ?? 'VetNova' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2310b981'><path d='M19 10.5h-5.5V5a1.5 1.5 0 0 0-3 0v5.5H5a1.5 1.5 0 0 0 0 3h5.5V19a1.5 1.5 0 0 0 3 0v-5.5H19a1.5 1.5 0 0 0 0-3z'/></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200/50">

    {{-- SweetAlert2 CDN para Modales de Avisos --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.addEventListener('swal', function(e) {
            let data = e.detail[0];
            Swal.fire({
                icon: data.icon,
                title: data.title,
                text: data.text,
                background: '#1d232a', // Dark theme background
                color: '#a6adbb',      // Dark theme text
                confirmButtonColor: '#10b981', // Success color
            });
        });
    </script>

    {{-- Layout principal con sidebar colapsable de Mary UI --}}
    <x-main full-width>

        {{-- ═══════════ SIDEBAR ═══════════ --}}
        <x-slot:sidebar drawer="main-drawer" class="bg-neutral text-neutral-content">

            {{-- Logo y nombre del sistema --}}
            <div class="flex items-center gap-3 px-5 py-6 border-b border-white/10">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary/20">
                    <x-icon name="o-heart" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <h1 class="text-lg font-bold font-heading tracking-tight">VetNova</h1>
                    <p class="text-xs text-neutral-content/60">Gestión Veterinaria</p>
                </div>
            </div>

            {{-- Menú de navegación --}}
            <x-menu activate-by-route class="mt-2">

                {{-- Dashboard --}}
                <x-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard') }}" />

                {{-- Agenda --}}
                <x-menu-item title="Citas" icon="o-calendar" link="{{ route('citas') }}" badge="Nuevo" badge-classes="badge-primary badge-sm" />

                <x-menu-separator />

                {{-- Sección Clínica --}}
                <x-menu-sub title="Clínica" icon="o-building-office">
                    <x-menu-item title="Clientes" icon="o-users" link="{{ route('clientes') }}" />
                    <x-menu-item title="Mascotas" icon="o-heart" link="{{ route('mascotas') }}" />
                    <x-menu-item title="Historia Clínica" icon="o-clipboard-document-list" link="{{ route('historias') }}" />
                    <x-menu-item title="Vacunas" icon="o-shield-check" link="{{ route('vacunas') }}" />
                </x-menu-sub>

                {{-- Sección Operaciones --}}
                <x-menu-sub title="Operaciones" icon="o-cog-6-tooth">
                    <x-menu-item title="Inventario" icon="o-cube" link="{{ route('inventario') }}" />
                    <x-menu-item title="Punto de Venta" icon="o-banknotes" link="{{ route('caja') }}" badge="POS" badge-classes="badge-success badge-sm" />
                    <x-menu-item title="Facturación" icon="o-document-text" link="{{ route('facturacion') }}" />
                    <x-menu-item title="Hospitalización" icon="o-building-office-2" link="{{ route('hospitalizacion') }}" />
                </x-menu-sub>

                <x-menu-separator />

                {{-- Notificaciones y reportes --}}
                <x-menu-item title="Recordatorios" icon="o-bell" link="{{ route('recordatorios') }}" />
                <x-menu-item title="Reportes" icon="o-chart-bar" link="{{ route('reportes') }}" />

                @can('configuracion.ver')
                    <x-menu-separator />
                    <x-menu-item title="Configuración" icon="o-cog-8-tooth" link="/configuracion" />
                    <x-menu-item title="Usuarios" icon="o-user-group" link="/usuarios" />
                @endcan
            </x-menu>

            {{-- Info del usuario en el fondo del sidebar --}}
            <div class="mt-auto border-t border-white/10 px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="avatar placeholder">
                        <div class="bg-primary/20 text-primary rounded-full w-9 flex items-center justify-center">
                            <span class="text-sm font-bold">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name ?? 'Usuario' }}</p>
                        <p class="text-xs text-neutral-content/60 truncate">
                            {{ auth()->user()->roles->first()?->name ?? 'Sin rol' }}
                        </p>
                    </div>
                </div>
            </div>
        </x-slot:sidebar>

        {{-- ═══════════ NAVBAR SUPERIOR ═══════════ --}}
        <x-slot:content>
            <x-nav sticky full-width class="border-b border-base-300 bg-base-100">
                <x-slot:brand>
                    {{-- Botón hamburguesa (solo móvil) --}}
                    <label for="main-drawer" class="lg:hidden mr-3 cursor-pointer">
                        <x-icon name="o-bars-3" class="w-6 h-6" />
                    </label>
                    {{-- Breadcrumb o título de página --}}
                    <h2 class="text-lg font-semibold font-heading text-base-content">
                        {{ $title ?? 'Dashboard' }}
                    </h2>
                </x-slot:brand>

                <x-slot:actions>

                    {{-- Botón de logout --}}
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <x-button
                            type="submit"
                            icon="o-arrow-right-on-rectangle"
                            class="btn-ghost btn-sm"
                            tooltip="Cerrar sesión"
                        />
                    </form>
                </x-slot:actions>
            </x-nav>

            {{-- ═══════════ CONTENIDO PRINCIPAL ═══════════ --}}
            <main class="p-4 md:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </x-slot:content>

    </x-main>

</body>
</html>
