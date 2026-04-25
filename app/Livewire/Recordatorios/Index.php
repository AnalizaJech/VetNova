<?php

declare(strict_types=1);

namespace App\Livewire\Recordatorios;

use App\Models\Cita;
use App\Models\Vacuna;
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

    public function enviarWhatsApp(string $telefono, string $nombreCliente, string $tipo)
    {
        // En un entorno productivo, aquí se inyectaría el SDK de Twilio usando 
        // las credenciales configuradas en el archivo .env (TWILIO_SID, TWILIO_TOKEN).
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

        // Buscar Citas (Hoy y Mañana)
        $citas = Cita::with(['mascota.cliente'])
            ->where('clinica_id', $clinica_id)
            ->whereIn('estado', ['PENDIENTE', 'CONFIRMADA'])
            ->whereBetween('fecha_hora', [$hoy, $manana->copy()->endOfDay()])
            ->orderBy('fecha_hora', 'asc')
            ->get();

        // Buscar Vacunas Próximas (Hoy y Mañana)
        $vacunas = Vacuna::with(['mascota.cliente'])
            ->where('clinica_id', $clinica_id)
            ->whereBetween('proxima_dosis', [$hoy, $manana->copy()->endOfDay()])
            ->orderBy('proxima_dosis', 'asc')
            ->get();

        return view('livewire.recordatorios.index', compact('citas', 'vacunas'));
    }
}
