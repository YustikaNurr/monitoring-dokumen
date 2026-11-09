<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentWorkflow extends Model
{
    use HasFactory;

    public const STATUS_ON_PROCESS = 'ON_PROCESS';
    public const STATUS_RETURN = 'RETURN';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_MENUNGGU_KONFIRMASI = 'MENUNGGU_KONFIRMASI';
    public const STATUS_COMPLETED = 'COMPLETED';

    public const DIRECTION_CREATED = 'CREATED';
    public const DIRECTION_SENT = 'SENT';
    public const DIRECTION_RECEIVED = 'RECEIVED';
    public const DIRECTION_RETURNED = 'RETURNED';
    public const DIRECTION_APPROVED = 'APPROVED';
    public const DIRECTION_REJECTED = 'REJECTED';
    public const DIRECTION_COMPLETED = 'COMPLETED';

    protected $fillable = [
        'document_id',
        'from_unit_id',
        'to_unit_id',
        'return_by_unit_id',
        'return_to_unit_id',
        'pic',
        'sender_pic',
        'receiver_pic',
        'status',
        'direction',
        'note',
        'received_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
        ];
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ON_PROCESS => 'On Process',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_RETURN => 'Return',
            self::STATUS_MENUNGGU_KONFIRMASI => 'Menunggu Konfirmasi',
            self::STATUS_COMPLETED => 'Done',
            default => $this->status,
        };
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_RETURN => 'return',
            self::STATUS_APPROVED => 'approved',
            self::STATUS_REJECTED => 'rejected',
            self::STATUS_MENUNGGU_KONFIRMASI => 'waiting',
            self::STATUS_COMPLETED => 'success',
            default => 'on-process',
        };
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }

    public function returnByUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'return_by_unit_id');
    }

    public function returnToUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'return_to_unit_id');
    }
}
