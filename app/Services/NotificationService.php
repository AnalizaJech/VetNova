<?php

declare(strict_types=1);

namespace App\Services;

use Twilio\Rest\Client as TwilioClient;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected ?TwilioClient $twilio = null;

    public function __construct()
    {
        $sid   = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        
        if ($sid && $token) {
            $this->twilio = new TwilioClient($sid, $token);
        }
    }

    /**
     * Envía un mensaje vía WhatsApp.
     */
    public function sendWhatsApp(string $to, string $body): bool
    {
        if (!$this->twilio) return false;

        try {
            $from = env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886');
            // Asegurar formato whatsapp:+
            $formattedTo = str_starts_with($to, 'whatsapp:') ? $to : "whatsapp:$to";
            if (!str_contains($formattedTo, '+')) {
                // Asumir Perú si no tiene prefijo (ajustar según necesidad)
                $formattedTo = str_replace('whatsapp:', 'whatsapp:+51', $formattedTo);
            }

            $this->twilio->messages->create($formattedTo, [
                'from' => $from,
                'body' => $body
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Twilio WhatsApp Error: " . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Envía un SMS tradicional.
     */
    public function sendSMS(string $to, string $body): bool
    {
        if (!$this->twilio) return false;

        try {
            $messagingServiceSid = env('TWILIO_MESSAGING_SERVICE_SID');
            $payload = ['body' => $body];

            if ($messagingServiceSid) {
                $payload['messagingServiceSid'] = $messagingServiceSid;
            } else {
                $payload['from'] = env('TWILIO_FROM');
            }

            $formattedTo = $to;
            if (!str_starts_with($formattedTo, '+')) {
                $formattedTo = "+51" . $formattedTo;
            }

            $this->twilio->messages->create($formattedTo, $payload);

            return true;
        } catch (\Exception $e) {
            Log::error("Twilio SMS Error: " . $e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Envía un correo electrónico vía Resend.
     */
    public function sendEmail(string $to, string $subject, string $html): bool
    {
        try {
            Resend::emails()->send([
                'from' => 'VetNova <onboarding@resend.dev>',
                'to' => [$to],
                'subject' => $subject,
                'html' => $html,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Resend Error: " . $e->getMessage());
            return false;
        }
    }
}
