<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentWorkflow;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        $query = $user->notifications()->with(['document.documentType', 'document.senderUnit', 'documentWorkflow.fromUnit', 'documentWorkflow.toUnit'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('read_status')) {
            if ($request->read_status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->read_status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        $notifications = $query->paginate(20)->withQueryString();

        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'filters' => $request->only(['type', 'read_status']),
        ]);
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        // Authorization: only the owner can mark as read
        abort_unless($notification->notifiable_type === auth()->user()->getMorphClass() && $notification->notifiable_id === auth()->id(), 403);

        $notification->markAsRead();

        return redirect()->back()->with('status', 'Notifikasi ditandai sebagai dibaca.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->back()->with('status', 'Semua notifikasi ditandai sebagai dibaca.');
    }

    public function confirmFromNotification(Notification $notification): RedirectResponse
    {
        // Authorization: only the owner can confirm
        abort_unless($notification->notifiable_type === auth()->user()->getMorphClass() && $notification->notifiable_id === auth()->id(), 403);

        // Disposition and return notifications both require receipt confirmation.
        abort_unless(in_array($notification->type, ['DOCUMENT_DISPOSITION', 'DOCUMENT_RETURNED'], true), 403, 'Notifikasi ini bukan untuk konfirmasi penerimaan.');

        $document = $notification->document;
        $workflow = $notification->documentWorkflow;

        if (!$document || !$workflow) {
            return redirect()->route('notifications.index')->with('error', 'Dokumen atau workflow tidak ditemukan.');
        }

        // Return notifications created before this flow may still have RETURN status.
        $isConfirmable = $notification->type === 'DOCUMENT_DISPOSITION'
            ? $workflow->status === DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI
            : in_array($workflow->status, [DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI, DocumentWorkflow::STATUS_RETURN], true);

        if (!$isConfirmable) {
            return redirect()->route('documents.show', $document)->with('error', 'Dokumen sudah tidak dalam status menunggu konfirmasi.');
        }

        // Verify user's unit matches target unit
        if (auth()->user()->unit_id && auth()->user()->unit_id !== $workflow->to_unit_id) {
            abort(403, 'Anda tidak berwenang mengonfirmasi penerimaan dokumen ini.');
        }

        // Open the existing receipt confirmation form on the document page.
        $notification->markAsRead();

        return redirect()->route('documents.show', $document)->with('confirm_receipt', true);
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        // Authorization: only the owner can delete
        abort_unless($notification->notifiable_type === auth()->user()->getMorphClass() && $notification->notifiable_id === auth()->id(), 403);

        $notification->delete();

        return redirect()->back()->with('status', 'Notifikasi dihapus.');
    }
}
