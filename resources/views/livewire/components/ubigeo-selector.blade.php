<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-select 
            label="Departamento" 
            wire:model.live="departamento_id"
            :options="$departamentos" 
            option-label="nombre"
            option-value="id"
            placeholder="Seleccione..."
        />

        <x-select 
            label="Provincia" 
            wire:model.live="provincia_id"
            :options="$provincias" 
            option-label="nombre"
            option-value="id"
            placeholder="Seleccione..."
            :disabled="empty($provincias)"
        />

        <x-select 
            label="Distrito" 
            wire:model.live="distrito_id"
            :options="$distritos" 
            option-label="nombre"
            option-value="id"
            placeholder="Seleccione..."
            :disabled="empty($distritos)"
        />
    </div>
</div>
