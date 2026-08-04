<?php

namespace App\Services;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Único punto por el que pasan todos los ->notify() del ciclo de vida de
 * tickets/aprobaciones. Las notificaciones se envían de forma síncrona (no
 * implementan ShouldQueue) y el driver de correo es Postmark en producción:
 * un timeout, rate-limit o error de red de Postmark no debe tumbar la
 * operación de negocio que disparó el aviso (crear/asignar/aprobar/etc.).
 */
class SafeNotifier
{
    public static function send(mixed $notifiable, Notification $notification): void
    {
        if (! $notifiable) {
            return;
        }

        try {
            $notifiable->notify($notification);
        } catch (Throwable $e) {
            Log::error('No se pudo enviar la notificación '.$notification::class, [
                'notifiable_type' => $notifiable::class,
                'notifiable_id' => $notifiable->id ?? null,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
