<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'document_id',
        'document_workflow_id',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function documentWorkflow(): BelongsTo
    {
        return $this->belongsTo(DocumentWorkflow::class);
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public static function createDispositionNotification(Document $document, DocumentWorkflow $workflow, Unit $targetUnit): void
    {
        $users = User::where('unit_id', $targetUnit->id)->get();

        foreach ($users as $user) {
            static::create([
                'type' => 'DOCUMENT_DISPOSITION',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'document_id' => $document->id,
                'document_workflow_id' => $workflow->id,
                'data' => [
                    'document_number' => $document->document_number,
                    'subject' => $document->subject,
                    'from_unit' => $workflow->fromUnit?->name,
                    'to_unit' => $targetUnit->name,
                    'message' => "Dokumen \"{$document->document_number}\" didisposisikan dari {$workflow->fromUnit?->name} ke {$targetUnit->name}. Silakan konfirmasi penerimaan.",
                ],
            ]);
        }
    }

    public static function createReturnNotification(Document $document, DocumentWorkflow $workflow, Unit $targetUnit): void
    {
        $users = User::where('unit_id', $targetUnit->id)->get();

        foreach ($users as $user) {
            static::create([
                'type' => 'DOCUMENT_RETURNED',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'document_id' => $document->id,
                'document_workflow_id' => $workflow->id,
                'data' => [
                    'document_number' => $document->document_number,
                    'subject' => $document->subject,
                    'from_unit' => $workflow->fromUnit?->name,
                    'to_unit' => $targetUnit->name,
                    'message' => "Dokumen \"{$document->document_number}\" dikembalikan (Return) dari {$workflow->fromUnit?->name} ke {$targetUnit->name}. Alasan: {$workflow->note}",
                ],
            ]);
        }
    }
}