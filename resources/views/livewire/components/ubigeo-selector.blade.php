<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-choices-offline 
            label="Departamento" 
            wire:model.live="departamento_id"
            :options="$departamentos" 
            option-label="nombre"
            option-value="id"
            placeholder="Seleccione..."
            searchable 
            single
        />

        <x-choices-offline 
            label="Provincia" 
            wire:model.live="provincia_id"
            :options="$provincias" 
            option-label="nombre"
            option-value="id"
            placeholder="Seleccione..."
            searchable 
            single
            :disabled="empty($provincias)"
        />

        <x-choices-offline 
            label="Distrito" 
            wire:model.live="distrito_id"
            :options="$distritos" 
            option-label="nombre"
            option-value="id"
            placeholder="Seleccione..."
            searchable 
            single
            :disabled="empty($distritos)"
        />
    </div>
</div>
