<div>
    <x-header title="Configuración" subtitle="Datos de tu clínica y sucursales" separator />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">

        {{-- ═══════════ DATOS DE LA CLÍNICA ═══════════ --}}
        <div class="lg:col-span-2">
            <x-card title="Datos Generales" subtitle="Información fiscal y de contacto de tu clínica" shadow class="border border-base-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Nombre comercial" wire:model="nombre" icon="o-building-office" placeholder="Mi Veterinaria" error-field="nombre" />
                    <x-input label="RUC" wire:model="ruc" icon="o-identification" placeholder="20123456789" maxlength="11" error-field="ruc" />
                    <div class="md:col-span-2">
                        <x-input label="Razón Social" wire:model="razon_social" icon="o-document-text" placeholder="Mi Veterinaria S.A.C." error-field="razon_social" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input label="Dirección fiscal" wire:model="direccion" icon="o-map-pin" placeholder="Av. Ejemplo 123, Lima" error-field="direccion" />
                    </div>
                    <x-input label="Teléfono" wire:model="telefono" icon="o-phone" placeholder="01-234-5678" error-field="telefono" />
                    <x-input label="Email" wire:model="email" icon="o-envelope" type="email" placeholder="contacto@clinica.pe" error-field="email" />
                    <div class="md:col-span-2">
                        <x-input label="Sitio web" wire:model="sitio_web" icon="o-globe-alt" placeholder="https://mi-clinica.pe" error-field="sitio_web" />
                    </div>
                </div>

                <x-slot:actions>
                    <x-button label="Guardar Cambios" class="btn-primary" wire:click="guardarClinica" icon="o-check" spinner />
                </x-slot:actions>
            </x-card>
        </div>

        {{-- ═══════════ INFO RÁPIDA ═══════════ --}}
        <div class="space-y-4 md:space-y-6">
            {{-- Resumen del plan --}}
            <x-card shadow class="border border-base-200">
                <div class="text-center">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-primary/10 mx-auto mb-3">
                        <x-icon name="o-heart" class="w-7 h-7 text-primary" />
                    </div>
                    <h3 class="font-bold text-lg text-base-content">VetNeoLink</h3>
                    <p class="text-sm text-base-content/60 mt-1">Sistema de Gestión Veterinaria</p>
                    <div class="divider my-3"></div>
                    <div class="text-left space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Versión</span>
                            <span class="font-mono text-base-content">1.0.0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Laravel</span>
                            <span class="font-mono text-base-content">{{ app()->version() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">PHP</span>
                            <span class="font-mono text-base-content">{{ PHP_VERSION }}</span>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- APIs configuradas --}}
            <x-card title="Integraciones" shadow class="border border-base-200">
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center">
                        <span>PeruAPI (DNI/RUC)</span>
                        @if(config('services.peruapi.key'))
                            <x-badge value="Configurada" class="badge-success badge-sm" />
                        @else
                            <x-badge value="Sin configurar" class="badge-error badge-sm" />
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Nubefact (SUNAT)</span>
                        @if(config('services.nubefact.token'))
                            <x-badge value="Configurada" class="badge-success badge-sm" />
                        @else
                            <x-badge value="Sin configurar" class="badge-warning badge-sm" />
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Twilio (WhatsApp)</span>
                        @if(config('services.twilio.sid'))
                            <x-badge value="Configurada" class="badge-success badge-sm" />
                        @else
                            <x-badge value="Sin configurar" class="badge-warning badge-sm" />
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- ═══════════ SUCURSALES ═══════════ --}}
    <div class="mt-6 md:mt-8">
        <x-card title="Sucursales" subtitle="Locales de atención de tu clínica" shadow class="border border-base-200">
            <x-slot:actions>
                <x-button label="Nueva Sucursal" icon="o-plus" class="btn-primary btn-sm" wire:click="crearSucursal" />
            </x-slot:actions>

            @if($sucursales->isEmpty())
                <div class="text-center py-8 text-base-content/40">
                    <x-icon name="o-building-office-2" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                    <p>No hay sucursales registradas.</p>
                </div>
            @else
                <div class="divide-y divide-base-200/50">
                    @foreach($sucursales as $sucursal)
                        <div class="py-4 flex justify-between items-center">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-base-content">{{ $sucursal->nombre }}</p>
                                    @if($sucursal->principal)
                                        <x-badge value="Principal" class="badge-primary badge-sm" />
                                    @endif
                                    @if(!$sucursal->activo)
                                        <x-badge value="Inactiva" class="badge-error badge-sm" />
                                    @endif
                                </div>
                                <p class="text-xs text-base-content/60 mt-1">
                                    {{ $sucursal->direccion ?? 'Sin dirección' }}
                                    @if($sucursal->telefono)
                                        &bull; {{ $sucursal->telefono }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex gap-1">
                                <x-button icon="o-pencil-square" class="btn-ghost btn-xs" wire:click="editarSucursal({{ $sucursal->id }})" tooltip="Editar" spinner />
                                @if(!$sucursal->principal)
                                    <x-button icon="o-trash" class="btn-ghost btn-xs text-error" 
                                        wire:click="eliminarSucursal({{ $sucursal->id }})"
                                        wire:confirm="¿Eliminar esta sucursal? Esta acción no se puede deshacer."
                                        tooltip="Eliminar" spinner />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

    {{-- ═══════════ MODAL SUCURSAL ═══════════ --}}
    <x-modal wire:model="modalSucursal" title="{{ $isEditingSucursal ? 'Editar Sucursal' : 'Nueva Sucursal' }}" box-class="max-w-lg">
        <div class="space-y-4">
            <x-input label="Nombre de la sucursal" wire:model="sucursal_nombre" icon="o-building-office-2" placeholder="Sede Norte" error-field="sucursal_nombre" />
            <x-input label="Dirección" wire:model="sucursal_direccion" icon="o-map-pin" placeholder="Av. Ejemplo 456" error-field="sucursal_direccion" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="Teléfono" wire:model="sucursal_telefono" icon="o-phone" placeholder="01-987-6543" error-field="sucursal_telefono" />
                <x-input label="Email" wire:model="sucursal_email" icon="o-envelope" type="email" placeholder="sede@clinica.pe" error-field="sucursal_email" />
            </div>
            <x-toggle label="Sucursal activa" wire:model="sucursal_activo" />
        </div>

        <x-slot:actions>
            <x-button label="Cancelar" class="btn-ghost" wire:click="$set('modalSucursal', false)" />
            <x-button label="{{ $isEditingSucursal ? 'Actualizar' : 'Crear Sucursal' }}" class="btn-primary" wire:click="guardarSucursal" spinner />
        </x-slot:actions>
    </x-modal>
</div>
