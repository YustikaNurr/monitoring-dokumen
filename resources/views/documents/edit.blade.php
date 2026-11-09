@extends('layouts.app', ['title' => 'Edit Dokumen'])

@section('content')
    <style>
        .edit-document { max-width: 980px; margin: 0 auto; }
        .edit-hero { position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 20px; padding: 30px 32px; border: 0; border-left: 5px solid #B71C1C; border-radius: 24px; background: #D32F2F; color: #FFFFFF; box-shadow: 0 16px 34px rgba(183,28,28,.18); }
        .edit-hero::after { content: ''; position: absolute; width: 240px; height: 240px; right: -70px; top: -100px; border-radius: 50%; background: rgba(255,255,255,.09); }
        .edit-hero > div { position: relative; z-index: 1; }
        .edit-hero .label { color: rgba(255,255,255,.8); }
        .edit-hero h2 { margin: 0 0 6px; color: #FFFFFF; font-size: clamp(24px, 3vw, 34px); letter-spacing: -.045em; }
        .edit-hero p { margin: 0; color: rgba(255,255,255,.9); line-height: 1.55; }
        .edit-card { padding: 26px; border-radius: 20px; box-shadow: 0 10px 28px rgba(10,40,74,.07); }
        .edit-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
        .edit-card-head .panel-icon { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 11px; color: var(--primary); background: var(--primary-soft); }
        .edit-card-head svg { width: 19px; height: 19px; }
        .edit-card-head h3 { margin: 0; font-size: 19px; }
        .edit-card-head p { margin: 3px 0 0; color: var(--muted); font-size: 13px; }
        .edit-form { gap: 0; }
        .edit-sender-unit { margin: -6px 0 24px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
        .edit-sender-unit label { display: inline; margin: 0 5px 0 0; color: var(--text); font-size: 12px; font-weight: 700; }
        .edit-sender-unit .sender-unit-value { display: inline; width: auto; color: var(--primary); font-size: 14px; font-weight: 700; white-space: normal; overflow-wrap: anywhere; }
        .edit-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 20px; }
        .edit-field { min-width: 0; }
        .edit-field.full { grid-column: 1 / -1; }
        .edit-field label { display: block; margin-bottom: 7px; color: var(--text); font-size: 12px; font-weight: 700; }
        .edit-field input, .edit-field select, .edit-field textarea { border-color: #d4d4d4; border-radius: 10px; }
        .edit-field input:focus, .edit-field select:focus, .edit-field textarea:focus { border-color: #D32F2F; box-shadow: 0 0 0 4px rgba(211,47,47,.12); }
        .compact-select { position: relative; }
        .compact-select-trigger { display: flex; align-items: center; justify-content: space-between; width: 100%; min-height: 44px; padding: 10px 14px; border: 1px solid #d4d4d4; border-radius: 14px; background: #F6F7F9; color: var(--text); font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
        .compact-select-trigger:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(220,31,43,.12); }
        .compact-select-trigger svg { width: 17px; height: 17px; color: var(--muted); }
        .compact-select-options { position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 20; display: none; max-height: 220px; overflow-y: auto; padding: 6px; border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 12px 28px rgba(17,24,39,.14); }
        .compact-select.is-open .compact-select-options { display: grid; gap: 2px; }
        .compact-select-option { padding: 10px 12px; border: 0; border-radius: 10px; background: transparent; color: var(--text); font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
        .compact-select-option:hover, .compact-select-option.is-selected { background: var(--primary-soft); color: var(--primary); }
        .edit-field input[readonly] { background: #f5f5f5; color: var(--muted); cursor: not-allowed; }
        .edit-field textarea { min-height: 120px; }
        .edit-help { margin-top: 5px; color: var(--muted); font-size: 12px; }
        .edit-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--line); }
        .edit-actions .button { min-width: 140px; }
        .text-danger { display: block; margin-top: 5px; color: var(--danger); font-size: 12px; }
        @media (max-width: 640px) {
            .edit-hero { align-items: flex-start; flex-direction: column; padding: 20px; }
            .edit-card { padding: 20px; }
            .edit-fields { grid-template-columns: 1fr; gap: 16px; }
            .edit-field.full { grid-column: auto; }
            .edit-actions .button { width: 100%; }
        }
    </style>

    <div class="edit-document">
        <div class="edit-hero">
            <div>
                <div class="label">Dokumen</div>
                <h2>{{ $document->document_number }}</h2>
                <p>{{ $document->subject }}</p>
            </div>
        </div>

        <div class="card edit-card">
            <div class="edit-card-head">
                <span class="panel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"></path>
                    </svg>
                </span>
                <div>
                    <h3>Informasi Dokumen</h3>
                    <p>Perbarui data dasar dokumen dengan informasi terbaru.</p>
                </div>
            </div>

            <form action="{{ route('documents.update', $document) }}" method="POST" class="edit-form">
                 @csrf
                 @method('PUT')

                <div class="edit-sender-unit">
                    <label for="sender_unit_id">Unit Pengirim</label>
                    <input type="hidden" id="sender_unit_id" name="sender_unit_id" value="{{ $document->sender_unit_id }}">
                    <span class="sender-unit-value">{{ $document->senderUnit?->name ?? '-' }}</span>
                </div>

                <div class="edit-fields">
                    <div class="edit-field">
                        <label for="document_number">Nomor Dokumen</label>
                        <input id="document_number" name="document_number" value="{{ old('document_number', $document->document_number) }}" required>
                        @error('document_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="edit-field">
                        <label for="received_at">Tanggal Masuk</label>
                        <input id="received_at" type="datetime-local" name="received_at" value="{{ old('received_at', $document->received_at?->format('Y-m-d\TH:i')) }}" autocomplete="off">
                        @error('received_at') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="edit-field full">
                        <label for="subject">Perihal</label>
                        <input id="subject" name="subject" value="{{ old('subject', $document->subject) }}" required>
                        @error('subject') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="edit-field">
                        <label for="document_type_id">Jenis Dokumen</label>
                        @php $selectedDocumentType = old('document_type_id', $document->document_type_id); @endphp
                        <input type="hidden" id="document_type_id" name="document_type_id" value="{{ $selectedDocumentType }}">
                        <div class="compact-select" data-compact-select data-input-id="document_type_id">
                            <button type="button" class="compact-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                                <span data-select-label>{{ $documentTypes->firstWhere('id', $selectedDocumentType)?->name ?? 'Pilih jenis dokumen' }}</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                            </button>
                            <div class="compact-select-options" role="listbox">
                                <button type="button" class="compact-select-option {{ $selectedDocumentType ? '' : 'is-selected' }}" data-value="" role="option">Pilih jenis dokumen</button>
                            @foreach ($documentTypes as $documentType)
                                <button type="button" class="compact-select-option {{ $selectedDocumentType == $documentType->id ? 'is-selected' : '' }}" data-value="{{ $documentType->id }}" role="option">{{ $documentType->name }}</button>
                            @endforeach
                            </div>
                        </div>
                        @error('document_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="edit-field">
                        <label for="initial_pic">PIC Awal</label>
                        <input id="initial_pic" name="initial_pic" value="{{ old('initial_pic', $document->initial_pic) }}" readonly required>
                        @error('initial_pic') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="edit-field full">
                        <label for="notes">Catatan</label>
                        <textarea id="notes" name="notes">{{ old('notes', $document->notes) }}</textarea>
                        @error('notes') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="edit-actions">
                    <button class="button" type="submit">Update Dokumen</button>
                    <a class="button secondary" href="{{ route('documents.show', $document) }}">Detail dokumen</a>
                    <a class="button secondary" href="{{ route('documents.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-compact-select]').forEach((select) => {
        const trigger = select.querySelector('.compact-select-trigger');
        const input = document.getElementById(select.dataset.inputId);
        const label = select.querySelector('[data-select-label]');

        select.querySelectorAll('[role="option"]').forEach((option) => {
            option.addEventListener('click', () => {
                input.value = option.dataset.value;
                label.textContent = option.textContent.trim();
                select.querySelectorAll('[role="option"]').forEach((item) => item.classList.remove('is-selected'));
                option.classList.add('is-selected');
                select.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
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
</script>
@endpush
