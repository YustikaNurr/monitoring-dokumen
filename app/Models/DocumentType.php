<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    public const DEFAULT_NAMES = [
        'SPPH/Undangan',
        'SPH',
        'BAKN',
        'MOM',
        'KONTRAK',
        'SPK/FPB/PO',
        'Justifikasi Kebutuhan',
        'Justifikasi Pengadaan',
        'Kajian Bisnis',
        'TOR/RKS Teknis',
        'RKS Administrasi',
        'Surat Penetapan',
        'BAPP/BAUT/BAST/Berita Acara Lainnya',
        'Surat Kesangupan',
        'Invoice',
        'Tagihan Mitra',
        'JIB',
        'PDRK (Dokumen RRA)',
        'Dokumen Lainnya',
    ];

    protected $fillable = [
        'name',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
