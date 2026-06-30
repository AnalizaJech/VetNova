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
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        
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
            $from = config('services.twilio.whatsapp_from', 'whatsapp:+14155238886');
            // Asegurar formato whatsapp:+
            $formattedTo = str_starts_with($to, 'whatsapp:') ? $to : "whatsapp:$to";
            if (!str_contains($formattedTo, '+')) {
                // Asumir Perú si no tiene prefijo
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
            $messagingServiceSid = config('services.twilio.messaging_service_sid');
            $payload = ['body' => $body];

            if ($messagingServiceSid) {
                $payload['messagingServiceSid'] = $messagingServiceSid;
            } else {
                $payload['from'] = config('services.twilio.from');
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
                'from' => 'VetNeoLink <onboarding@resend.dev>',
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
