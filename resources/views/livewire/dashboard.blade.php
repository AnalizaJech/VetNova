<div>
    {{-- Saludo --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold font-heading text-base-content tracking-tight">
            ¡Hola, {{ auth()->user()->name }}! <x-icon name="o-sparkles" class="w-8 h-8 text-warning inline-block" />
        </h1>
        <p class="text-base-content/60">Aquí tienes el resumen de tu clínica de hoy, {{ now()->translatedFormat('l d de F') }}.</p>
    </div>

    {{-- Tarjetas de Estadísticas (Stats) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat 
            title="Ingresos de Hoy" 
            value="S/ {{ number_format($ventasHoy, 2) }}" 
            icon="o-banknotes" 
            class="shadow-sm border-l-4 border-success bg-base-100" 
            color="text-success" 
        />

        <x-stat 
            title="Citas para Hoy" 
            value="{{ $citasHoy }}" 
            icon="o-calendar-days" 
            class="shadow-sm border-l-4 border-primary bg-base-100" 
            color="text-primary" 
        />

        <x-stat 
            title="Nuevos Pacientes (Mes)" 
            value="{{ $nuevosPacientes }}" 
            icon="o-heart" 
            class="shadow-sm border-l-4 border-info bg-base-100" 
            color="text-info" 
        />

        <x-stat 
            title="Alertas de Inventario" 
            value="{{ $alertasStock }}" 
            icon="o-exclamation-triangle" 
            class="shadow-sm border-l-4 border-error bg-base-100" 
            color="text-error" 
            description="Productos con stock bajo"
        />
    </div>

    {{-- Widgets a dos columnas --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        
        {{-- Widget 1: Citas de Hoy --}}
        <x-card title="Próximas Citas (Hoy)" shadow class="border border-base-200">
            <x-slot:menu>
                <x-button icon="o-arrow-right" link="{{ route('citas') }}" class="btn-ghost btn-sm" tooltip="Ver agenda completa" />
            </x-slot:menu>

            @if($proximasCitas->isEmpty())
                <div class="text-center py-6 text-base-content/40">
                    <x-icon name="o-cup" class="w-10 h-10 mx-auto mb-2 opacity-50" />
                    <p>No tienes citas pendientes para el resto del día.</p>
                </div>
            @else
                <div class="divide-y divide-base-200">
                    @foreach($proximasCitas as $cita)
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="bg-primary/10 text-primary font-bold px-3 py-1.5 rounded-lg text-sm">
                                    {{ \Carbon\Carbon::parse($cita->fecha_hora)->format('h:i A') }}
                                </div>
                                <div>
                                    <p class="font-semibold text-base-content">{{ $cita->mascota->nombre ?? 'N/A' }}</p>
                                    <p class="text-xs text-base-content/60">{{ $cita->motivo }}</p>
                                </div>
                            </div>
                            <div>
                                <x-badge :value="$cita->estado" class="{{ $cita->estado === 'EN_PROGRESO' ? 'badge-warning' : 'badge-ghost' }} badge-sm" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- Widget 2: Últimas Ventas --}}
        <x-card title="Ventas Recientes" shadow class="border border-base-200">
            <x-slot:menu>
                <x-button icon="o-plus" link="{{ route('caja') }}" class="btn-primary btn-sm" label="Nueva Venta" />
            </x-slot:menu>

            @if($ultimasVentas->isEmpty())
                <div class="text-center py-6 text-base-content/40">
                    <x-icon name="o-receipt-percent" class="w-10 h-10 mx-auto mb-2 opacity-50" />
                    <p>No hay ventas registradas recientemente.</p>
                </div>
            @else
                <div class="divide-y divide-base-200">
                    @foreach($ultimasVentas as $venta)
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-base-200 text-base-content rounded-full w-10">
                                        <x-icon name="o-banknotes" class="w-5 h-5 text-success" />
                                    </div>
                                </div>
                                <div>
                                    <p class="font-semibold text-base-content">
                                        {{ $venta->cliente->nombres ?? 'Público General' }}
                                    </p>
                                    <p class="text-xs text-base-content/60">
                                        {{ $venta->created_at->format('h:i A') }} &bull; {{ $venta->tipo_comprobante }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-success">S/ {{ number_format($venta->total, 2) }}</p>
                                <p class="text-[10px] text-base-content/50 uppercase">{{ $venta->metodo_pago }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

    </div>
</div>
