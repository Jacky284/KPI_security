<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatatanPelanggaran extends Model
{
    protected $table = 'catatan_pelanggaran';
    protected $primaryKey = 'id_catatan';

    protected $fillable = [
        'id_anggota',
        'id_danru_penilai',
        'tanggal_kejadian',
        'minggu_ke',
        'bulan',
        'tahun',
        'kategori_indikator',
        'tingkat_pelanggaran',
        'deskripsi_kejadian',
        'status_tindak_lanjut',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_anggota', 'id_user');
    }

    public function danruPenilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_danru_penilai', 'id_user');
    }
}
