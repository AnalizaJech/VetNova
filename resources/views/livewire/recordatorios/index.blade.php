<div>
    <x-header title="Centro de Recordatorios" subtitle="Envíos automáticos por WhatsApp/SMS" separator />

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- Próximas Citas --}}
        <x-card title="Recordatorios de Citas" subtitle="Programadas para hoy y mañana" shadow class="border border-base-200">
            @if($citas->isEmpty())
                <div class="text-center py-8 text-base-content/40">
                    <x-icon name="o-calendar" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                    <p>No hay citas programadas para notificar.</p>
                </div>
            @else
                <div class="divide-y divide-base-200/50">
                    @foreach($citas as $cita)
                        <div class="py-4 flex justify-between items-center">
                            <div>
                                <p class="font-bold text-base-content">{{ $cita->mascota->cliente->nombre_completo ?? 'Sin Cliente' }}</p>
                                <p class="text-xs text-base-content/60">
                                    Mascota: {{ $cita->mascota->nombre }} &bull; {{ $cita->fecha_hora->format('d/m/Y h:i A') }}
                                </p>
                            </div>
                            <div>
                                @if($cita->mascota->cliente && $cita->mascota->cliente->telefono)
                                    <x-button icon="o-chat-bubble-left-ellipsis" class="btn-sm btn-success text-white" 
                                        wire:click="enviarWhatsApp('{{ $cita->mascota->cliente->telefono }}', '{{ $cita->mascota->cliente->nombres }}', 'Cita Médica')" 
                                        tooltip="Enviar WhatsApp" spinner />
                                @else
                                    <x-badge value="Sin número" class="badge-error badge-sm" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- Próximas Vacunas / Desparasitaciones --}}
        <x-card title="Recordatorios de Vacunación" subtitle="Refuerzos y desparasitaciones pendientes para hoy y mañana" shadow class="border border-base-200">
            @if($vacunas->isEmpty())
                <div class="text-center py-8 text-base-content/40">
                    <x-icon name="o-shield-check" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                    <p>No hay vacunas pendientes para notificar.</p>
                </div>
            @else
                <div class="divide-y divide-base-200/50">
                    @foreach($vacunas as $registro)
                        <div class="py-4 flex justify-between items-center">
                            <div>
                                <p class="font-bold text-base-content">{{ $registro->mascota->cliente->nombre_completo ?? 'Sin Cliente' }}</p>
                                <p class="text-xs text-base-content/60">
                                    Mascota: {{ $registro->mascota->nombre }} &bull; Próxima dosis: {{ $registro->fecha_proxima->format('d/m/Y') }}
                                </p>
                                <p class="text-xs">
                                    <span class="badge {{ $registro->tipo_badge['class'] }} badge-sm">{{ $registro->tipo_badge['label'] }}</span>
                                    {{ $registro->producto_o_enfermedad }}
                                </p>
                            </div>
                            <div>
                                @if($registro->mascota->cliente && $registro->mascota->cliente->telefono)
                                    <x-button icon="o-chat-bubble-left-ellipsis" class="btn-sm btn-success text-white" 
                                        wire:click="enviarWhatsApp('{{ $registro->mascota->cliente->telefono }}', '{{ $registro->mascota->cliente->nombres }}', 'Vacunación')" 
                                        tooltip="Enviar WhatsApp" spinner />
                                @else
                                    <x-badge value="Sin número" class="badge-error badge-sm" />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

    </div>
</div>
