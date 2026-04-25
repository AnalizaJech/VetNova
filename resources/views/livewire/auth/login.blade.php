<div>
    <h2 class="text-xl font-bold font-heading text-center mb-6">Iniciar Sesión</h2>

    <form wire:submit="login" class="space-y-4">
        <x-input
            label="Correo electrónico"
            wire:model="email"
            icon="o-envelope"
            type="email"
            placeholder="admin@vetnova.pe"
            required
        />

        <x-input
            label="Contraseña"
            wire:model="password"
            icon="o-lock-closed"
            type="password"
            placeholder="••••••••"
            required
        />

        <div class="flex items-center justify-between">
            <x-toggle label="Recordarme" wire:model="remember" />
        </div>

        <x-button
            label="Ingresar"
            type="submit"
            icon="o-arrow-right-circle"
            class="btn-primary w-full"
            spinner="login"
        />
    </form>
</div>
