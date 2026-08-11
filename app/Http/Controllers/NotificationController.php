<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
        ]);
    }

    public function read(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_id === $request->user()->id
            && $notification->notifiable_type === $request->user()::class, 404);

        $notification->markAsRead();

        // Defesa em profundidade: hoje toda notificação usa uma URL interna
        // gerada pelo backend, mas o redirect não deve confiar cegamente no
        // valor armazenado — só segue caminhos relativos internos (bloqueia
        // "https://..." e "//host" de virarem open redirect).
        $url = $notification->data['url'] ?? null;
        $isInternalPath = is_string($url) && str_starts_with($url, '/') && ! str_starts_with($url, '//');

        return redirect($isInternalPath ? $url : route('notifications.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Notificações marcadas como lidas.');
    }
}
