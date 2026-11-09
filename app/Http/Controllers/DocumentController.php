<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentWorkflow;
use App\Models\Notification;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Document::query()
            ->with([
                'documentType',
                'senderUnit',
                'latestWorkflow.fromUnit',
                'latestWorkflow.toUnit',
                'attachments' => fn ($q) => $q->orderBy('id'),
                'workflows' => fn ($q) => $q->orderBy('id'),
            ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('initial_pic', 'like', "%{$search}%")
                  ->orWhereHas('senderUnit', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('documentType', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->status;
            $query->whereHas('latestWorkflow', fn ($q) => $q->where('status', $status));
        }

        // Document type filter
        if ($request->filled('document_type_id')) {
            $query->where('document_type_id', $request->document_type_id);
        }

        // Date range filter (based on received_at)
        if ($request->filled('date_from')) {
            $query->whereDate('received_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('received_at', '<=', $request->date_to);
        }

        $documents = $query->latest('id')->get();

        // KPI calculations (based on filtered results)
        $totalDocuments = $documents->count();
        $onProcessCount = $documents->filter(fn ($d) => in_array($d->currentStatus(), [
            DocumentWorkflow::STATUS_ON_PROCESS,
            DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI,
            DocumentWorkflow::STATUS_APPROVED, // Approved bisa didisposisi lagi
        ]))->count();
        $rejectedCount = $documents->filter(fn ($d) => $d->currentStatus() === DocumentWorkflow::STATUS_REJECTED)->count();
        $completedCount = $documents->filter(fn ($d) => $d->currentStatus() === DocumentWorkflow::STATUS_COMPLETED)->count();
        $returnedCount = $documents->filter(fn ($d) => $d->hasReturn())->count();

        return view('documents.index', [
            'documents' => $documents,
            'kpi' => [
                'total' => $totalDocuments,
                'on_process' => $onProcessCount,
                'returned' => $returnedCount,
                'approved' => $documents->filter(fn ($d) => $d->currentStatus() === DocumentWorkflow::STATUS_APPROVED)->count(),
                'rejected' => $rejectedCount,
                'completed' => $completedCount,
            ],
            'filters' => $request->only(['search', 'status', 'document_type_id', 'date_from', 'date_to']),
            'documentTypes' => DocumentType::query()
                ->whereIn('name', DocumentType::DEFAULT_NAMES)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        $documentTypeOrder = array_flip(DocumentType::DEFAULT_NAMES);
        $unitOrder = array_flip(Unit::DEFAULT_NAMES);

        $userUnit = auth()->user()->unit;

        return view('documents.create', [
            'units' => Unit::query()
                ->whereIn('name', Unit::DEFAULT_NAMES)
                ->get()
                ->sortBy(fn (Unit $unit) => $unitOrder[$unit->name] ?? PHP_INT_MAX)
                ->values(),
            'documentTypes' => DocumentType::query()
                ->whereIn('name', DocumentType::DEFAULT_NAMES)
                ->get()
                ->sortBy(fn (DocumentType $documentType) => $documentTypeOrder[$documentType->name] ?? PHP_INT_MAX)
                ->values(),
            'userUnit' => $userUnit,
        ]);
    }

    public function edit(Document $document): View
    {
        $documentTypeOrder = array_flip(DocumentType::DEFAULT_NAMES);
        $unitOrder = array_flip(Unit::DEFAULT_NAMES);

        return view('documents.edit', [
            'document' => $document,
            'units' => Unit::query()
                ->whereIn('name', Unit::DEFAULT_NAMES)
                ->get()
                ->sortBy(fn (Unit $unit) => $unitOrder[$unit->name] ?? PHP_INT_MAX)
                ->values(),
            'documentTypes' => DocumentType::query()
                ->whereIn('name', DocumentType::DEFAULT_NAMES)
                ->get()
                ->sortBy(fn (DocumentType $documentType) => $documentTypeOrder[$documentType->name] ?? PHP_INT_MAX)
                ->values(),
        ]);
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $receivedAtInput = $request->input('received_at');
        $receivedAt = (!empty($receivedAtInput) && strtotime($receivedAtInput) !== false) ? $receivedAtInput : $document->received_at;

        $validated = $request->validate([
            'document_number' => ['required', 'string', 'max:255', Rule::unique('documents', 'document_number')->ignore($document->id)],
            'received_at' => ['nullable', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'initial_pic' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $document->update([
            'document_number' => $validated['document_number'],
            'received_at' => $receivedAt,
            'subject' => $validated['subject'],
            'document_type_id' => $validated['document_type_id'],
            'initial_pic' => $validated['initial_pic'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('documents.edit', $document)
            ->with('status', 'Dokumen berhasil diperbarui.');
    }

    public function store(Request $request): RedirectResponse
    {
        $userUnit = auth()->user()->unit;
        
        abort_unless($userUnit, 403, 'Anda tidak memiliki unit yang ditugaskan. Hubungi administrator.');

        $receivedAtInput = $request->input('received_at');
        $receivedAt = (!empty($receivedAtInput) && strtotime($receivedAtInput) !== false) ? $receivedAtInput : now();

        $validated = $request->validate([
            'document_number' => ['required', 'string', 'max:255', 'unique:documents,document_number'],
            'received_at' => ['nullable', 'date'],
            'subject' => ['required', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'initial_pic' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $document = DB::transaction(function () use ($request, $validated, $userUnit, $receivedAt) {
            $document = Document::create([
                'document_number' => $validated['document_number'],
                'received_at' => $receivedAt,
                'subject' => $validated['subject'],
                'document_type_id' => $validated['document_type_id'],
                'sender_unit_id' => $userUnit->id,
                'initial_pic' => $validated['initial_pic'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $workflow = $document->workflows()->create([
                'from_unit_id' => null,
                'to_unit_id' => $document->sender_unit_id,
                'pic' => $document->initial_pic,
                'status' => DocumentWorkflow::STATUS_ON_PROCESS,
                'direction' => DocumentWorkflow::DIRECTION_CREATED,
                'note' => $document->notes ?: 'Dokumen dibuat.',
                'created_by' => auth()->id(),
            ]);

            // NOTE: No notification for initial workflow - sender doesn't need to confirm their own document
            // Notification will be sent when document is disposisi'd to another unit

            foreach ($request->file('attachments', []) as $file) {
                $storedPath = $file->storeAs(
                    'documents/'.$document->id,
                    Str::uuid().'_'.$file->getClientOriginalName(),
                    'local'
                );

                $document->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }

            return $document;
        });

        return redirect()
            ->route('documents.create')
            ->with('status', 'Dokumen berhasil dibuat dan workflow pertama sudah tercatat.');
    }

    public function show(Document $document): View
    {
        $unitOrder = array_flip(Unit::DEFAULT_NAMES);

        $document->load([
            'documentType',
            'senderUnit',
            'attachments' => fn ($query) => $query->orderBy('id'),
            'latestWorkflow.fromUnit',
            'latestWorkflow.toUnit',
            'latestWorkflow.returnByUnit',
            'latestWorkflow.returnToUnit',
            'workflows' => fn ($query) => $query->orderBy('id')->with(['fromUnit', 'toUnit', 'returnByUnit', 'returnToUnit']),
        ]);

        return view('documents.show', [
            'document' => $document,
            'units' => Unit::query()
                ->whereIn('name', Unit::REVIEW_UNITS)
                ->get()
                ->sortBy(fn (Unit $unit) => array_search($unit->name, Unit::REVIEW_UNITS) !== false ? array_search($unit->name, Unit::REVIEW_UNITS) : PHP_INT_MAX)
                ->values(),
        ]);
    }

    public function storeWorkflow(Request $request, Document $document): RedirectResponse
    {
        $latestWorkflow = $document->workflows()->latest('id')->firstOrFail();

        // Authorization: hanya unit yang sedang memegang dokumen (to_unit) yang bisa update workflow
        $currentUnitId = $latestWorkflow->to_unit_id;
        abort_unless($currentUnitId, 403, 'Dokumen tidak memiliki unit pemegang saat ini.');

        // Check if current user belongs to the current unit
        if (auth()->check() && auth()->user()->unit_id) {
            abort_unless(
                auth()->user()->unit_id == $currentUnitId, 
                403, 
                'Anda tidak berwenang mengelola dokumen ini. ' .
                'Dokumen saat ini di unit: ' . ($latestWorkflow->toUnit?->name ?? 'Tidak diketahui') . 
                ', unit Anda: ' . (auth()->user()->unit?->name ?? 'Tidak ada')
            );
        } elseif (auth()->check() && !auth()->user()->unit_id) {
            abort(403, 'Anda tidak memiliki unit yang ditugaskan. Hubungi administrator untuk menetapkan unit Anda.');
        }

        // Cannot process if waiting for confirmation
        if ($latestWorkflow->status === DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI) {
            abort(403, 'Dokumen sedang menunggu konfirmasi penerimaan oleh unit tujuan.');
        }

        // Cannot process if already completed
        if ($latestWorkflow->status === DocumentWorkflow::STATUS_COMPLETED) {
            abort(403, 'Dokumen sudah diselesaikan (Done), tidak dapat diubah.');
        }

        $action = $request->input('action');

        // Check if this is the first workflow (created by sender unit)
        $isFirstWorkflow = $latestWorkflow->direction === DocumentWorkflow::DIRECTION_CREATED && $latestWorkflow->from_unit_id === null;

        // Define allowed actions per current status
        $allowedActions = match ($latestWorkflow->status) {
            DocumentWorkflow::STATUS_ON_PROCESS => $isFirstWorkflow 
                ? ['disposisi', 'return', 'rejected']  // First workflow: can disposisi, return, or reject
                : ['approved', 'return', 'rejected'],  // Subsequent units: must approve/return/reject
            DocumentWorkflow::STATUS_APPROVED => ['disposisi', 'completed'],
            DocumentWorkflow::STATUS_RETURN => [], // Return must be confirmed by the origin unit first
            DocumentWorkflow::STATUS_REJECTED => ['completed'],
            default => [],
        };

        abort_unless(in_array($action, $allowedActions), 403, 'Aksi tidak diizinkan untuk status ' . $latestWorkflow->formatted_status . '.');

        // Validation: disposisi requires at least one attachment
        if ($action === 'disposisi') {
            $hasAttachment = $document->attachments()->exists();
            abort_unless($hasAttachment, 403, 'Dokumen tidak dapat didisposisikan karena belum ada attachment/file. Silakan upload file terlebih dahulu.');
        }

        // Validation rules per action
        $rules = [
            'action' => ['required', Rule::in($allowedActions)],
        ];

        if ($action === 'disposisi') {
            $rules['to_unit_id'] = ['required', 'exists:units,id'];
            $rules['pic'] = ['required', 'string', 'max:255'];
            $rules['note'] = ['nullable', 'string'];
        } elseif ($action === 'return') {
            // to_unit_id will be auto-set to sender_unit_id
            $rules['pic'] = ['required', 'string', 'max:255'];
            $rules['alasan'] = ['required', 'string'];
        } elseif ($action === 'approved') {
            $rules['note'] = ['nullable', 'string'];
        } elseif ($action === 'rejected') {
            $rules['alasan'] = ['required', 'string'];
        } elseif ($action === 'completed') {
            $rules['note'] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        $fromUnitId = $currentUnitId;
        $targetUnitId = $validated['to_unit_id'] ?? $fromUnitId;
        $status = DocumentWorkflow::STATUS_ON_PROCESS;
        $direction = DocumentWorkflow::DIRECTION_SENT;
        $note = null;
        $returnByUnitId = null;
        $returnToUnitId = null;
        $senderPic = $latestWorkflow->pic ?: $document->initial_pic;

        $newWorkflowData = match ($action) {
            'disposisi' => [
                $status = DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI,
                $direction = DocumentWorkflow::DIRECTION_SENT,
                $targetUnitId = $validated['to_unit_id'],
                $note = $validated['note'] ?? 'Dokumen didisposisikan ke unit tujuan.',
            ],
            'approved' => [
                $status = DocumentWorkflow::STATUS_APPROVED,
                $direction = DocumentWorkflow::DIRECTION_APPROVED,
                $targetUnitId = $fromUnitId,
                $note = $validated['note'] ?? 'Dokumen telah disetujui',
            ],
            'return' => [
                $targetUnitId = $document->sender_unit_id,
                $returnByUnitId = $fromUnitId,        // Unit yang melakukan Return
                $returnToUnitId = $document->sender_unit_id, // Unit asal tujuan Return
                $status = DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI,
                $direction = DocumentWorkflow::DIRECTION_RETURNED,
                $note = $validated['alasan'],
            ],
            'rejected' => [
                $targetUnitId = $document->sender_unit_id,
                $status = DocumentWorkflow::STATUS_REJECTED,
                $direction = DocumentWorkflow::DIRECTION_REJECTED,
                $note = $validated['alasan'],
            ],
            'completed' => [
                // Only allow completing if current status is APPROVED or REJECTED
                abort_unless(in_array($latestWorkflow->status, [DocumentWorkflow::STATUS_APPROVED, DocumentWorkflow::STATUS_REJECTED]), 403, 'Dokumen harus Approved atau Rejected sebelum dapat diselesaikan (Done).'),
                $status = DocumentWorkflow::STATUS_COMPLETED,
                $direction = DocumentWorkflow::DIRECTION_COMPLETED,
                $targetUnitId = $fromUnitId,
                $note = $validated['note'] ?? 'Dokumen telah diselesaikan.',
            ],
        };

        $newWorkflow = $document->workflows()->create([
            'from_unit_id' => $fromUnitId,
            'to_unit_id' => $targetUnitId,
            'return_by_unit_id' => $returnByUnitId,
            'return_to_unit_id' => $returnToUnitId,
            'pic' => $validated['pic'] ?? $latestWorkflow->pic,
            'sender_pic' => $senderPic,
            'receiver_pic' => $validated['pic'] ?? null,
            'status' => $status,
            'direction' => $direction,
            'note' => $note,
            'received_at' => in_array($status, [DocumentWorkflow::STATUS_APPROVED, DocumentWorkflow::STATUS_REJECTED, DocumentWorkflow::STATUS_RETURN, DocumentWorkflow::STATUS_COMPLETED]) ? now() : null,
            'created_by' => auth()->id(),
        ]);

        // Send notifications based on action
        if ($action === 'disposisi') {
            $targetUnit = Unit::find($targetUnitId);
            if ($targetUnit) {
                Notification::createDispositionNotification($document, $newWorkflow, $targetUnit);
            }
        } elseif ($action === 'return') {
            $targetUnit = Unit::find($returnToUnitId);
            if ($targetUnit) {
                Notification::createReturnNotification($document, $newWorkflow, $targetUnit);
            }
        }

        $message = match ($action) {
            'disposisi' => 'Dokumen berhasil didisposisikan. Menunggu konfirmasi penerimaan dari unit tujuan.',
            'completed' => 'Dokumen berhasil diselesaikan (Done).',
            'return' => 'Dokumen berhasil dikembalikan (Return) ke unit asal.',
            'rejected' => 'Dokumen telah ditolak (Rejected).',
            'approved' => 'Dokumen telah disetujui (Approved). Bisa didisposisikan ke unit berikutnya.',
            default => 'Workflow dokumen berhasil diperbarui.',
        };

        return redirect()
            ->route('documents.show', $document)
            ->with('status', $message);
    }

    public function confirmReceipt(Request $request, Document $document): RedirectResponse
    {
        $latestWorkflow = $document->workflows()->latest('id')->firstOrFail();

        // A returned document also requires receipt confirmation by the origin unit.
        abort_unless(
            in_array($latestWorkflow->status, [DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI, DocumentWorkflow::STATUS_RETURN], true),
            403,
            'Dokumen tidak dalam status menunggu konfirmasi.'
        );

        // Authorization: only the target unit (to_unit) can confirm receipt
        $targetUnitId = $latestWorkflow->to_unit_id;
        abort_unless($targetUnitId, 403, 'Unit tujuan tidak valid.');

        // Check if current user belongs to the target unit
        if (auth()->check() && auth()->user()->unit_id) {
            abort_unless(
                auth()->user()->unit_id == $targetUnitId, 
                403, 
                'Anda tidak berwenang mengonfirmasi penerimaan dokumen ini. ' .
                'Dokumen ini untuk unit: ' . ($latestWorkflow->toUnit?->name ?? 'Tidak diketahui') . 
                ', unit Anda: ' . (auth()->user()->unit?->name ?? 'Tidak ada')
            );
        } elseif (auth()->check() && !auth()->user()->unit_id) {
            abort(403, 'Anda tidak memiliki unit yang ditugaskan. Hubungi administrator untuk menetapkan unit Anda.');
        }

        $latestWorkflow->update([
            'status' => DocumentWorkflow::STATUS_ON_PROCESS,
            'direction' => DocumentWorkflow::DIRECTION_RECEIVED,
            'received_at' => now(),
            'receiver_pic' => $latestWorkflow->receiver_pic ?: $latestWorkflow->pic,
            // PIC tetap yang sudah ditentukan saat disposisi, tidak berubah
        ]);

        // Mark the related notification as read after the receiving unit confirms.
        if (auth()->check()) {
            auth()->user()->notifications()
                ->where('document_id', $document->id)
                ->where('document_workflow_id', $latestWorkflow->id)
                ->whereIn('type', ['DOCUMENT_DISPOSITION', 'DOCUMENT_RETURNED'])
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Konfirmasi penerimaan berhasil. Dokumen sekarang On Process.');
    }

    public function downloadAttachment(Attachment $attachment)
    {
        $path = Storage::disk('local')->path($attachment->file_path);

        abort_unless(file_exists($path), 404);

        return response()->download($path, $attachment->file_name);
    }

    public function previewAttachment(Attachment $attachment, ?string $filename = null)
    {
        $path = $attachment->file_path;
        $mime = $attachment->file_type;
        $filename = $attachment->file_name;

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('local')->path($path);

        $contentDisposition = (new ResponseHeaderBag())->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $filename,
            Str::ascii($filename) ?: 'document'
        );

        $headers = [
            'Content-Type' => $mime ?? 'application/octet-stream',
            'Content-Disposition' => $contentDisposition,
            'Content-Description' => $filename,
        ];

        return response()->file($fullPath, $headers);
    }

    public function storeAttachments(Request $request, Document $document): RedirectResponse
    {
        $validated = $request->validate([
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $storedPath = $file->storeAs(
                'documents/' . $document->id,
                Str::uuid() . '_' . $file->getClientOriginalName(),
                'local'
            );

            $document->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'File berhasil ditambahkan.');
    }

    public function deleteAttachment(Attachment $attachment): RedirectResponse
    {
        $document = $attachment->document;

        Storage::disk('local')->delete($attachment->file_path);
        $attachment->delete();

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'File berhasil dihapus.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        // Delete attachments files from storage
        foreach ($document->attachments as $attachment) {
            Storage::disk('local')->delete($attachment->file_path);
        }

        // Delete document (workflows and attachments will cascade delete via FK)
        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('status', 'Dokumen berhasil dihapus.');
    }

    }
