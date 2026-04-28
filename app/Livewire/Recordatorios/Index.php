<?php

declare(strict_types=1);

namespace App\Livewire\Recordatorios;

use App\Models\Cita;
use App\Models\RegistroPreventivo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Traits\AlertModal;
use Resend\Laravel\Facades\Resend;

#[Layout('components.layouts.app')]
#[Title('Centro de Recordatorios — VetNova')]
class Index extends Component
{
    use AlertModal;

    /**
     * Envía un mensaje vía WhatsApp usando Twilio.
     */
    public function enviarWhatsApp(int $id, string $tipoObj, \App\Services\NotificationService $notifications): void
    {
        $item = $tipoObj === 'Cita' ? Cita::with('mascota.cliente')->find($id) : RegistroPreventivo::with('mascota.cliente')->find($id);
        
        if (!$item || !$item->mascota->cliente?->telefono) {
            $this->error("No se encontró el teléfono del cliente.");
            return;
        }

        $cliente = $item->mascota->cliente;
        $nombreMascota = $item->mascota->nombre;
        $fecha = $tipoObj === 'Cita' ? $item->fecha_hora->format('d/m/Y h:i A') : $item->fecha_proxima->format('d/m/Y');
        $asunto = $tipoObj === 'Cita' ? 'tu cita' : "el refuerzo de {$item->producto_o_enfermedad}";
        
        $msg = "Hola {$cliente->nombres}, recordatorio de VetNova: tienes $asunto para $nombreMascota el día $fecha. ¡Te esperamos!";
        
        try {
            if ($notifications->sendWhatsApp($cliente->telefono, $msg)) {
                $this->marcarComoNotificado($id, $tipoObj, 'whatsapp');
                $this->success("WhatsApp enviado con éxito.");
            }
        } catch (\Exception $e) {
            $this->error("Error Twilio WA: " . $e->getMessage());
        }
    }

    /**
     * Envía un SMS tradicional usando Twilio.
     */
    public function enviarSMS(int $id, string $tipoObj, \App\Services\NotificationService $notifications): void
    {
        $item = $tipoObj === 'Cita' ? Cita::with('mascota.cliente')->find($id) : RegistroPreventivo::with('mascota.cliente')->find($id);
        
        if (!$item || !$item->mascota->cliente?->telefono) {
            $this->error("No se encontró el teléfono del cliente.");
            return;
        }

        $cliente = $item->mascota->cliente;
        $nombreMascota = $item->mascota->nombre;
        $fecha = $tipoObj === 'Cita' ? $item->fecha_hora->format('d/m/Y h:i A') : $item->fecha_proxima->format('d/m/Y');
        $asunto = $tipoObj === 'Cita' ? 'Cita' : 'Refuerzo';
        
        $msg = "VetNova: $asunto para $nombreMascota el $fecha. ¡Te esperamos!";
        
        try {
            if ($notifications->sendSMS($cliente->telefono, $msg)) {
                $this->marcarComoNotificado($id, $tipoObj, 'sms');
                $this->success("SMS enviado con éxito.");
            }
        } catch (\Exception $e) {
            $this->error("Error Twilio SMS: " . $e->getMessage());
        }
    }

    /**
     * Envía correo electrónico usando Resend.
     */
    public function enviarEmail(int $id, string $tipoObj, \App\Services\NotificationService $notifications): void
    {
        $item = $tipoObj === 'Cita' ? Cita::with('mascota.cliente')->find($id) : RegistroPreventivo::with('mascota.cliente')->find($id);
        
        if (!$item || !$item->mascota->cliente?->email) {
            $this->error("No se encontró el correo del cliente.");
            return;
        }

        $cliente = $item->mascota->cliente;
        $nombreMascota = $item->mascota->nombre;
        $tipoMsg = $tipoObj === 'Cita' ? 'Cita Médica' : 'Control Preventivo';
        $detalle = $tipoObj === 'Cita' 
            ? "Cita para $nombreMascota el " . $item->fecha_hora->format('d/m/Y h:i A')
            : "Refuerzo de {$item->producto_o_enfermedad} para $nombreMascota el " . $item->fecha_proxima->format('d/m/Y');

        $html = "
            <div style='font-family: sans-serif; color: #1e293b; max-width: 600px; margin: auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;'>
                <div style='background: #4f46e5; padding: 20px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 24px;'>Recordatorio VetNova</h1>
                </div>
                <div style='padding: 30px; background: white;'>
                    <h2 style='color: #1e293b; margin-top: 0;'>Hola {$cliente->nombres},</h2>
                    <p style='font-size: 16px; line-height: 1.6;'>Te escribimos para recordarte tu próxima actividad en nuestra clínica:</p>
                    <div style='background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #4f46e5; margin: 20px 0;'>
                        <p style='margin: 5px 0;'><strong>Asunto:</strong> $tipoMsg</p>
                        <p style='margin: 5px 0;'><strong>Paciente:</strong> $nombreMascota</p>
                        <p style='margin: 5px 0;'><strong>Detalle:</strong> $detalle</p>
                    </div>
                    <p style='font-size: 14px; color: #64748b; text-align: center; margin-top: 30px;'>
                        Por favor, llega 10 minutos antes. ¡Te esperamos!
                    </p>
                </div>
                <div style='background: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #94a3b8;'>
                    VetNova — Gestión Veterinaria Profesional
                </div>
            </div>
        ";

        if ($notifications->sendEmail($cliente->email, "Recordatorio de $tipoMsg — VetNova", $html)) {
            $this->marcarComoNotificado($id, $tipoObj, 'email');
            $this->success("Correo enviado con éxito.");
        } else {
            $this->error("Error al enviar correo. Verifique su cuenta de Resend.");
        }
    }

    private function marcarComoNotificado(int $id, string $tipoObj, string $medio): void
    {
        $columna = "notificado_$medio";
        if ($tipoObj === 'Cita') {
            Cita::where('id', $id)->update([$columna => true]);
        } else {
            RegistroPreventivo::where('id', $id)->update([$columna => true]);
        }
    }

    public function render()
    {
        $clinica_id = Auth::user()->clinica_id;
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
        $vacunas = RegistroPreventivo::with(['mascota.cliente'])
            ->where('clinica_id', $clinica_id)
            ->whereNotNull('fecha_proxima')
            ->whereBetween('fecha_proxima', [$hoy, $manana->copy()->endOfDay()])
            ->orderBy('fecha_proxima', 'asc')
            ->get();

        return view('livewire.recordatorios.index', compact('citas', 'vacunas'));
    }
}
