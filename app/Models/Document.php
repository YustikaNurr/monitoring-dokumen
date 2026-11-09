<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'received_at',
        'subject',
        'document_type_id',
        'sender_unit_id',
        'initial_pic',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::retrieved(function () {
            Carbon::setLocale('id');
        });
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function senderUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sender_unit_id');
    }

    public function workflows(): HasMany
    {
        return $this->hasMany(DocumentWorkflow::class);
    }

    public function latestWorkflow(): HasOne
    {
        return $this->hasOne(DocumentWorkflow::class)->latestOfMany();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function currentStatus(): ?string
    {
        return $this->latestWorkflow?->status;
    }

    public function currentPic(): ?string
    {
        return $this->latestWorkflow?->pic;
    }

    public function currentUnit(): ?Unit
    {
        return $this->latestWorkflow?->toUnit;
    }

    public function isFinal(): bool
    {
        $status = $this->currentStatus();
        return in_array($status, [
            DocumentWorkflow::STATUS_COMPLETED,
            DocumentWorkflow::STATUS_REJECTED,
        ]);
    }

    public function canBeDisposed(): bool
    {
        $status = $this->currentStatus();
        $waitingConfirmation = DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI;
        
        return !in_array($status, [
            DocumentWorkflow::STATUS_COMPLETED,
            DocumentWorkflow::STATUS_REJECTED,
            $waitingConfirmation,
        ]);
    }

    public function currentAging(): string
    {
        $latestWorkflow = $this->latestWorkflow;
        
        if (!$latestWorkflow) {
            return '-';
        }

        // Truly final statuses: aging stops at workflow's received_at
        $finalStatuses = [
            DocumentWorkflow::STATUS_COMPLETED,
            DocumentWorkflow::STATUS_REJECTED,
        ];

        // For MENUNGGU_KONFIRMASI, aging shows how long it's been waiting for confirmation
        // For ON_PROCESS, aging starts from received_at (konfirmasi penerimaan)
        // For RETURN, aging starts from received_at
        // For APPROVED, aging continues (can still be disposed)
        if (in_array($latestWorkflow->status, $finalStatuses)) {
            return 'Selesai';
        } elseif ($latestWorkflow->status === DocumentWorkflow::STATUS_ON_PROCESS) {
            $startedAt = $latestWorkflow->received_at ?? $latestWorkflow->created_at;
            $endedAt = now();
        } elseif ($latestWorkflow->status === DocumentWorkflow::STATUS_MENUNGGU_KONFIRMASI) {
            $startedAt = $latestWorkflow->created_at; // Waiting since disposisi
            $endedAt = now();
        } else {
            $startedAt = $latestWorkflow->received_at ?? $latestWorkflow->created_at;
            $endedAt = now();
        }

        if (!$startedAt instanceof CarbonInterface) {
            return '-';
        }

        if ($startedAt === $endedAt) {
            return '0 detik';
        }

        $diff = $startedAt->diff($endedAt);
        return $this->formatDuration($diff);
    }

    public function receivedAt(): ?string
    {
        return $this->latestWorkflow?->received_at?->format('d/m/Y');
    }

    public function totalAging(): string
    {
        $latestWorkflow = $this->latestWorkflow;
        
        if (!$this->received_at instanceof CarbonInterface) {
            return '-';
        }

        // Truly final statuses: total aging stops at the final workflow's received_at
        $finalStatuses = [
            DocumentWorkflow::STATUS_COMPLETED,
            DocumentWorkflow::STATUS_REJECTED,
        ];

        if ($latestWorkflow && in_array($latestWorkflow->status, $finalStatuses)) {
            $endedAt = $latestWorkflow->received_at ?? $latestWorkflow->created_at;
            if (!$endedAt instanceof CarbonInterface) {
                return '-';
            }
            $diff = $this->received_at->diff($endedAt);
            return $this->formatDuration($diff);
        }

        // For ongoing (including APPROVED), total aging = now - document received_at
        $diff = $this->received_at->diff(now());
        return $this->formatDuration($diff);
    }

    private function formatDuration(\DateInterval $diff): string
    {
        $parts = [];
        
        if ($diff->y > 0) {
            $parts[] = $diff->y . ' tahun';
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' bulan';
        }
        if ($diff->d > 0) {
            $parts[] = $diff->d . ' hari';
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h . ' jam';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i . ' menit';
        }
        if ($diff->s > 0 && empty($parts)) {
            $parts[] = $diff->s . ' detik';
        }
        
        if (empty($parts)) {
            return '0 detik';
        }
        
        return implode(' ', array_slice($parts, 0, 2));
    }

    public function hasReturn(): bool
    {
        return $this->relationLoaded('workflows')
            ? $this->workflows->contains(fn (DocumentWorkflow $workflow) => $workflow->status === DocumentWorkflow::STATUS_RETURN)
            : $this->workflows()->where('status', DocumentWorkflow::STATUS_RETURN)->exists();
    }

    public function hasReject(): bool
    {
        return $this->relationLoaded('workflows')
            ? $this->workflows->contains(fn (DocumentWorkflow $workflow) => $workflow->status === DocumentWorkflow::STATUS_REJECTED)
            : $this->workflows()->where('status', DocumentWorkflow::STATUS_REJECTED)->exists();
    }
}