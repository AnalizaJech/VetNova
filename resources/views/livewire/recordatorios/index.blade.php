<div>
    <x-header title="Centro de Recordatorios" subtitle="Gestión omnicanal de notificaciones" separator>
        <x-slot:actions>
            <x-button icon="o-arrow-path" class="btn-ghost btn-sm" wire:click="$refresh" tooltip="Refrescar datos" />
        </x-slot:actions>
    </x-header>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        
        {{-- Próximas Citas --}}
        <div class="space-y-4">
            <div class="flex items-center gap-3 mb-2 px-2">
                <div class="bg-primary/10 p-2 rounded-lg">
                    <x-icon name="o-calendar" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <h2 class="text-xl font-bold font-heading">Recordatorios de Citas</h2>
                    <p class="text-xs text-base-content/50 uppercase tracking-wider font-semibold">Hoy y Mañana</p>
                </div>
            </div>

            @if($citas->isEmpty())
                <x-card class="bg-base-200/30 border-dashed border-2 border-base-300">
                    <div class="text-center py-6 text-base-content/40">
                        <p>No hay citas programadas para notificar.</p>
                    </div>
                </x-card>
            @else
                @foreach($citas as $cita)
                    <x-card class="hover:shadow-md transition-shadow border border-base-200 overflow-visible">
                        <div class="flex flex-col md:flex-row justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-lg text-base-content">{{ $cita->mascota->cliente->nombre_completo ?? 'Sin Cliente' }}</span>
                                    <x-badge :value="$cita->fecha_hora->format('h:i A')" class="badge-ghost font-mono text-[10px]" />
                                </div>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-base-content/70">
                                    <span class="flex items-center gap-1"><x-icon name="o-heart" class="w-4 h-4 text-primary" /> {{ $cita->mascota->nombre }}</span>
                                    <span class="flex items-center gap-1 font-medium"><x-icon name="o-calendar" class="w-4 h-4" /> {{ $cita->fecha_hora->format('d/m/Y') }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2.5 min-w-[170px] border-t md:border-t-0 md:border-l border-base-200 pt-3 md:pt-0 md:pl-4">
                                {{-- WhatsApp --}}
                                <div class="flex items-center justify-between md:justify-end gap-2">
                                    @if($cita->notificado_whatsapp)
                                        <x-badge label="Enviado" icon="o-check-circle" class="badge-success badge-sm font-bold gap-1 shadow-sm" />
                                    @endif
                                    
                                    @if($cita->mascota->cliente?->telefono)
                                        <x-button icon="o-chat-bubble-left-ellipsis" 
                                            class="btn-sm {{ $cita->notificado_whatsapp ? 'btn-success' : 'btn-outline btn-success' }} h-8! min-h-0!" 
                                            wire:click="enviarWhatsApp({{ $cita->id }}, 'Cita')" 
                                            tooltip="Enviar WhatsApp" spinner />
                                    @else
                                        <div class="flex items-center gap-1.5 text-error opacity-70" title="Sin WhatsApp">
                                            <x-icon name="o-no-symbol" class="w-4 h-4" />
                                            <span class="text-[10px] font-black uppercase tracking-tighter">Sin WA</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- SMS --}}
                                <div class="flex items-center justify-between md:justify-end gap-2">
                                    @if($cita->notificado_sms)
                                        <x-badge label="Enviado" icon="o-check-circle" class="badge-neutral badge-sm font-bold gap-1 shadow-sm" />
                                    @endif
                                    
                                    @if($cita->mascota->cliente?->telefono)
                                        <x-button icon="o-device-phone-mobile" 
                                            class="btn-sm {{ $cita->notificado_sms ? 'btn-neutral' : 'btn-outline btn-neutral' }} h-8! min-h-0!" 
                                            wire:click="enviarSMS({{ $cita->id }}, 'Cita')" 
                                            tooltip="Enviar SMS" spinner />
                                    @endif
                                </div>

                                {{-- Email --}}
                                <div class="flex items-center justify-between md:justify-end gap-2">
                                    @if($cita->notificado_email)
                                        <x-badge label="Enviado" icon="o-check-circle" class="badge-info badge-sm font-bold gap-1 shadow-sm" />
                                    @endif
                                    
                                    @if($cita->mascota->cliente?->email)
                                        <x-button icon="o-envelope" 
                                            class="btn-sm {{ $cita->notificado_email ? 'btn-info' : 'btn-outline btn-info' }} h-8! min-h-0!" 
                                            wire:click="enviarEmail({{ $cita->id }}, 'Cita')" 
                                            tooltip="Enviar Email" spinner />
                                    @else
                                        <div class="flex items-center gap-1.5 text-error opacity-70" title="Sin Email">
                                            <x-icon name="o-no-symbol" class="w-4 h-4" />
                                            <span class="text-[10px] font-black uppercase tracking-tighter">Sin Email</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            @endif
        </div>

        {{-- Próximas Vacunas / Desparasitaciones --}}
        <div class="space-y-4">
            <div class="flex items-center gap-3 mb-2 px-2">
                <div class="bg-success/10 p-2 rounded-lg">
                    <x-icon name="o-shield-check" class="w-6 h-6 text-success" />
                </div>
                <div>
                    <h2 class="text-xl font-bold font-heading">Control Preventivo</h2>
                    <p class="text-xs text-base-content/50 uppercase tracking-wider font-semibold">Vencimientos Próximos</p>
                </div>
            </div>

            @if($vacunas->isEmpty())
                <x-card class="bg-base-200/30 border-dashed border-2 border-base-300">
                    <div class="text-center py-6 text-base-content/40">
                        <p>No hay vacunas pendientes para notificar.</p>
                    </div>
                </x-card>
            @else
                @foreach($vacunas as $registro)
                    <x-card class="hover:shadow-md transition-shadow border border-base-200 overflow-visible">
                        <div class="flex flex-col md:flex-row justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-lg text-base-content">{{ $registro->mascota->cliente->nombre_completo ?? 'Sin Cliente' }}</span>
                                    <x-badge :value="$registro->tipo_badge['label']" class="{{ $registro->tipo_badge['class'] }} badge-xs font-bold" />
                                </div>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-base-content/70">
                                    <span class="flex items-center gap-1 font-medium text-primary"><x-icon name="o-heart" class="w-4 h-4" /> {{ $registro->mascota->nombre }}</span>
                                    <span class="flex items-center gap-1"><x-icon name="o-beaker" class="w-4 h-4" /> {{ $registro->producto_o_enfermedad }}</span>
                                    <span class="flex items-center gap-1 font-bold text-error/80"><x-icon name="o-calendar" class="w-4 h-4" /> {{ $registro->fecha_proxima->format('d/m/Y') }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2.5 min-w-[170px] border-t md:border-t-0 md:border-l border-base-200 pt-3 md:pt-0 md:pl-4">
                                {{-- WhatsApp --}}
                                <div class="flex items-center justify-between md:justify-end gap-2">
                                    @if($registro->notificado_whatsapp)
                                        <x-badge label="Enviado" icon="o-check-circle" class="badge-success badge-sm font-bold gap-1 shadow-sm" />
                                    @endif
                                    
                                    @if($registro->mascota->cliente?->telefono)
                                        <x-button icon="o-chat-bubble-left-ellipsis" 
                                            class="btn-sm {{ $registro->notificado_whatsapp ? 'btn-success' : 'btn-outline btn-success' }} h-8! min-h-0!" 
                                            wire:click="enviarWhatsApp({{ $registro->id }}, 'Vacuna')" 
                                            tooltip="Enviar WhatsApp" spinner />
                                    @else
                                        <div class="flex items-center gap-1.5 text-error opacity-70" title="Sin WhatsApp">
                                            <x-icon name="o-no-symbol" class="w-4 h-4" />
                                            <span class="text-[10px] font-black uppercase tracking-tighter">Sin WA</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- SMS --}}
                                <div class="flex items-center justify-between md:justify-end gap-2">
                                    @if($registro->notificado_sms)
                                        <x-badge label="Enviado" icon="o-check-circle" class="badge-neutral badge-sm font-bold gap-1 shadow-sm" />
                                    @endif
                                    
                                    @if($registro->mascota->cliente?->telefono)
                                        <x-button icon="o-device-phone-mobile" 
                                            class="btn-sm {{ $registro->notificado_sms ? 'btn-neutral' : 'btn-outline btn-neutral' }} h-8! min-h-0!" 
                                            wire:click="enviarSMS({{ $registro->id }}, 'Vacuna')" 
                                            tooltip="Enviar SMS" spinner />
                                    @endif
                                </div>

                                {{-- Email --}}
                                <div class="flex items-center justify-between md:justify-end gap-2">
                                    @if($registro->notificado_email)
                                        <x-badge label="Enviado" icon="o-check-circle" class="badge-info badge-sm font-bold gap-1 shadow-sm" />
                                    @endif
                                    
                                    @if($registro->mascota->cliente?->email)
                                        <x-button icon="o-envelope" 
                                            class="btn-sm {{ $registro->notificado_email ? 'btn-info' : 'btn-outline btn-info' }} h-8! min-h-0!" 
                                            wire:click="enviarEmail({{ $registro->id }}, 'Vacuna')" 
                                            tooltip="Enviar Email" spinner />
                                    @else
                                        <div class="flex items-center gap-1.5 text-error opacity-70" title="Sin Email">
                                            <x-icon name="o-no-symbol" class="w-4 h-4" />
                                            <span class="text-[10px] font-black uppercase tracking-tighter">Sin Email</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            @endif
        </div>
    </div>
</div>
