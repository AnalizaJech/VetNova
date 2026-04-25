<div>
    <x-header title="Historial de Ventas" subtitle="Consulta de comprobantes y facturación electrónica" separator>
        <x-slot:middle class="!justify-end gap-2 flex-row">
            <x-input type="date" wire:model.live="filtroFecha" icon="o-calendar" />
            <x-input icon="o-magnifying-glass" placeholder="Buscar por cliente o DNI..." wire:model.live.debounce.500ms="search" clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-banknotes" link="{{ route('caja') }}" class="btn-primary" label="Ir a Caja" responsive />
        </x-slot:actions>
    </x-header>

    <x-card class="shadow-sm">
        <x-table :headers="$headers" :rows="$ventas" with-pagination>
            
            {{-- Columna Nro Comprobante --}}
            @scope('cell_id', $venta)
                <div class="font-bold text-base-content">
                    {{ $venta->serie_correlativo ?? str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}
                </div>
                <div class="text-xs text-base-content/60">
                    {{ $venta->created_at->format('H:i A') }}
                </div>
            @endscope

            {{-- Columna Cliente --}}
            @scope('cell_cliente', $venta)
                <div class="font-medium">{{ $venta->cliente->nombre_completo ?? 'Público General' }}</div>
                @if($venta->cliente && $venta->cliente->numero_documento)
                    <div class="text-xs text-base-content/60">
                        {{ $venta->cliente->tipo_documento }}: {{ $venta->cliente->numero_documento }}
                    </div>
                @endif
            @endscope

            {{-- Columna Documento --}}
            @scope('cell_tipo_comprobante', $venta)
                <div class="flex items-center gap-2">
                    @if($venta->tipo_comprobante === 'FACTURA')
                        <x-badge value="FACTURA" class="badge-neutral badge-sm" />
                    @elseif($venta->tipo_comprobante === 'BOLETA')
                        <x-badge value="BOLETA" class="badge-info badge-sm" />
                    @else
                        <x-badge value="TICKET" class="badge-ghost badge-sm" />
                    @endif

                    @if($venta->nubefact_enlace_pdf)
                        <x-icon name="o-cloud-arrow-up" class="w-4 h-4 text-success" tooltip="Enviado a SUNAT" />
                    @endif
                </div>
            @endscope

            {{-- Columna Total --}}
            @scope('cell_total', $venta)
                <div class="font-bold text-success">S/ {{ number_format($venta->total, 2) }}</div>
            @endscope

            {{-- Columna Método Pago --}}
            @scope('cell_metodo_pago', $venta)
                <div class="text-sm">{{ $venta->metodo_pago }}</div>
            @endscope

            {{-- Columna Estado --}}
            @scope('cell_estado', $venta)
                <x-badge :value="$venta->estado" class="{{ $venta->estado === 'PAGADO' ? 'badge-success' : 'badge-error' }} badge-sm" />
            @endscope

            {{-- Acciones --}}
            @scope('actions', $venta)
                <div class="flex items-center gap-1">
                    {{-- Imprimir Ticket (abre en nueva pestaña) --}}
                    <a href="{{ route('ventas.ticket', $venta->id) }}" target="_blank" class="btn btn-ghost btn-sm text-info tooltip" data-tip="Imprimir Ticket">
                        <x-icon name="o-printer" class="w-4 h-4" />
                    </a>

                    {{-- Ver PDF de SUNAT si existe --}}
                    @if($venta->nubefact_enlace_pdf)
                        <a href="{{ $venta->nubefact_enlace_pdf }}" target="_blank" class="btn btn-ghost btn-sm text-success tooltip" data-tip="Descargar XML/PDF">
                            <x-icon name="o-document-arrow-down" class="w-4 h-4" />
                        </a>
                    @endif
                </div>
            @endscope

        </x-table>
    </x-card>
</div>
