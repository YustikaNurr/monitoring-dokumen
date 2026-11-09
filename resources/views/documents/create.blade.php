@extends('layouts.app', ['title' => 'Input Dokumen'])

@section('content')
    <style>
        .create-document { max-width: 980px; margin: 0 auto; }
        .create-hero { position: relative; overflow: hidden; margin-bottom: 20px; padding: 30px 32px; border: 0; border-left: 5px solid #B71C1C; border-radius: 24px; background: #D32F2F; color: #FFFFFF; box-shadow: 0 16px 34px rgba(183,28,28,.18); }
        .create-hero::after { content: ''; position: absolute; width: 240px; height: 240px; right: -70px; top: -100px; border-radius: 50%; background: rgba(255,255,255,.09); }
        .create-hero > div { position: relative; z-index: 1; }
        .create-hero .label { color: rgba(255,255,255,.8); }
        .create-hero h2 { margin: 0 0 6px; color: #FFFFFF; font-size: clamp(24px, 3vw, 34px); letter-spacing: -.045em; }
        .create-hero p { margin: 0; color: rgba(255,255,255,.9); line-height: 1.55; }
        .create-card { padding: 26px; border-radius: 20px; box-shadow: 0 10px 28px rgba(10,40,74,.07); }
        .create-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
        .create-card-head .panel-icon { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 11px; color: var(--primary); background: var(--primary-soft); }
        .create-card-head svg { width: 19px; height: 19px; }
        .create-card-head h3 { margin: 0; font-size: 19px; }
        .create-card-head p { margin: 3px 0 0; color: var(--muted); font-size: 13px; }
        .create-form { gap: 0; }
        .create-sender-unit { margin: -6px 0 24px; padding-bottom: 16px; border-bottom: 1px solid var(--line); }
        .create-sender-unit label { display: inline; margin: 0 5px 0 0; color: var(--text); font-size: 12px; font-weight: 700; }
        .create-sender-unit .sender-unit-value { display: inline; width: auto; min-height: 0; padding: 0; border: 0; background: transparent; color: var(--primary); font-size: 14px; font-weight: 700; white-space: normal; overflow: visible; overflow-wrap: anywhere; }
        .create-sender-unit .compact-select-trigger { min-height: 0; width: auto; padding: 0; border: 0; border-radius: 0; background: transparent; color: var(--primary); font-weight: 700; }
        .create-sender-unit .compact-select-trigger:focus { box-shadow: none; }
        .create-sender-unit .compact-select-trigger svg { margin-left: 6px; }
        .create-sender-unit .create-help { margin-top: 4px; }
        .create-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 20px; }
        .create-field { min-width: 0; }
        .create-field.full { grid-column: 1 / -1; }
        .create-field label { display: block; margin-bottom: 7px; color: var(--text); font-size: 12px; font-weight: 700; }
        .create-field input, .create-field select, .create-field textarea { border-color: #d4d4d4; border-radius: 10px; }
        .create-field input:focus, .create-field select:focus, .create-field textarea:focus { border-color: #D32F2F; box-shadow: 0 0 0 4px rgba(211,47,47,.12); }
        .compact-select { position: relative; }
        .compact-select-trigger { display: flex; align-items: center; justify-content: space-between; width: 100%; min-height: 44px; padding: 10px 14px; border: 1px solid #d4d4d4; border-radius: 14px; background: #F6F7F9; color: var(--text); font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
        .compact-select-trigger:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(220,31,43,.12); }
        .compact-select-trigger svg { width: 17px; height: 17px; color: var(--muted); }
        .compact-select-options { position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 20; display: none; max-height: 220px; overflow-y: auto; padding: 6px; border: 1px solid var(--line); border-radius: 14px; background: #fff; box-shadow: 0 12px 28px rgba(17,24,39,.14); }
        .compact-select.is-open .compact-select-options { display: grid; gap: 2px; }
        .compact-select-option { padding: 10px 12px; border: 0; border-radius: 10px; background: transparent; color: var(--text); font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
        .compact-select-option:hover, .compact-select-option.is-selected { background: var(--primary-soft); color: var(--primary); }
        .create-field input[readonly] { background: #f5f5f5; color: var(--muted); cursor: not-allowed; }
        .create-field textarea { min-height: 120px; }
        .create-help { margin-top: 5px; color: var(--muted); font-size: 12px; }
        .create-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--line); }
        .create-actions .button { min-width: 140px; }
        .create-field .errors { margin: 5px 0 0; padding: 0; border: 0; background: transparent; box-shadow: none; color: var(--danger); font-size: 12px; }
        @media (max-width: 640px) {
            .create-hero { padding: 20px; }
            .create-card { padding: 20px; }
            .create-fields { grid-template-columns: 1fr; gap: 16px; }
            .create-field.full { grid-column: auto; }
            .create-actions .button { width: 100%; }
        }
    </style>

    <div class="create-document">
        <div class="create-hero">
            <div>
                <div class="label">Dokumen</div>
                <h2>Input Dokumen</h2>
                <p>Tambahkan dokumen baru ke dalam monitoring.</p>
            </div>
        </div>

        <div class="card create-card">
            <div class="create-card-head">
                <span class="panel-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14"></path><path d="M5 12h14"></path><rect x="4" y="4" width="16" height="16" rx="3"></rect>
                    </svg>
                </span>
                <div>
                    <h3>Informasi Dokumen</h3>
                    <p>Lengkapi data dokumen dan file pendukungnya.</p>
                </div>
            </div>

            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="create-form">
                 @csrf

                <div class="create-sender-unit">
                    <label for="sender_unit_id">Unit Pengirim</label>
                    @if ($userUnit)
                        <input type="hidden" id="sender_unit_id" name="sender_unit_id" value="{{ $userUnit->id }}">
                        <span class="sender-unit-value">{{ $userUnit->name }}</span>
                    @else
                        <input type="hidden" id="sender_unit_id" name="sender_unit_id" value="{{ old('sender_unit_id') }}">
                        <div class="compact-select" data-compact-select data-input-id="sender_unit_id">
                            <button type="button" class="compact-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                                <span data-select-label>{{ old('sender_unit_id') ? $units->firstWhere('id', old('sender_unit_id'))?->name : 'Pilih unit' }}</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                            </button>
                            <div class="compact-select-options" role="listbox">
                                <button type="button" class="compact-select-option {{ old('sender_unit_id') ? '' : 'is-selected' }}" data-value="" role="option">Pilih unit</button>
                            @foreach ($units as $unit)
                                <button type="button" class="compact-select-option {{ old('sender_unit_id') == $unit->id ? 'is-selected' : '' }}" data-value="{{ $unit->id }}" role="option">{{ $unit->name }}</button>
                            @endforeach
                            </div>
                        </div>
                        @error('sender_unit_id') <span class="text-danger">{{ $message }}</span> @enderror
                    @endif
                </div>

                <div class="create-fields">
                    <div class="create-field">
                        <label for="document_number">Nomor Dokumen</label>
                        <input id="document_number" name="document_number" value="{{ old('document_number') }}" required>
                        @error('document_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="create-field">
                        <label for="received_at">Tanggal Masuk</label>
                        <input id="received_at" type="datetime-local" name="received_at" value="{{ old('received_at') }}" autocomplete="off">
                        @error('received_at') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="create-field full">
                        <label for="subject">Perihal</label>
                        <input id="subject" name="subject" value="{{ old('subject') }}" required>
                        @error('subject') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="create-field">
                        <label for="document_type_id">Jenis Dokumen</label>
                        <input type="hidden" id="document_type_id" name="document_type_id" value="{{ old('document_type_id') }}">
                        <div class="compact-select" data-compact-select data-input-id="document_type_id">
                            <button type="button" class="compact-select-trigger" aria-haspopup="listbox" aria-expanded="false">
                                <span data-select-label>{{ old('document_type_id') ? $documentTypes->firstWhere('id', old('document_type_id'))?->name : 'Pilih jenis dokumen' }}</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                            </button>
                            <div class="compact-select-options" role="listbox">
                                <button type="button" class="compact-select-option {{ old('document_type_id') ? '' : 'is-selected' }}" data-value="" role="option">Pilih jenis dokumen</button>
                            @foreach ($documentTypes as $documentType)
                                <button type="button" class="compact-select-option {{ old('document_type_id') == $documentType->id ? 'is-selected' : '' }}" data-value="{{ $documentType->id }}" role="option">{{ $documentType->name }}</button>
                            @endforeach
                            </div>
                        </div>
                        @error('document_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="create-field">
                        <label for="initial_pic">PIC Awal</label>
                        <input id="initial_pic" name="initial_pic" value="{{ old('initial_pic') }}" required>
                        @error('initial_pic') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="create-field full">
                        <label for="notes">Catatan</label>
                        <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                        @error('notes') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="create-field full">
                        <label for="attachments">Attachment <span class="text-danger">*</span></label>
                        <input id="attachments" type="file" name="attachments[]" multiple required>
                        <div class="create-help">Multi-file, maksimal 10 MB per file.</div>
                        @error('attachments.*') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="create-actions">
                    <button class="button" type="submit">Simpan Dokumen</button>
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
