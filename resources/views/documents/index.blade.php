@extends('layouts.app', ['title' => 'Monitoring Dokumen'])

@php
    $statusClass = static function (?string $status): string {
        return match ($status) {
            \App\Models\DocumentWorkflow::STATUS_RETURN => 'return',
            \App\Models\DocumentWorkflow::STATUS_APPROVED => 'approved',
            \App\Models\DocumentWorkflow::STATUS_REJECTED => 'rejected',
            \App\Models\DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI => 'waiting',
            \App\Models\DocumentWorkflow::STATUS_COMPLETED => 'success',
            default => 'on-process',
        };
    };
@endphp

@section('content')
    <style>
        .monitoring-page { width: calc(100% + 32px); margin-left: -16px; }
        body:has(.monitoring-page) .content { overflow: hidden; }
        body:has(.monitoring-page) .content > .container { height: 100%; display: flex; flex-direction: column; }
        .monitoring-page { flex: 1; min-height: 0; display: flex; flex-direction: column; }
        .monitoring-toolbar { display: block; margin-bottom: 24px; }
        .monitoring-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; }
        .monitoring-toolbar .stat-card { min-width: 0 !important; padding: 18px; border: 0; border-radius: 20px; box-shadow: 0 8px 18px rgba(15,23,42,.07); transition: transform .2s ease, box-shadow .2s ease; }
        .monitoring-toolbar .stat-card::after { display: none; }
        .monitoring-toolbar .stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(15,23,42,.12); }
        .monitoring-toolbar .stat-card .label { color: var(--stat-accent); font-size: 12px; font-weight: 700; letter-spacing: .06em; }
        .monitoring-toolbar .stat-card .value { margin-top: 6px; color: var(--stat-accent); font-size: 36px; line-height: 1.1; font-weight: 800; }
        .stat-card.status-total { --stat-accent: #334155; background: #EEF1F5; }
        .stat-card.status-on-process { --stat-accent: #1D4ED8; background: #E8F0FE; }
        .stat-card.status-return { --stat-accent: #C2410C; background: #FFF0E5; }
        .stat-card.status-approved { --stat-accent: #15803D; background: #E6F6EC; }
        .stat-card.status-rejected { --stat-accent: #9B1C1C; background: #FDECEC; }
        .stat-card.status-done { --stat-accent: #6D28D9; background: #F0EAFD; }
        .stat-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
        .stat-icon { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 10px; color: var(--stat-accent); background: rgba(255,255,255,.58); }
        .stat-icon svg { width: 17px; height: 17px; }
        .monitoring-filter { display: flex; flex-wrap: wrap; gap: 14px; align-items: end; width: 100%; margin-top: 20px; padding: 20px; border: 1px solid var(--line); border-radius: 20px; background: #fff; box-shadow: 0 8px 24px rgba(17,24,39,.06); }
        .monitoring-filter > div { flex: 1; min-width: 150px; }
        .monitoring-filter input, .monitoring-filter select { min-height: 44px; padding: 9px 12px !important; border-radius: 14px; border-color: var(--line); font-size: 14px !important; }
        .monitoring-compact-select { position: relative; }
        .monitoring-compact-trigger { display: flex; align-items: center; justify-content: space-between; width: 100%; min-height: 44px; padding: 9px 12px; border: 1px solid var(--line); border-radius: 14px; background: #F6F7F9; color: var(--text); font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
        .monitoring-compact-trigger:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(220,31,43,.12); }
        .monitoring-compact-trigger svg { width: 17px; height: 17px; color: var(--muted); }
        .monitoring-compact-options { position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 30; display: none; max-height: 220px; overflow-y: auto; padding: 6px; border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 12px 28px rgba(17,24,39,.14); }
        .monitoring-compact-select.is-open .monitoring-compact-options { display: grid; gap: 2px; }
        .monitoring-compact-option { padding: 9px 10px; border: 0; border-radius: 9px; background: transparent; color: var(--text); font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
        .monitoring-compact-option:hover, .monitoring-compact-option.is-selected { background: var(--primary-soft); color: var(--primary); }
        .date-range-filter { flex: 2 !important; min-width: 300px !important; }
        .date-range-fields { display: grid; grid-template-columns: auto minmax(0, 1fr) auto minmax(0, 1fr); gap: 8px; align-items: center; }
        .date-range-label { color: #64748b; font-size: 11px; font-weight: 700; }
        .monitoring-filter .button { width: 100%; padding: 10px 16px; border-radius: 999px; font-size: 13px; box-shadow: none; }
        .monitoring-table { flex: 1; min-height: 0; padding: 22px; border-radius: 20px; box-shadow: 0 10px 28px rgba(10,40,74,.07); }
        .monitoring-table .table-shell { height: 100%; max-height: none !important; overflow: auto; border-radius: 14px; }
        .monitoring-table .table-shell,
        .monitoring-table tbody { scrollbar-width: thin; scrollbar-color: #D32F2F #edf0f3; }
        .monitoring-table .table-shell::-webkit-scrollbar,
        .monitoring-table tbody::-webkit-scrollbar { display: block; width: 10px; height: 10px; }
        .monitoring-table .table-shell::-webkit-scrollbar-track,
        .monitoring-table tbody::-webkit-scrollbar-track { background: #edf0f3; border-radius: 999px; }
        .monitoring-table .table-shell::-webkit-scrollbar-thumb,
        .monitoring-table tbody::-webkit-scrollbar-thumb { background: #D32F2F; border: 2px solid #edf0f3; border-radius: 999px; }
        .monitoring-table .table-shell::-webkit-scrollbar-thumb:hover,
        .monitoring-table tbody::-webkit-scrollbar-thumb:hover { background: #B71C1C; }
        .monitoring-table table { display: block; width: 100%; height: 100%; min-width: 0; font-size: 13px; border-collapse: separate; border-spacing: 0; table-layout: fixed; }
        .monitoring-table table { font-family: 'Inter', 'Segoe UI', Arial, Helvetica, sans-serif; }
        .monitoring-table th { background: #D32F2F; color: #FFFFFF; border-color: #B71C1C; white-space: normal; overflow-wrap: anywhere; line-height: 1.25; font-weight: 700; padding: 12px 15px; text-align: center; box-shadow: inset -1px 0 rgba(255,255,255,.18); }
        .monitoring-table th:first-child { white-space: nowrap; overflow-wrap: normal; }
        .monitoring-table thead { display: table; width: 100%; height: 58px; table-layout: fixed; }
        .monitoring-table tbody { display: block; height: calc(100% - 58px); overflow-y: auto; overflow-x: hidden; scrollbar-gutter: stable; }
        .monitoring-table tbody tr { display: table; width: 100%; table-layout: fixed; }
        .monitoring-table th:nth-child(1), .monitoring-table td:nth-child(1) { width: 3%; }
        .monitoring-table th:nth-child(2), .monitoring-table td:nth-child(2) { width: 7%; }
        .monitoring-table th:nth-child(3), .monitoring-table td:nth-child(3) { width: 7%; }
        .monitoring-table th:nth-child(4), .monitoring-table td:nth-child(4) { width: 9%; }
        .monitoring-table th:nth-child(5), .monitoring-table td:nth-child(5) { width: 7%; }
        .monitoring-table th:nth-child(6), .monitoring-table td:nth-child(6) { width: 7%; }
        .monitoring-table th:nth-child(7), .monitoring-table td:nth-child(7) { width: 5%; }
        .monitoring-table th:nth-child(8), .monitoring-table td:nth-child(8) { width: 8%; }
        .monitoring-table th:nth-child(9), .monitoring-table td:nth-child(9) { width: 6%; }
        .monitoring-table th:nth-child(10), .monitoring-table td:nth-child(10) { width: 8%; }
        .monitoring-table th:nth-child(11), .monitoring-table td:nth-child(11) { width: 8%; }
        .monitoring-table th:nth-child(12), .monitoring-table td:nth-child(12) { width: 8%; }
        .monitoring-table th:nth-child(13), .monitoring-table td:nth-child(13) { width: 8%; }
        .monitoring-table th:nth-child(14), .monitoring-table td:nth-child(14) { width: 9%; }
        .monitoring-table .table-actions .button { flex: 0 0 auto; white-space: nowrap; }
        .monitoring-table td { padding: 13px 12px; }
        .monitoring-table td:nth-child(13) { overflow-wrap: break-word; word-break: normal; line-height: 1.45; }
        .monitoring-table .table-actions { white-space: nowrap; }
        .monitoring-table tbody tr:nth-child(even) { background: #fafafa; }
        .monitoring-table tbody tr:hover { background: #fff7f7; }
        @media (max-width: 640px) {
            .monitoring-page { width: 100%; margin-left: 0; }
            .monitoring-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .monitoring-toolbar .stat-card { padding: 14px; }
            .monitoring-filter > div { min-width: 100%; }
            .date-range-filter { min-width: 100% !important; }
            .monitoring-table { padding: 14px; }
            .table-shell { max-height: 400px; }
        }
        @media (min-width: 641px) and (max-width: 1100px) { .monitoring-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    </style>

    <div class="monitoring-page">
    <div class="monitoring-toolbar">
        <div class="monitoring-kpis">
        <div class="card stat-card status-total" style="flex: 1; min-width: 140px;">
            <div class="stat-card-head"><div class="label">Total Dokumen</div><span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 3h9l3 3v15H6z"></path><path d="M15 3v4h4M9 12h6M9 16h6"></path></svg></span></div>
            <div class="value">{{ $kpi['total'] }}</div>
        </div>
        <div class="card stat-card status-on-process" style="flex: 1; min-width: 140px;">
            <div class="stat-card-head"><div class="label">On Process</div><span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="8"></circle><path d="M12 8v5l3 2"></path></svg></span></div>
            <div class="value">{{ $kpi['on_process'] }}</div>
        </div>
        <div class="card stat-card status-return" style="flex: 1; min-width: 140px;">
            <div class="stat-card-head"><div class="label">Return</div><span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 7 4 12l5 5"></path><path d="M4 12h10a6 6 0 0 1 6 6"></path></svg></span></div>
            <div class="value">{{ $kpi['returned'] }}</div>
        </div>
        <div class="card stat-card status-approved" style="flex: 1; min-width: 140px;">
            <div class="stat-card-head"><div class="label">Approved</div><span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="8"></circle><path d="m8 12 3 3 5-6"></path></svg></span></div>
            <div class="value">{{ $kpi['approved'] }}</div>
        </div>
        <div class="card stat-card status-rejected" style="flex: 1; min-width: 140px;">
            <div class="stat-card-head"><div class="label">Rejected</div><span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="8"></circle><path d="m9 9 6 6M15 9l-6 6"></path></svg></span></div>
            <div class="value">{{ $kpi['rejected'] }}</div>
        </div>
        <div class="card stat-card status-done" style="flex: 1; min-width: 140px;">
            <div class="stat-card-head"><div class="label">Done</div><span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg></span></div>
            <div class="value">{{ $kpi['completed'] ?? 0 }}</div>
        </div>
        </div>
        <form method="GET" action="{{ route('documents.index') }}" id="filterForm" class="monitoring-filter" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: end; width: 100%;">
            <div style="flex: 1; min-width: 140px;">
                <input type="text" name="search" id="searchInput" value="{{ $filters['search'] ?? '' }}" placeholder="Cari Nomor, Perihal, PIC, Unit, Jenis..." style="padding: 8px 12px; font-size: 13px; width: 100%;">
            </div>
            <div style="flex: 1; min-width: 140px;">
                @php $selectedStatus = $filters['status'] ?? ''; @endphp
                <input type="hidden" name="status" id="statusSelect" value="{{ $selectedStatus }}">
                <div class="monitoring-compact-select" data-monitoring-select data-input-id="statusSelect">
                    <button type="button" class="monitoring-compact-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span data-monitoring-label>{{ $selectedStatus === '' ? 'Semua Status' : match ($selectedStatus) {
                            \App\Models\DocumentWorkflow::STATUS_ON_PROCESS => 'On Process',
                            \App\Models\DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI => 'Menunggu Konfirmasi',
                            \App\Models\DocumentWorkflow::STATUS_APPROVED => 'Approved',
                            \App\Models\DocumentWorkflow::STATUS_REJECTED => 'Rejected',
                            \App\Models\DocumentWorkflow::STATUS_RETURN => 'Return',
                            \App\Models\DocumentWorkflow::STATUS_COMPLETED => 'Done',
                            default => 'Semua Status',
                        } }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                    </button>
                    <div class="monitoring-compact-options" role="listbox">
                        <button type="button" class="monitoring-compact-option {{ $selectedStatus === '' ? 'is-selected' : '' }}" data-value="" role="option">Semua Status</button>
                        @foreach ([
                            \App\Models\DocumentWorkflow::STATUS_ON_PROCESS => 'On Process',
                            \App\Models\DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI => 'Menunggu Konfirmasi',
                            \App\Models\DocumentWorkflow::STATUS_APPROVED => 'Approved',
                            \App\Models\DocumentWorkflow::STATUS_REJECTED => 'Rejected',
                            \App\Models\DocumentWorkflow::STATUS_RETURN => 'Return',
                            \App\Models\DocumentWorkflow::STATUS_COMPLETED => 'Done',
                        ] as $statusValue => $statusLabel)
                            <button type="button" class="monitoring-compact-option {{ $selectedStatus === $statusValue ? 'is-selected' : '' }}" data-value="{{ $statusValue }}" role="option">{{ $statusLabel }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div style="flex: 1; min-width: 140px;">
                @php $selectedDocumentType = $filters['document_type_id'] ?? ''; @endphp
                <input type="hidden" name="document_type_id" id="documentTypeSelect" value="{{ $selectedDocumentType }}">
                <div class="monitoring-compact-select" data-monitoring-select data-input-id="documentTypeSelect">
                    <button type="button" class="monitoring-compact-trigger" aria-haspopup="listbox" aria-expanded="false">
                        <span data-monitoring-label>{{ $selectedDocumentType === '' ? 'Semua Jenis' : ($documentTypes->firstWhere('id', $selectedDocumentType)?->name ?? 'Semua Jenis') }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                    </button>
                    <div class="monitoring-compact-options" role="listbox">
                        <button type="button" class="monitoring-compact-option {{ $selectedDocumentType === '' ? 'is-selected' : '' }}" data-value="" role="option">Semua Jenis</button>
                        @foreach ($documentTypes as $type)
                            <button type="button" class="monitoring-compact-option {{ $selectedDocumentType == $type->id ? 'is-selected' : '' }}" data-value="{{ $type->id }}" role="option">{{ $type->name }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="date-range-filter">
                <div class="date-range-fields">
                    <span class="date-range-label">From</span>
                    <input type="date" name="date_from" id="dateFromInput" value="{{ $filters['date_from'] ?? '' }}" style="padding: 8px 12px; font-size: 13px; width: 100%;" aria-label="Tanggal mulai">
                    <span class="date-range-label">To</span>
                    <input type="date" name="date_to" id="dateToInput" value="{{ $filters['date_to'] ?? '' }}" style="padding: 8px 12px; font-size: 13px; width: 100%;" aria-label="Tanggal akhir">
                </div>
            </div>
            <div style="min-width: 140px;">
                <a class="button secondary" href="{{ route('documents.index') }}" style="padding: 8px 12px; font-size: 13px; width: 100%; text-align: center;">Reset</a>
            </div>
        </form>
    </div>

    <div class="card monitoring-table" id="documentsTable">
        <div class="table-shell" style="overflow: auto; max-height: 500px;">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tgl Masuk</th>
                        <th>No. Dokumen</th>
                        <th>Perihal</th>
                        <th>Jenis Dokumen</th>
                        <th>Unit Pengirim</th>
                        <th>PIC</th>
                        <th>Posisi Dokumen</th>
                        <th>PIC Saat Ini</th>
                        <th>Status</th>
                        <th>Tgl Diterima PIC</th>
                        <th>Aging</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $index => $document)
                        @php $status = $document->currentStatus(); $statusClassName = $statusClass($status); @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $document->received_at?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $document->document_number }}</td>
                            <td>{{ $document->subject }}</td>
                            <td>{{ $document->documentType?->name ?? '-' }}</td>
                            <td>{{ $document->senderUnit?->name ?? '-' }}</td>
                            <td>{{ $document->initial_pic ?? '-' }}</td>
                            <td>{{ $document->currentUnit()?->name ?? '-' }}</td>
                            <td>{{ $document->currentPic() ?? '-' }}</td>
                            <td>
                                 <span class="badge {{ $statusClassName }}">
                                    {{ $document->latestWorkflow?->formatted_status ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $document->latestWorkflow?->received_at?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <span class="aging {{ $statusClassName }}">
                                    @if ($document->isFinal() && in_array($status, [\App\Models\DocumentWorkflow::STATUS_COMPLETED, \App\Models\DocumentWorkflow::STATUS_REJECTED]))
                                        {{ $document->totalAging() }}
                                    @else
                                        {{ $document->currentAging() }}
                                    @endif
                                </span>
</td>
                            <td>{{ $document->notes ?? '-' }}</td>
                            <td>
                                <div class="table-actions">
                                    <a class="button small" href="{{ route('documents.show', $document) }}">Detail</a>
                                    <a class="button secondary small" href="{{ route('documents.edit', $document) }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="muted">Belum ada dokumen. Mulai dari input dokumen pertama.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
<script>
    let debounceTimer;

    function submitFilter() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    }

    // Search input - debounce
    document.getElementById('searchInput').addEventListener('input', submitFilter);

    document.querySelectorAll('[data-monitoring-select]').forEach((select) => {
        const trigger = select.querySelector('.monitoring-compact-trigger');
        const input = document.getElementById(select.dataset.inputId);
        const label = select.querySelector('[data-monitoring-label]');

        select.querySelectorAll('[role="option"]').forEach((option) => {
            option.addEventListener('click', () => {
                input.value = option.dataset.value;
                label.textContent = option.textContent.trim();
                select.querySelectorAll('[role="option"]').forEach((item) => item.classList.remove('is-selected'));
                option.classList.add('is-selected');
                select.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
                clearTimeout(debounceTimer);
                document.getElementById('filterForm').submit();
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

    // Date inputs - immediate on change
    document.getElementById('dateFromInput').addEventListener('change', () => {
        clearTimeout(debounceTimer);
        document.getElementById('filterForm').submit();
    });
    document.getElementById('dateToInput').addEventListener('change', () => {
        clearTimeout(debounceTimer);
        document.getElementById('filterForm').submit();
    });

</script>
@endpush
