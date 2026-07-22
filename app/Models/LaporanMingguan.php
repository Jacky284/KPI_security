<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanMingguan extends Model
{
    protected $table = 'laporan_mingguan';
    protected $primaryKey = 'id_laporan_mingguan';

    protected $fillable = [
        'id_danru',
        'regu',
        'minggu_ke',
        'bulan',
        'tahun',
        'file_pdf_url',
        'status_dokumen',
        'ttd_danru_url',
        'tgl_ttd_danru',
        'ttd_chief_url',
        'tgl_ttd_chief',
    ];

    public function danru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_danru', 'id_user');
    }
}
