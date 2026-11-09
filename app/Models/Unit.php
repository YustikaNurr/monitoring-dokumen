<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    public const DEFAULT_NAMES = [
        'KANTOR PUSAT',
        'BUSINESS SUPPORT & RISK MANAGEMENT',
        'PLANNING & DELIVERY',
        'OPERATION & MAINTENANCE',
        'MARKETING, SALES & SOLUTION',
        'AREA SEMARANG',
        'AREA YOGYAKARTA',
        'AREA SOLO',
        'AREA SURAMADU',
        'AREA JATIM TIMUR',
        'AREA JATIM BARAT',
        'AREA BALI',
        'AREA NUSRA',
        'BM TTC PAHLAWAN',
        'BM TTC GOMBEL',
        'BM TTC SOLO BARU',
        'BM SIGMA GICC',
        'BM TTC GAYUNGAN',
        'BM TTC HR MUHAMMAD',
        'BM TSO RENON',
        'BM TTC RENON',
    ];

    public const REVIEW_UNITS = [
        'BUSINESS SUPPORT & RISK MANAGEMENT',
        'PLANNING & DELIVERY',
        'OPERATION & MAINTENANCE',
        'MARKETING, SALES & SOLUTION',
        'VP',
    ];

    protected $fillable = [
        'name',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'sender_unit_id');
    }
}
