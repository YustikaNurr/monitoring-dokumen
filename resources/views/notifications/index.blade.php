@extends('layouts.app', ['title' => 'Notifikasi'])

@php
    $notificationGroups = $notifications->getCollection()->groupBy(
        static fn ($notification) => $notification->created_at->toDateString()
    );
@endphp

@section('content')
<style>
    .notifications-page { width: 100%; }
    .notifications-card { padding: 0; overflow: hidden; border-top: 4px solid #D32F2F; border-radius: 20px; box-shadow: 0 10px 28px rgba(17,24,39,.07); }
    .notifications-toolbar { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; padding: 20px 24px; border-bottom: 1px solid #EEF0F3; }
    .notifications-filter { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin: 0; }
    .notifications-filter-field { width: 200px; }
    .notifications-filter-field.status-field { width: 165px; }
    .notifications-filter label { display: block; margin: 0 0 6px; color: #B71C1C; font-size: 12px; font-weight: 700; }
    .notifications-filter select { height: 44px; background: #F6F7F9; border-color: #E5E7EB; border-radius: 14px; }
    .notification-compact-select { position: relative; }
    .notification-compact-trigger { display: flex; align-items: center; justify-content: space-between; width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid #E5E7EB; border-radius: 14px; background: #F6F7F9; color: var(--text); font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
    .notification-compact-trigger:focus, .notification-compact-select.is-open .notification-compact-trigger { outline: none; border-color: #D32F2F; box-shadow: 0 0 0 4px rgba(211,47,47,.12); }
    .notification-compact-trigger svg { width: 17px; height: 17px; color: var(--muted); }
    .notification-compact-options { position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 30; display: none; max-height: 220px; overflow-y: auto; padding: 6px; border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 12px 28px rgba(17,24,39,.14); }
    .notification-compact-select.is-open .notification-compact-options { display: grid; gap: 2px; }
    .notification-compact-option { padding: 9px 10px; border: 0; border-radius: 9px; background: transparent; color: var(--text); font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
    .notification-compact-option:hover, .notification-compact-option.is-selected { background: #FDECEC; color: #B71C1C; }
    .notifications-reset { min-height: 44px; padding: 10px 16px; border-radius: 999px; background: #FDECEC; color: #B71C1C; box-shadow: none; }
    .notifications-reset:hover { background: #F4C2C2; }
    .notifications-summary { display: flex; align-items: center; justify-content: flex-end; gap: 10px; flex-wrap: wrap; }
    .unread-count { display: inline-flex; align-items: center; min-height: 32px; padding: 6px 12px; border: 1px solid #F4C2C2; border-radius: 999px; background: #FDECEC; color: #9B1C1C; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .mark-all-button { min-height: 36px; padding: 8px 13px; border-radius: 999px; background: #FFF5F5; color: #B71C1C; box-shadow: none; }
    .mark-all-button:hover { background: #FDECEC; }
    .notification-list { overflow: hidden; background: #FFFFFF; }
    .notification-date { padding: 12px 24px; border-left: 4px solid #D32F2F; background: transparent; color: #D32F2F; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .notification-item { padding: 20px 24px; border-top: 1px solid #EEF0F3; background: #FFFFFF; transition: background .2s ease; }
    .notification-item.unread { background: #FFF5F5; }
    .notification-item:hover { background: #FAFBFC; }
    .notification-item.unread:hover { background: #FFF1F1; }
    .notification-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 24px; align-items: center; }
    .notification-main { min-width: 0; }
    .notification-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 9px; }
    .notification-meta .badge { padding: 6px 12px; font-size: 12px; }
    .notification-dot { width: 8px; height: 8px; flex: 0 0 8px; border-radius: 50%; background: #D32F2F; }
    .visually-hidden { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    .notification-meta time { color: #6B7280; font-size: 12px; }
    .notification-message { margin: 0 0 9px; color: #111827; font-size: 15px; font-weight: 700; line-height: 1.5; }
    .notification-item.unread .notification-message { color: #B71C1C; }
    .notification-data { display: flex; gap: 7px 18px; flex-wrap: wrap; color: #6B7280; font-size: 13px; line-height: 1.5; }
    .notification-data span { display: inline-flex; gap: 4px; }
    .notification-data strong { color: #111827; font-weight: 700; }
    .notification-actions { display: flex; align-items: center; justify-content: flex-end; gap: 8px; flex-wrap: wrap; }
    .notification-actions .button { min-height: 36px; padding: 8px 13px; border-radius: 999px; box-shadow: none; font-size: 13px; }
    .notification-actions .confirm-action { background: #D32F2F; color: #FFFFFF; }
    .notification-actions .confirm-action:hover { background: #B71C1C; }
    .notification-actions .read-action { background: transparent; color: #6B7280; box-shadow: none; }
    .notification-actions .read-action:hover { background: #F3F4F6; }
    .notification-actions .delete-action { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; padding: 0; border: 0; border-radius: 50%; background: #FDECEC; color: #B71C1C; cursor: pointer; }
    .notification-actions .delete-action:hover { background: #F4C2C2; }
    .notification-actions .delete-action svg { width: 17px; height: 17px; }
    .notification-empty { padding: 56px 24px; color: #6B7280; text-align: center; }
    .notification-empty strong { display: block; margin-bottom: 4px; color: #111827; }
    .notifications-card > .pagination { margin: 0; padding: 16px 24px; }
    @media (max-width: 900px) {
        .notifications-toolbar { align-items: flex-start; flex-direction: column; }
        .notifications-summary { justify-content: flex-start; }
    }
    @media (max-width: 767px) {
        .notifications-toolbar { padding: 16px; }
        .notifications-filter-field, .notifications-filter-field.status-field { width: 100%; }
        .notifications-filter, .notifications-filter-field, .notifications-reset { width: 100%; }
        .notifications-filter select, .notifications-reset { width: 100%; }
        .notification-date, .notification-item { padding-left: 16px; padding-right: 16px; }
        .notification-row { grid-template-columns: 1fr; gap: 16px; }
        .notification-actions { justify-content: flex-start; }
    }
</style>

<div class="notifications-page">
    <div class="card notifications-card">
        <div class="notifications-toolbar">
            <form method="GET" action="{{ route('notifications.index') }}" id="notificationFilter" class="notifications-filter">
                <div class="notifications-filter-field">
                    <label for="notificationType">Jenis notifikasi</label>
                    @php $selectedNotificationType = $filters['type'] ?? ''; @endphp
                    <input type="hidden" name="type" id="notificationType" value="{{ $selectedNotificationType }}">
                    <div class="notification-compact-select" data-notification-select data-input-id="notificationType">
                        <button type="button" class="notification-compact-trigger" aria-haspopup="listbox" aria-expanded="false"><span data-notification-label>{{ $selectedNotificationType === 'DOCUMENT_DISPOSITION' ? 'Disposisi Dokumen' : ($selectedNotificationType === 'DOCUMENT_RETURNED' ? 'Dokumen dikembalikan' : 'Semua jenis') }}</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg></button>
                        <div class="notification-compact-options" role="listbox">
                            <button type="button" class="notification-compact-option {{ $selectedNotificationType === '' ? 'is-selected' : '' }}" data-value="" role="option">Semua jenis</button>
                            <button type="button" class="notification-compact-option {{ $selectedNotificationType === 'DOCUMENT_DISPOSITION' ? 'is-selected' : '' }}" data-value="DOCUMENT_DISPOSITION" role="option">Disposisi Dokumen</button>
                            <button type="button" class="notification-compact-option {{ $selectedNotificationType === 'DOCUMENT_RETURNED' ? 'is-selected' : '' }}" data-value="DOCUMENT_RETURNED" role="option">Dokumen dikembalikan</button>
                        </div>
                    </div>
                </div>
                <div class="notifications-filter-field status-field">
                    <label for="notificationReadStatus">Status</label>
                    @php $selectedReadStatus = $filters['read_status'] ?? ''; @endphp
                    <input type="hidden" name="read_status" id="notificationReadStatus" value="{{ $selectedReadStatus }}">
                    <div class="notification-compact-select" data-notification-select data-input-id="notificationReadStatus">
                        <button type="button" class="notification-compact-trigger" aria-haspopup="listbox" aria-expanded="false"><span data-notification-label>{{ $selectedReadStatus === 'unread' ? 'Belum dibaca' : ($selectedReadStatus === 'read' ? 'Sudah dibaca' : 'Semua status') }}</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg></button>
                        <div class="notification-compact-options" role="listbox">
                            <button type="button" class="notification-compact-option {{ $selectedReadStatus === '' ? 'is-selected' : '' }}" data-value="" role="option">Semua status</button>
                            <button type="button" class="notification-compact-option {{ $selectedReadStatus === 'unread' ? 'is-selected' : '' }}" data-value="unread" role="option">Belum dibaca</button>
                            <button type="button" class="notification-compact-option {{ $selectedReadStatus === 'read' ? 'is-selected' : '' }}" data-value="read" role="option">Sudah dibaca</button>
                        </div>
                    </div>
                </div>
                <a class="button secondary notifications-reset" href="{{ route('notifications.index') }}">Atur ulang</a>
            </form>

            <div class="notifications-summary">
                @if($unreadCount > 0)
                    <span class="unread-count">{{ $unreadCount }} belum dibaca</span>
                    <form action="{{ route('notifications.read-all') }}" method="POST">
                        @csrf
                        <button class="button secondary mark-all-button" type="submit">Tandai semua sudah dibaca</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="notification-list">
            @forelse ($notificationGroups as $date => $dateNotifications)
                <div class="notification-date">{{ strtoupper(\Carbon\Carbon::parse($date)->format('d M Y')) }}</div>
                @foreach ($dateNotifications as $notification)
                    @php
                        $notificationMessage = $notification->data['message'] ?? '';
                        $reasonMarker = 'Alasan: ';
                        $notificationReason = str_contains($notificationMessage, $reasonMarker)
                            ? substr($notificationMessage, strpos($notificationMessage, $reasonMarker) + strlen($reasonMarker))
                            : null;
                        $isUnread = ! $notification->isRead();
                        $isDisposition = $notification->type === 'DOCUMENT_DISPOSITION';
                    @endphp
                    <article class="notification-item {{ $isUnread ? 'unread' : '' }}">
                        <div class="notification-row">
                            <div class="notification-main">
                                <div class="notification-meta">
                                    @if($isUnread)
                                        <span class="notification-dot" aria-hidden="true"></span>
                                        <span class="visually-hidden">Belum dibaca</span>
                                    @endif
                                    <span class="badge {{ $isDisposition ? 'on-process' : 'return' }}">
                                        {{ $isDisposition ? 'Disposisi dokumen' : 'Dokumen dikembalikan' }}
                                    </span>
                                    <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->format('H:i') }}</time>
                                </div>

                                <div class="notification-message">
                                    {{ $isDisposition ? 'Dokumen menunggu konfirmasi penerimaan.' : 'Dokumen dikembalikan.' }}
                                </div>

                                <div class="notification-data">
                                    <span>No. Dokumen: <strong>{{ $notification->data['document_number'] ?? '-' }}</strong></span>
                                    <span>Perihal: <strong>{{ $notification->data['subject'] ?? '-' }}</strong></span>
                                    @if($notification->data['from_unit'] ?? false)
                                        <span>Dari: <strong>{{ $notification->data['from_unit'] }}</strong></span>
                                    @endif
                                    @if($notification->data['to_unit'] ?? false)
                                        <span>Ke: <strong>{{ $notification->data['to_unit'] }}</strong></span>
                                    @endif
                                    @if($notificationReason)
                                        <span>Alasan: <strong>{{ $notificationReason }}</strong></span>
                                    @endif
                                </div>
                            </div>

                            <div class="notification-actions">
                                @if(in_array($notification->type, ['DOCUMENT_DISPOSITION', 'DOCUMENT_RETURNED'], true) && $isUnread)
                                    <a href="{{ route('notifications.confirm', $notification) }}" class="button confirm-action" onclick="return confirm('Konfirmasi penerimaan dokumen ini?')">Konfirmasi penerimaan</a>
                                @endif

                                @if($notification->document)
                                    <a href="{{ route('documents.show', $notification->document) }}" class="button secondary">Buka dokumen</a>
                                @endif

                                @if($isUnread)
                                    <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                        @csrf
                                        <button class="button read-action" type="submit">Tandai sudah dibaca</button>
                                    </form>
                                @endif

                                <form action="{{ route('notifications.destroy', $notification) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="delete-action" type="submit" aria-label="Hapus notifikasi" title="Hapus notifikasi">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2M19 6l-1 14H6L5 6"></path><path d="M10 11v5M14 11v5"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            @empty
                <div class="notification-empty">
                    <strong>Belum ada notifikasi</strong>
                    <span>Notifikasi baru terkait dokumen akan muncul di sini.</span>
                </div>
            @endforelse
        </div>

        {{ $notifications->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    const notificationFilter = document.getElementById('notificationFilter');
    document.querySelectorAll('[data-notification-select]').forEach((select) => {
        const trigger = select.querySelector('.notification-compact-trigger');
        const input = document.getElementById(select.dataset.inputId);
        const label = select.querySelector('[data-notification-label]');

        select.querySelectorAll('[role="option"]').forEach((option) => {
            option.addEventListener('click', () => {
                input.value = option.dataset.value;
                label.textContent = option.textContent.trim();
                select.querySelectorAll('[role="option"]').forEach((item) => item.classList.remove('is-selected'));
                option.classList.add('is-selected');
                select.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
                notificationFilter.submit();
            });
        });

        trigger.addEventListener('click', () => {
            const isOpen = select.classList.toggle('is-open');
            trigger.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', (event) => {
            if (!select.contains(event.target)) {
                select.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    });

    document.querySelectorAll('.notifications-card form').forEach((form) => {
        form.addEventListener('submit', () => {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.setAttribute('aria-disabled', 'true');
            }
        });
    });

    document.querySelectorAll('.confirm-action').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (link.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            link.dataset.submitting = 'true';
            link.setAttribute('aria-disabled', 'true');
            link.style.pointerEvents = 'none';
        });
    });
</script>
@endpush
