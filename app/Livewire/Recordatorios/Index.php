<?php

declare(strict_types=1);

namespace App\Livewire\Recordatorios;

use App\Models\Cita;
use App\Models\RegistroPreventivo;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Traits\AlertModal;

#[Layout('components.layouts.app')]
#[Title('Recordatorios de WhatsApp — VetNova')]
class Index extends Component
{
    use AlertModal;

    /**
     * Simula envío de WhatsApp vía Twilio.
     * En producción: inyectar TwilioClient y usar messages->create().
     */
    public function enviarWhatsApp(string $telefono, string $nombreCliente, string $tipo): void
    {
        // TODO: Integrar SDK de Twilio real usando TWILIO_SID, TWILIO_TOKEN del .env
        // Ejemplo: TwilioClient->messages->create("whatsapp:$telefono", [...])
        
        // Simulamos un retraso de red
        sleep(1);

        $this->success(
            "El recordatorio de $tipo fue enviado correctamente al número: $telefono",
            "Mensaje Enviado a $nombreCliente"
        );
    }

    public function render()
    {
        $clinica_id = auth()->user()->clinica_id;
        $hoy = Carbon::today();
        $manana = Carbon::tomorrow();

        // Buscar Citas pendientes/confirmadas de hoy y mañana
        $citas = Cita::with(['mascota.cliente'])
            ->where('clinica_id', $clinica_id)
            ->whereIn('estado', ['PENDIENTE', 'CONFIRMADA'])
            ->whereBetween('fecha_hora', [$hoy, $manana->copy()->endOfDay()])
            ->orderBy('fecha_hora', 'asc')
            ->get();

        // Buscar Vacunas/Desparasitaciones con próxima dosis hoy o mañana
        // Modelo correcto: RegistroPreventivo — campo correcto: fecha_proxima
        $vacunas = RegistroPreventivo::with(['mascota.cliente'])
            ->where('clinica_id', $clinica_id)
            ->whereNotNull('fecha_proxima')
            ->whereBetween('fecha_proxima', [$hoy, $manana->copy()->endOfDay()])
            ->orderBy('fecha_proxima', 'asc')
            ->get();

        return view('livewire.recordatorios.index', compact('citas', 'vacunas'));
    }
}
