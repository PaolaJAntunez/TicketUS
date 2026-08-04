<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Bandeja de notificaciones del navbar (campana): lee/marca las
 * notificaciones de base de datos que ya generan las Notification existentes
 * (canal 'database', agregado junto a 'mail'). No dispara ningún envío
 * nuevo, solo lee lo que Laravel Notifications ya guarda en la tabla
 * "notifications" al notificar por ese canal.
 */
class NotificationController extends Controller
{
    /** Últimas notificaciones + contador de no leídas, para el dropdown y el polling. */
    public function index(Request $request)
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? null,
                'icon' => $notification->data['icon'] ?? 'ti-bell',
                'title' => $notification->data['title'] ?? '',
                'message' => $notification->data['message'] ?? '',
                'url' => $notification->data['url'] ?? null,
                'read' => ! is_null($notification->read_at),
                'created_at' => $notification->created_at->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /** Marca una notificación como leída y redirige a su ticket (o responde JSON si vino por fetch). */
    public function markAsRead(Request $request, string $notification)
    {
        $record = Auth::user()->notifications()->findOrFail($notification);
        $record->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect($record->data['url'] ?? route('dashboard'));
    }

    /** Marca todas las notificaciones pendientes del usuario como leídas. */
    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }
}
