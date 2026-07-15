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
        'shift_berjalan',
        'minggu_ke',
        'bulan',
        'tahun',
        'file_pdf_url',
    ];

    public function danru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_danru', 'id_user');
    }
}
