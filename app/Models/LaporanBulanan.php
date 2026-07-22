<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanBulanan extends Model
{
    protected $table = 'laporan_bulanan';
    protected $primaryKey = 'id_laporan_bulanan';

    protected $fillable = [
        'id_danru_pembuat',
        'regu',
        'bulan',
        'tahun',
        'status_dokumen',
        'ttd_danru_url',
        'tgl_ttd_danru',
        'ttd_chief_url',
        'tgl_ttd_chief',
        'ttd_klien_url',
        'tgl_ttd_klien',
        'file_pdf_url',
    ];

    public function danruPembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_danru_pembuat', 'id_user');
    }
}
